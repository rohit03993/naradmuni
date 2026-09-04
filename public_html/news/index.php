<?php
/**
 * Old PHP article skin disabled — redirect to Next.js.
 * Path preserved: /news/{newsurl}  (SEO-safe)
 */
include_once __DIR__ . "/../admin/config.php";
$slug = isset($_GET["url"]) ? trim($_GET["url"]) : "";
if ($slug === "") {
  header("Location: " . $publicroot, true, 302);
  exit;
}
header("Location: " . $publicroot . "news/" . $slug, true, 302);
exit;
