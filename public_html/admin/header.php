<?php
$date = date_default_timezone_set("Asia/Kolkata");
$nmAdminName = !empty($userRow["aname"]) ? $userRow["aname"] : (!empty($userRow["aemail"]) ? $userRow["aemail"] : "Admin");
if (!defined("NM_ADMIN_ASSETS")) {
	define("NM_ADMIN_ASSETS", true);
	echo '<link rel="stylesheet" href="css/admin-modern.css?v=4">' . "\n";
}
?>
<nav class="navbar navbar-expand-lg navbar-light nm-topbar">
  <div class="container-fluid">
    <div class="nm-topbar-left">
      <button type="button" id="sidebarCollapse" class="btn btn-info">
        <i class="fas fa-bars"></i>
        <span>Menu</span>
      </button>
      <div class="nm-clock" title="Server time">
        <div class="nm-clock-label">Time</div>
        <div id="TimestatusMsg">—</div>
      </div>
    </div>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user-circle"></i>
          <?php echo htmlspecialchars($nmAdminName); ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
          <a href="settings.php" class="dropdown-item"><i class="fa fa-cog"></i> Settings</a>
          <a href="profile.php" class="dropdown-item"><i class="fa fa-user"></i> Profile</a>
          <div class="dropdown-divider"></div>
          <a href="logout.php?logout='1'" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Log out</a>
        </div>
      </li>
    </ul>
  </div>
</nav>
<script type="text/javascript">
(function () {
  function nmUpdateClock() {
    var el = document.getElementById("TimestatusMsg");
    if (!el) return;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "check.php", true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        el.innerHTML = xhr.responseText;
      }
    };
    xhr.send(null);
  }
  nmUpdateClock();
  setInterval(nmUpdateClock, 30000);
})();
</script>
<style>
  tr { font-size: 13px; }
</style>
