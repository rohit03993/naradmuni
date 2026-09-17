<?php
include "config.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["aemail"]) || $_SESSION["aemail"] === "") {
	echo json_encode(array("ok" => false, "message" => "Log in first.", "newsid" => 0, "files_removed" => 0));
	exit;
}

$newsid = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
if (!function_exists('nm_can_manage_news')) {
	require_once __DIR__ . '/admin_helpers.php';
}
if (!nm_can_manage_news($con, $newsid)) {
	echo json_encode(array("ok" => false, "message" => "You can only delete your own news.", "newsid" => $newsid, "files_removed" => 0));
	exit;
}

require_once __DIR__ . "/news_media.php";
$result = nm_delete_news_article($con, $newsid, null, array("skip_view_check" => true));

echo json_encode(array(
    "ok" => !empty($result["ok"]),
    "message" => isset($result["message"]) ? $result["message"] : "",
    "newsid" => isset($result["newsid"]) ? (int) $result["newsid"] : $newsid,
    "files_removed" => isset($result["files_removed"]) ? (int) $result["files_removed"] : 0,
));
