<?php
include __DIR__ . "/../admin/config.php";
$slug = isset($_GET["url"]) ? trim($_GET["url"]) : "";
// Public /page/{page_url} is rendered by Next.js. This file is only reached if
// Next is down or Apache is hit directly — send to the same path, not home.
if ($slug !== "") {
	header("Location: " . rtrim((string) $publicroot, "/") . "/page/" . rawurlencode($slug), true, 302);
} else {
	header("Location: " . $publicroot, true, 302);
}
exit;
