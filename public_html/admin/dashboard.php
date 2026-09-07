<?php

    include"config.php";

	if (!isset($_SESSION['aemail'])) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../manage.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['aemail']);
		header("location: ../manage.php");
	}

$usersession=$_SESSION['aemail'];

$res=mysqli_query($con,"SELECT * FROM admin WHERE aemail='$usersession'");

$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);

/** Safe COUNT for optional/missing tables (PHP 8+ mysqli throws; @ does not help). */
function nm_dash_count(mysqli $con, string $sql): int {
	try {
		$result = mysqli_query($con, $sql);
		if ($result && ($row = mysqli_fetch_assoc($result))) {
			return (int)$row['c'];
		}
	} catch (Throwable $e) {
		// missing table / DB error — show 0
	}
	return 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
  <link rel="stylesheet" href="css/admin-modern.css?v=7">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css"> 
<link rel="stylesheet" href="../include/css/jquery-ui.css">
<script src="../include/js/jquery.min.js"></script>

 <script>
  $( function() {
    $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
    
    $( "#datepicker1" ).datepicker({ dateFormat: 'yy-mm-dd' });
      
  } );
  </script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar  -->
        <?php include"sidebar.php"; ?>

        <!-- Page Content  -->
        <div id="content">

            <?php include"header.php"; ?>
            
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            </ol>
    <div class="nm-dash-wrap">
        <h2>Dashboard</h2>
        <p class="nm-dash-sub">Overview of your CMS · live MySQL counts</p>

        <div class="nm-dash-grid">
            <div class="nm-stat nm-stat--focus" style="--nm-stat-accent:#1565c0">
                <i class="dash-icon fas fa-file-alt"></i>
                <h3>Content news</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news WHERE newstype='Content'")); ?></h4>
            </div>
            <div class="nm-stat" style="--nm-stat-accent:#c62828">
                <i class="dash-icon fas fa-video"></i>
                <h3>Video news</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news WHERE newstype='Video'")); ?></h4>
            </div>
            <div class="nm-stat nm-stat--focus" style="--nm-stat-accent:#455a64">
                <i class="dash-icon fas fa-bell"></i>
                <h3>Push subscribers</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM tokens")); ?></h4>
            </div>
            <div class="nm-stat nm-stat--focus" style="--nm-stat-accent:#6d4c41">
                <i class="dash-icon fas fa-broom"></i>
                <h3>Old news (6mo+)</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news WHERE STR_TO_DATE(`date`,'%d-%m-%Y') < DATE_SUB(CURDATE(), INTERVAL 6 MONTH)")); ?></h4>
                <p><a href="cleanup_news.php">Open cleanup →</a></p>
            </div>
            <div class="nm-stat" style="--nm-stat-accent:#2e7d32">
                <i class="dash-icon fas fa-address-card"></i>
                <h3>Team members</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM `team`")); ?></h4>
            </div>
            <div class="nm-stat" style="--nm-stat-accent:#00838f">
                <i class="dash-icon fas fa-eye"></i>
                <h3>Post views (approx.)</h3>
                <h4><?php
                    echo number_format(nm_dash_count($con, "SELECT TABLE_ROWS AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news_views'"));
                ?></h4>
            </div>
            <div class="nm-stat" style="--nm-stat-accent:#ad1457">
                <i class="dash-icon fas fa-comments"></i>
                <h3>Comments</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM `comments`")); ?></h4>
            </div>
            <div class="nm-stat" style="--nm-stat-accent:#5d4037">
                <i class="dash-icon fas fa-user-friends"></i>
                <h3>Users</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM `users`")); ?></h4>
            </div>
        </div>

        <h3 class="nm-quick-heading">Quick links</h3>
        <div class="nm-quick">
          <a class="nm-quick--primary" href="add_news.php"><i class="fas fa-plus"></i> Add news</a>
          <a class="nm-quick--primary" href="news.php"><i class="fas fa-newspaper"></i> Manage news</a>
          <a href="cleanup_news.php"><i class="fas fa-broom"></i> Cleanup</a>
          <a href="categories.php"><i class="fas fa-folder-open"></i> Categories</a>
          <a href="notification.php"><i class="fas fa-bell"></i> Notification</a>
          <a href="ads.php"><i class="fas fa-ad"></i> Ads</a>
        </div>
    </div>
            <br>
            <?php include"footer.php"; ?>
        </div>
    </div>

    <!-- jQuery CDN - Slim version (=without AJAX) -->
    

    <script type="text/javascript">
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
    
<script>
$(document).ready(function() {
    $('#table').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]
    } );
} );    
</script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="../include/js/bootstrap.min.js"></script>
  <script src="js/all.js"></script>
  <!-- Font Awesome JS -->
   
<script src="../include/js/jquery-ui.js"></script>
    
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
</body>
</html>