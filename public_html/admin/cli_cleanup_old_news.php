<?php
/**
 * Fast CLI cleanup for large DBs.
 *
 * FAST (recommended for migration shrink) — age only, bulk SQL, no per-file unlink:
 *   C:\xampp\php\php.exe public_html\admin\cli_cleanup_old_news.php --months=24 --fast
 *
 * Safer (keeps 3000+ views, slower; needs index on news_views.newsid):
 *   C:\xampp\php\php.exe public_html\admin\cli_cleanup_old_news.php --months=24 --batch=200
 *
 * --fast implies: skip view protection + skip deleting image files now
 *   (DB rows go away; orphan files under images/news can be cleaned later)
 */
if (php_sapi_name() !== "cli") {
	fwrite(STDERR, "CLI only.\n");
	exit(1);
}

@set_time_limit(0);
@ini_set("memory_limit", "1024M");
@ini_set("output_buffering", "0");

$months = 6;
$batch = 500;
$dry = false;
$skipViews = false;
$fast = false;
$dbOnly = false;

foreach ($argv as $arg) {
	if (preg_match('/^--months=(\d+)$/', $arg, $m)) {
		$months = max(1, (int) $m[1]);
	}
	if (preg_match('/^--batch=(\d+)$/', $arg, $m)) {
		$batch = max(50, min(2000, (int) $m[1]));
	}
	if ($arg === "--dry-run") {
		$dry = true;
	}
	if ($arg === "--skip-views") {
		$skipViews = true;
	}
	if ($arg === "--fast") {
		$fast = true;
		$skipViews = true;
		$dbOnly = true;
		$batch = max($batch, 1000);
	}
	if ($arg === "--db-only") {
		$dbOnly = true;
	}
}

function out($msg) {
	echo $msg . PHP_EOL;
	@ob_flush();
	flush();
}

function nm_parse_news_date($dateStr) {
	$dateStr = trim((string) $dateStr);
	if ($dateStr === "") {
		return null;
	}
	$dt = DateTime::createFromFormat("d-m-Y", $dateStr);
	if ($dt instanceof DateTime) {
		$dt->setTime(0, 0, 0);
		return $dt;
	}
	$ts = strtotime($dateStr);
	return $ts ? (new DateTime())->setTimestamp($ts) : null;
}

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/news_media.php";

$cutoff = new DateTime("today");
$cutoff->modify("-{$months} months");
$afterId = 0;
$deleted = 0;
$skipped = 0;
$scanned = 0;
$loops = 0;

out("Cleanup FAST-capable CLI");
out("months=$months batch=$batch" . ($fast ? " MODE=fast(db-only,no-view-protect)" : "") . ($dry ? " DRY-RUN" : ""));
out("Cutoff date: " . $cutoff->format("Y-m-d") . " (posts older than this)");
if ($fast) {
	out("NOTE: --fast does NOT keep 3000+ view posts. Only age filter. Image files left on disk for later.");
}
out("Starting...");

while (true) {
	$loops++;
	$t0 = microtime(true);

	// Walk by primary key (fast) and filter date in PHP (avoids STR_TO_DATE full scans)
	$sql = "SELECT `newsid`, `date`, `image`, `video_file` FROM `news` WHERE `newsid` > $afterId ORDER BY `newsid` ASC LIMIT $batch";
	$q = mysqli_query($con, $sql);
	if (!$q) {
		out("SQL error: " . mysqli_error($con));
		exit(1);
	}

	$rows = array();
	while ($r = mysqli_fetch_assoc($q)) {
		$rows[] = $r;
	}
	if (!$rows) {
		break;
	}

	$toDelete = array();
	$images = array();
	foreach ($rows as $r) {
		$id = (int) $r["newsid"];
		$afterId = max($afterId, $id);
		$scanned++;
		$dt = nm_parse_news_date($r["date"]);
		if (!$dt || $dt >= $cutoff) {
			continue; // too new or bad date
		}

		if (!$skipViews) {
			$v = nm_news_view_count($con, $id);
			if (nm_is_view_protected($v)) {
				$skipped++;
				continue;
			}
		}

		$toDelete[] = $id;
		if (!$dbOnly) {
			$img = trim((string) $r["image"]);
			if ($img !== "" && $img !== "null") {
				$images[] = "images/news/" . basename($img);
			}
			$vid = trim((string) $r["video_file"]);
			if ($vid !== "" && $vid !== "null") {
				$images[] = "videos/" . basename($vid);
			}
		}
	}

	$nDel = count($toDelete);
	out("loop $loops: scanned " . count($rows) . " rows, delete_candidates=$nDel (after_id=$afterId) " . round(microtime(true) - $t0, 2) . "s");

	if ($nDel === 0) {
		continue;
	}

	if ($dry) {
		$deleted += $nDel;
		out("DRY: would delete $nDel (total $deleted)");
		continue;
	}

	$in = implode(",", $toDelete);

	@mysqli_query($con, "DELETE FROM `news_cat` WHERE `news_id` IN ($in)");
	@mysqli_query($con, "DELETE FROM `comments` WHERE `newsid` IN ($in)");
	// Skip news_views (too slow without index)

	if (!mysqli_query($con, "DELETE FROM `news` WHERE `newsid` IN ($in)")) {
		out("DELETE news failed: " . mysqli_error($con));
		exit(1);
	}
	$deleted += $nDel;

	if (!$dbOnly && $images) {
		foreach ($images as $rel) {
			nm_safe_unlink($rel);
		}
	}

	out("loop $loops: deleted_batch=$nDel total_deleted=$deleted skipped_views=$skipped");
}

out("FINISHED scanned=$scanned deleted=$deleted skipped_protected=$skipped");
out("If --fast/--db-only: image files may remain under images/news (orphan cleanup later).");
