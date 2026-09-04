<?php
/**
 * Old PHP category skin disabled — redirect to Next.js.
 * Path preserved: /category/{cat_url}
 */
include __DIR__ . "/../admin/config.php";
$slug = isset($_GET["url"]) ? trim($_GET["url"]) : "";
if ($slug === "") {
  header("Location: " . $publicroot, true, 302);
  exit;
}
header("Location: " . $publicroot . "category/" . $slug, true, 302);
exit;
