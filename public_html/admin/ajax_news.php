<?php
include "config.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["aemail"]) || $_SESSION["aemail"] === "") {
	echo json_encode(array("ok" => false, "message" => "Log in first.", "newsid" => 0, "files_removed" => 0));
	exit;
}

$newsid = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
require_once __DIR__ . "/news_media.php";

// Any logged-in CMS user (Admin or Author) can delete from the News list.
$result = nm_delete_news_article($con, $newsid, null, array("skip_view_check" => true));

echo json_encode(array(
    "ok" => !empty($result["ok"]),
    "message" => isset($result["message"]) ? $result["message"] : "",
    "newsid" => isset($result["newsid"]) ? (int) $result["newsid"] : $newsid,
    "files_removed" => isset($result["files_removed"]) ? (int) $result["files_removed"] : 0,
));
