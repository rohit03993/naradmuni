<?php
include __DIR__ . "/../admin/config.php";
$slug = isset($_GET["url"]) ? trim($_GET["url"]) : "";
// Static pages not yet on Next — send to home for now (files kept)
header("Location: " . $publicroot, true, 302);
exit;
