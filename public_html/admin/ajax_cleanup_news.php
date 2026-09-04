<?php
include "config.php";
require_once __DIR__ . "/news_media.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["aemail"])) {
	echo json_encode(array("ok" => false, "message" => "Not logged in"));
	exit;
}

if (!isset($_POST["confirm"]) || $_POST["confirm"] !== "CONFIRM") {
	echo json_encode(array("ok" => false, "message" => "Type CONFIRM to delete"));
	exit;
}

$mode = isset($_POST["mode"]) ? $_POST["mode"] : "ids";
$limit = isset($_POST["limit"]) ? min(50, max(1, (int) $_POST["limit"])) : 25;
$deleted = 0;
$skipped = 0;
$files = 0;
$bytes = 0;
$errors = array();
$remaining = null;
$afterId = 0;

if ($mode === "all") {
	$months = isset($_POST["months"]) ? max(1, (int) $_POST["months"]) : 6;
	$afterId = isset($_POST["after_id"]) ? max(0, (int) $_POST["after_id"]) : 0;
	$where = nm_age_where_sql($months);
	// Cursor by newsid so protected (3k+ views) posts are skipped without infinite loop
	$q = mysqli_query(
		$con,
		"SELECT `newsid` FROM news WHERE $where AND `newsid` > $afterId ORDER BY `newsid` ASC LIMIT $limit"
	);
	$ids = array();
	while ($q && ($r = mysqli_fetch_assoc($q))) {
		$ids[] = (int) $r["newsid"];
	}
	$viewMap = nm_batch_view_counts($con, $ids);
	$maxId = $afterId;
	foreach ($ids as $id) {
		$maxId = max($maxId, $id);
		if (nm_is_view_protected(isset($viewMap[$id]) ? $viewMap[$id] : 0)) {
			$skipped++;
			continue;
		}
		$res = nm_delete_news_article($con, $id);
		if ($res["ok"]) {
			$deleted++;
			$files += (int) $res["files_removed"];
			$bytes += isset($res["bytes_freed"]) ? (int) $res["bytes_freed"] : 0;
		} else {
			// Still protected at delete time, or missing
			if (strpos($res["message"], "Protected:") === 0) {
				$skipped++;
			} else {
				$errors[] = $res["message"];
			}
		}
	}
	$afterId = $maxId;
	$cr = mysqli_query($con, "SELECT COUNT(*) AS c FROM news WHERE $where AND `newsid` > $afterId");
	$row = $cr ? mysqli_fetch_assoc($cr) : null;
	$remaining = $row ? (int) $row["c"] : 0;
	$done = (count($ids) === 0);
} else {
	$ids = isset($_POST["ids"]) ? $_POST["ids"] : array();
	if (!is_array($ids)) {
		$ids = array($ids);
	}
	foreach ($ids as $id) {
		$id = (int) $id;
		if ($id <= 0) {
			continue;
		}
		$res = nm_delete_news_article($con, $id);
		if ($res["ok"]) {
			$deleted++;
			$files += (int) $res["files_removed"];
			$bytes += isset($res["bytes_freed"]) ? (int) $res["bytes_freed"] : 0;
		} else {
			if (strpos($res["message"], "Protected:") === 0) {
				$skipped++;
			} else {
				$errors[] = $res["message"];
			}
		}
	}
	$done = true;
}

echo json_encode(array(
	"ok" => true,
	"deleted" => $deleted,
	"skipped" => $skipped,
	"files_removed" => $files,
	"bytes_freed" => $bytes,
	"bytes_label" => nm_format_bytes($bytes),
	"remaining" => $remaining,
	"after_id" => $afterId,
	"done" => !empty($done) || ($remaining === 0),
	"message" => "Batch: deleted $deleted, skipped protected $skipped, files $files, freed " . nm_format_bytes($bytes)
		. ($remaining !== null ? (", remaining ~$remaining") : "")
		. (count($errors) ? (" | " . implode("; ", array_slice($errors, 0, 2))) : ""),
	"errors" => $errors,
));
