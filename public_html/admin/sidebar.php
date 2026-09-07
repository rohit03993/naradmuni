<?php
if (!defined("NM_ADMIN_ASSETS")) {
	define("NM_ADMIN_ASSETS", true);
	echo '<link rel="stylesheet" href="css/admin-modern.css?v=9">' . "\n";
}
$nmPage = basename(isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : "");
$nmAvatar = "";
if (!empty($userRow["image"])) {
	$nmAvatar = "profile/" . $userRow["image"];
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
    <?php if ($nmAvatar && is_file(__DIR__ . "/" . $nmAvatar)) { ?>
      <img class="sidebar-avatar" src="<?php echo htmlspecialchars($nmAvatar); ?>" alt="Admin" width="64" height="64">
    <?php } else { ?>
      <div class="sidebar-avatar" style="display:inline-flex;align-items:center;justify-content:center;font-weight:700;color:#fff;">N</div>
    <?php } ?>
    <a class="sidebar-brand" href="dashboard.php">
      Naradmuni
      <span>Admin CMS</span>
    </a>
  </div>

  <ul class="list-unstyled components">
    <p class="nav-label">Overview</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "dashboard.php"); ?>" href="dashboard.php">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
    </li>

    <p class="nav-label">Content</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("categories.php", "edit_category.php")); ?>" href="categories.php">
        <i class="fas fa-folder-open"></i> Categories
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("news.php", "add_news.php", "edit_news.php")); ?>" href="news.php">
        <i class="fas fa-newspaper"></i> News
      </a>
    </li>
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
      <a class="<?php echo nm_nav_active($nmPage, "team.php"); ?>" href="team.php">
        <i class="fas fa-users"></i> Team
      </a>
    </li>
  </ul>
</nav>
