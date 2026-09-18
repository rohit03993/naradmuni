<?php
include "config.php";
require_once __DIR__ . "/admin_helpers.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["aemail"])) {
	http_response_code(401);
	echo json_encode(array("ok" => false, "message" => "Login required."));
	exit;
}

if (!nm_is_admin($con)) {
	http_response_code(403);
	echo json_encode(array("ok" => false, "message" => "Admin only."));
	exit;
}

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
if ($id < 1) {
	echo json_encode(array("ok" => false, "message" => "Missing page id."));
	exit;
}

$ok = mysqli_query($con, "DELETE FROM `pages` WHERE `p_id`='$id' LIMIT 1");
if ($ok && mysqli_affected_rows($con) > 0) {
	echo json_encode(array("ok" => true, "message" => "Page deleted."));
	exit;
}

echo json_encode(array("ok" => false, "message" => "Could not delete."));
