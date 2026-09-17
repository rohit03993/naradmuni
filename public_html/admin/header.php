<?php
date_default_timezone_set("Asia/Kolkata");
if (!function_exists('nm_h')) {
	require_once __DIR__ . '/admin_helpers.php';
}
if (!isset($nmMe) || !is_array($nmMe)) {
	$nmMe = function_exists('nm_cms_identity') ? nm_cms_identity($con, isset($userRow) ? $userRow : null) : array(
		'name' => 'Admin', 'role' => 'Admin', 'is_admin' => true, 'avatar' => '', 'initial' => 'N',
	);
}
$nmAdminName = $nmMe['name'];
if (!defined("NM_ADMIN_ASSETS")) {
	define("NM_ADMIN_ASSETS", true);
	echo '<link rel="stylesheet" href="css/admin-modern.css?v=20">' . "\n";
	echo '<script src="js/nm-dialog.js?v=1"></script>' . "\n";
}
?>
<nav class="navbar navbar-expand-lg navbar-light nm-topbar">
  <div class="container-fluid">
    <div class="nm-topbar-left">
      <button type="button" id="sidebarCollapse" class="btn btn-info">
        <i class="fas fa-bars"></i>
        <span>Menu</span>
      </button>
    </div>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle nm-topbar-user" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <?php if (!empty($nmMe['avatar'])) { ?>
            <img class="nm-topbar-avatar" src="<?php echo htmlspecialchars($nmMe['avatar']); ?>" alt="">
          <?php } else { ?>
            <span class="nm-topbar-avatar nm-topbar-avatar--letter"><?php echo htmlspecialchars($nmMe['initial']); ?></span>
          <?php } ?>
          <?php echo htmlspecialchars($nmAdminName); ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
          <a href="my_profile.php" class="dropdown-item"><i class="fa fa-user"></i> My profile</a>
          <a href="settings.php" class="dropdown-item"><i class="fa fa-cog"></i> Settings</a>
          <div class="dropdown-divider"></div>
          <a href="logout.php?logout='1'" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Log out</a>
        </div>
      </li>
    </ul>
  </div>
</nav>
<style>
  tr { font-size: 13px; }
</style>
