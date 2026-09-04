<?php
include "config.php";
$newsid = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
require_once __DIR__ . "/news_media.php";
$result = nm_delete_news_article($con, $newsid);
echo $result["ok"] ? $result["message"] : ("Error: " . $result["message"]);
