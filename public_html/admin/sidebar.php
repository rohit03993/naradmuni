<?php
if (!function_exists('nm_h')) {
	require_once __DIR__ . '/admin_helpers.php';
}
if (!defined("NM_ADMIN_ASSETS")) {
	define("NM_ADMIN_ASSETS", true);
	echo '<link rel="stylesheet" href="css/admin-modern.css?v=21">' . "\n";
	echo '<script src="js/nm-dialog.js?v=1"></script>' . "\n";
}
$nmPage = basename(isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : "");
if (!isset($nmMe) || !is_array($nmMe)) {
	$nmMe = function_exists('nm_cms_identity') ? nm_cms_identity($con, isset($userRow) ? $userRow : null) : array(
		'name' => 'Admin', 'role' => 'Admin', 'is_admin' => true, 'team_id' => 0, 'avatar' => '', 'initial' => 'N',
	);
}
$nmIsAdmin = !empty($nmMe['is_admin']);
$nmAdminOnly = array(
	'categories.php', 'edit_category.php', 'cleanup_news.php', 'rashifal.php', 'edit_rashifal.php',
	'pages.php', 'add_pages.php', 'edit_pages.php', 'video.php', 'notification.php', 'comments.php',
	'post_views.php', 'rss_link.php', 'add_rss_link.php', 'edit_rss_link.php', 'ads.php',
	'youtube_shorts.php', 'branding.php', 'whatsapp_share.php', 'team.php', 'admin_users.php',
	'slider.php', 'users.php', 'jobs.php',
);
if (!$nmIsAdmin && in_array($nmPage, $nmAdminOnly, true)) {
	nm_require_admin($con);
}
if (!function_exists("nm_nav_active")) {
	function nm_nav_active($page, $files) {
		$files = (array) $files;
		return in_array($page, $files, true) ? " is-active" : "";
	}
}
?>
<nav id="sidebar">
  <div class="sidebar-header">
    <?php if (!empty($nmMe['avatar'])) { ?>
      <img class="sidebar-avatar" src="<?php echo htmlspecialchars($nmMe['avatar']); ?>" alt="" width="64" height="64">
    <?php } else { ?>
      <div class="sidebar-avatar sidebar-avatar--letter"><?php echo htmlspecialchars($nmMe['initial']); ?></div>
    <?php } ?>
    <div class="sidebar-who">
      <strong><?php echo htmlspecialchars($nmMe['name']); ?></strong>
      <span><?php echo htmlspecialchars($nmMe['role']); ?></span>
    </div>
    <a class="sidebar-brand" href="dashboard.php">Naradmuni</a>
  </div>

  <ul class="list-unstyled components">
    <p class="nav-label">Overview</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "dashboard.php"); ?>" href="dashboard.php">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
    </li>

    <p class="nav-label">Content</p>
    <?php if ($nmIsAdmin) { ?>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("categories.php", "edit_category.php")); ?>" href="categories.php">
        <i class="fas fa-folder-open"></i> Categories
      </a>
    </li>
    <?php } ?>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("news.php", "add_news.php", "edit_news.php")); ?>" href="news.php">
        <i class="fas fa-newspaper"></i> News
      </a>
    </li>
    <?php if ($nmIsAdmin) { ?>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "cleanup_news.php"); ?>" href="cleanup_news.php">
        <i class="fas fa-broom"></i> Cleanup old news
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("rashifal.php", "edit_rashifal.php")); ?>" href="rashifal.php">
        <i class="fas fa-star"></i> Rashifal
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("pages.php", "add_pages.php", "edit_pages.php")); ?>" href="pages.php">
        <i class="fas fa-file-alt"></i> Pages
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "video.php"); ?>" href="video.php">
        <i class="fas fa-video"></i> Upload video
      </a>
    </li>
    <?php } ?>

    <p class="nav-label">You</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "my_profile.php"); ?>" href="my_profile.php">
        <i class="fas fa-user"></i> My profile
      </a>
    </li>

    <?php if ($nmIsAdmin) { ?>
    <p class="nav-label">Engagement</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "notification.php"); ?>" href="notification.php">
        <i class="fas fa-bell"></i> Notification
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "comments.php"); ?>" href="comments.php">
        <i class="fas fa-comments"></i> Comments
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "post_views.php"); ?>" href="post_views.php">
        <i class="fas fa-chart-bar"></i> Post views
      </a>
    </li>

    <p class="nav-label">Site</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("rss_link.php", "add_rss_link.php", "edit_rss_link.php")); ?>" href="rss_link.php">
        <i class="fas fa-rss"></i> Aggregator
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "ads.php"); ?>" href="ads.php">
        <i class="fas fa-ad"></i> Ads
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "youtube_shorts.php"); ?>" href="youtube_shorts.php">
        <i class="fab fa-youtube"></i> YouTube Shorts
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "branding.php"); ?>" href="branding.php">
        <i class="fas fa-image"></i> Branding
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "whatsapp_share.php"); ?>" href="whatsapp_share.php">
        <i class="fab fa-whatsapp"></i> WhatsApp share
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "team.php"); ?>" href="team.php">
        <i class="fas fa-users"></i> Team
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "admin_users.php"); ?>" href="admin_users.php">
        <i class="fas fa-user-shield"></i> Admin users
      </a>
    </li>
    <?php } ?>
  </ul>
</nav>
