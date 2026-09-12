<?php
/**
 * Auto-publish scheduled news when pub_date_time <= now.
 *
 * CLI (recommended cron):
 *   * * * * * cd /home/thenaradmuni/htdocs/www.thenaradmuni.com && php public_html/admin/cron_publish_scheduled.php
 *
 * Or HTTP (optional):
 *   /naradmuni/admin/cron_publish_scheduled.php?key=YOUR_SECRET
 *   Secret is stored in site_settings.cron_publish_key (auto-created on first CLI run).
 */
@ini_set('memory_limit', '128M');
@set_time_limit(60);

$isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/site_settings_lib.php';

nm_ensure_site_settings($con);

if (!$isCli) {
	header('Content-Type: text/plain; charset=utf-8');
	$stored = nm_setting_get($con, 'cron_publish_key', '');
	if ($stored === '') {
		$stored = bin2hex(random_bytes(16));
		nm_setting_set($con, 'cron_publish_key', $stored);
	}
	$given = isset($_GET['key']) ? (string) $_GET['key'] : '';
	if (!hash_equals($stored, $given)) {
		http_response_code(403);
		echo "Forbidden\n";
		exit;
	}
} else {
	// Ensure a key exists for optional HTTP use
	if (nm_setting_get($con, 'cron_publish_key', '') === '') {
		nm_setting_set($con, 'cron_publish_key', bin2hex(random_bytes(16)));
	}
}

$now = date('Y-m-d H:i:s');
// pub_date_time is stored as Y-m-d H:i — compare as datetime
$sql = "SELECT `newsid`, `title`, `short_description`, `image`, `newsurl`, `status`, `pub_date_time`
	FROM `news`
	WHERE `status` = 'Scheduled'
	  AND `pub_date_time` IS NOT NULL
	  AND TRIM(`pub_date_time`) != ''
	  AND STR_TO_DATE(REPLACE(`pub_date_time`, 'T', ' '), '%Y-%m-%d %H:%i') <= NOW()
	ORDER BY `newsid` ASC
	LIMIT 50";

$q = mysqli_query($con, $sql);
if (!$q) {
	echo "Query error: " . mysqli_error($con) . "\n";
	exit(1);
}

$done = 0;
$pushFile = __DIR__ . '/push_news.php';
while ($row = mysqli_fetch_assoc($q)) {
	$id = (int) $row['newsid'];
	if ($id <= 0) {
		continue;
	}
	$ok = mysqli_query(
		$con,
		"UPDATE `news` SET `status`='Published' WHERE `newsid`='$id' AND `status`='Scheduled' LIMIT 1"
	);
	if (!$ok || mysqli_affected_rows($con) < 1) {
		continue;
	}
	$done++;
	if (is_file($pushFile)) {
		include_once $pushFile;
		if (function_exists('naradmuni_send_news_push')) {
			@naradmuni_send_news_push(
				$con,
				(string) $row['title'],
				(string) $row['short_description'],
				(string) $row['image'],
				(string) $row['newsurl']
			);
		}
	}
	echo "Published news #$id (" . $row['newsurl'] . ") scheduled for " . $row['pub_date_time'] . "\n";
}

echo "Done. Published $done scheduled item(s) at $now\n";
exit(0);
