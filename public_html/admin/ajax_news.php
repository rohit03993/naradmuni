<?php
include "config.php";
header("Content-Type: application/json; charset=utf-8");

$newsid = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
require_once __DIR__ . "/news_media.php";

// Manual trash from News list: one confirm already happened — skip slow view scan
$result = nm_delete_news_article($con, $newsid, null, array("skip_view_check" => true));

echo json_encode(array(
    "ok" => !empty($result["ok"]),
    "message" => isset($result["message"]) ? $result["message"] : "",
    "newsid" => isset($result["newsid"]) ? (int) $result["newsid"] : $newsid,
    "files_removed" => isset($result["files_removed"]) ? (int) $result["files_removed"] : 0,
));
