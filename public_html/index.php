<?php
/**
 * Old PHP homepage disabled — public site is Next.js on :3000.
 * Files kept on disk; this only redirects. Article paths stay /news/{slug}.
 */
include __DIR__ . "/admin/config.php";
header("Location: " . $publicroot, true, 302);
exit;
