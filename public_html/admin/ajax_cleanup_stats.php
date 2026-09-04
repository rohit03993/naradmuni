<?php
include "config.php";
require_once __DIR__ . "/news_media.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["aemail"])) {
	echo json_encode(array("ok" => false, "message" => "Not logged in"));
	exit;
}

$months = isset($_POST["months"]) ? max(1, (int) $_POST["months"]) : 6;
$offset = isset($_POST["offset"]) ? max(0, (int) $_POST["offset"]) : 0;
$limit = isset($_POST["limit"]) ? min(500, max(50, (int) $_POST["limit"])) : 300;

$where = nm_age_where_sql($months);

// Fast totals once (offset 0)
$posts = 0;
$withImage = 0;
if ($offset === 0) {
	$q = mysqli_query($con, "SELECT COUNT(*) AS c,
		SUM(CASE WHEN `image` IS NOT NULL AND TRIM(`image`) != '' AND `image` != 'null' THEN 1 ELSE 0 END) AS wi
		FROM news WHERE $where");
	if ($q && ($r = mysqli_fetch_assoc($q))) {
		$posts = (int) $r["c"];
		$withImage = (int) $r["wi"];
	}
}

$sql = "SELECT `image`, `video_file` FROM news WHERE $where ORDER BY newsid ASC LIMIT $offset, $limit";
$res = mysqli_query($con, $sql);
$bytes = 0;
$filesFound = 0;
$scanned = 0;
while ($res && ($row = mysqli_fetch_assoc($res))) {
	$scanned++;
	$b = nm_featured_bytes($row["image"], $row["video_file"]);
	if ($b > 0) {
		$filesFound++;
		$bytes += $b;
	}
}

$done = ($scanned < $limit);
echo json_encode(array(
	"ok" => true,
	"posts" => $posts,
	"with_featured" => $withImage,
	"offset" => $offset,
	"next_offset" => $offset + $scanned,
	"scanned" => $scanned,
	"chunk_bytes" => $bytes,
	"chunk_files" => $filesFound,
	"done" => $done,
	"note" => "Size is featured images + videos on disk (body embeds not fully scanned for speed).",
));
