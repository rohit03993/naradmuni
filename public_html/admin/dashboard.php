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
if (!function_exists('nm_cms_identity')) {
	require_once __DIR__ . '/admin_helpers.php';
}
$nmMe = nm_cms_identity($con, $userRow);
$nmIsAdmin = !empty($nmMe['is_admin']);
$nmMine = (int) $nmMe['team_id'];
$nmMineWhere = $nmMine > 0 ? (" WHERE team_id='" . $nmMine . "'") : " WHERE team_id='-1'";

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
  <link rel="stylesheet" href="css/admin-modern.css?v=15">
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
        <div class="nm-dash-hero">
          <?php if (!empty($nmMe['avatar'])) { ?>
            <img src="<?php echo htmlspecialchars($nmMe['avatar']); ?>" alt="">
          <?php } else { ?>
            <span class="nm-dash-hero__letter"><?php echo htmlspecialchars($nmMe['initial']); ?></span>
          <?php } ?>
          <div>
            <h2>Namaste, <?php echo htmlspecialchars($nmMe['name']); ?></h2>
            <p class="nm-dash-sub"><?php echo $nmIsAdmin ? ($nmMine > 0 ? 'Admin · also publishing as this byline' : 'Admin · full site') : 'Your stories only'; ?></p>
          </div>
          <a class="btn btn-info" href="add_news.php"><i class="fas fa-plus"></i> Add news</a>
        </div>
        <?php if (!empty($_GET['denied'])) { ?>
          <div class="alert alert-warning">That section is for Admins only. You can use News and My profile.</div>
        <?php } ?>
        <?php if (!$nmIsAdmin && $nmMine < 1) { ?>
          <div class="alert alert-warning">Ask an Admin to link your login to a Team profile so your photo and stories appear here.</div>
        <?php } ?>

        <div class="nm-dash-grid">
            <?php if ($nmIsAdmin) { ?>
            <a class="nm-stat nm-stat--focus" style="--nm-stat-accent:#1565c0" href="news.php">
                <i class="dash-icon fas fa-check-circle"></i>
                <h3>Published</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news WHERE status='Published'")); ?></h4>
            </a>
            <a class="nm-stat" style="--nm-stat-accent:#0b6bcb" href="news.php">
                <i class="dash-icon fas fa-clock"></i>
                <h3>Scheduled</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news WHERE status='Scheduled'")); ?></h4>
            </a>
            <a class="nm-stat" style="--nm-stat-accent:#9a6700" href="news.php">
                <i class="dash-icon fas fa-eye-slash"></i>
                <h3>Unpublished</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news WHERE status='Unpublished'")); ?></h4>
            </a>
            <?php if ($nmMine > 0) { ?>
            <a class="nm-stat nm-stat--focus" style="--nm-stat-accent:#1b7a4a" href="news.php?author=me">
                <i class="dash-icon fas fa-user-edit"></i>
                <h3>Published by me</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news WHERE status='Published' AND team_id='$nmMine'")); ?></h4>
            </a>
            <a class="nm-stat" style="--nm-stat-accent:#5a6a7a" href="news.php?author=others">
                <i class="dash-icon fas fa-users"></i>
                <h3>By other authors</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news WHERE team_id<>'$nmMine' OR team_id IS NULL OR team_id=0")); ?></h4>
            </a>
            <?php } ?>
            <a class="nm-stat" style="--nm-stat-accent:#ad1457" href="comments.php">
                <i class="dash-icon fas fa-comments"></i>
                <h3>Comments</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM `comments`")); ?></h4>
            </a>
            <?php } else { ?>
            <a class="nm-stat nm-stat--focus" style="--nm-stat-accent:#1565c0" href="news.php">
                <i class="dash-icon fas fa-check-circle"></i>
                <h3>My published</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news" . $nmMineWhere . ($nmMine > 0 ? " AND status='Published'" : ""))); ?></h4>
            </a>
            <a class="nm-stat" style="--nm-stat-accent:#0b6bcb" href="news.php">
                <i class="dash-icon fas fa-clock"></i>
                <h3>My scheduled</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news" . $nmMineWhere . ($nmMine > 0 ? " AND status='Scheduled'" : ""))); ?></h4>
            </a>
            <a class="nm-stat" style="--nm-stat-accent:#9a6700" href="news.php">
                <i class="dash-icon fas fa-pen"></i>
                <h3>My unpublished</h3>
                <h4><?php echo number_format(nm_dash_count($con, "SELECT COUNT(*) AS c FROM news" . $nmMineWhere . ($nmMine > 0 ? " AND status='Unpublished'" : ""))); ?></h4>
            </a>
            <?php } ?>
        </div>

        <?php if ($nmIsAdmin) {
            $authorRows = array();
            $aq = @mysqli_query($con, "SELECT n.team_id, t.name,
                SUM(CASE WHEN n.status='Published' THEN 1 ELSE 0 END) AS pub,
                SUM(CASE WHEN n.status='Scheduled' THEN 1 ELSE 0 END) AS sch
                FROM news n
                LEFT JOIN team t ON t.t_id = n.team_id
                WHERE n.team_id > 0
                GROUP BY n.team_id, t.name
                ORDER BY pub DESC, sch DESC
                LIMIT 24");
            if ($aq instanceof mysqli_result) {
                while ($ar = mysqli_fetch_assoc($aq)) {
                    $authorRows[] = $ar;
                }
            }
            if ($authorRows) { ?>
        <h3 class="nm-quick-heading">Authors</h3>
        <p class="nm-dash-sub" style="margin-top:-8px;">Click a name to open their published and scheduled stories.</p>
        <div class="nm-author-pills">
          <?php foreach ($authorRows as $ar) {
              $tid = (int) $ar['team_id'];
              $label = trim((string) ($ar['name'] ?? ''));
              if ($label === '') {
                  $label = 'Author #' . $tid;
              }
              $mineCls = ($tid === $nmMine) ? ' is-me' : '';
              ?>
            <a class="nm-author-pill<?php echo $mineCls; ?>" href="news.php?author=<?php echo $tid; ?>">
              <strong><?php echo htmlspecialchars($label); ?></strong>
              <span><?php echo (int) $ar['pub']; ?> published · <?php echo (int) $ar['sch']; ?> scheduled</span>
            </a>
          <?php } ?>
        </div>
        <?php }
        } ?>

        <h3 class="nm-quick-heading">Quick links</h3>
        <div class="nm-quick">
          <a class="nm-quick--primary" href="add_news.php"><i class="fas fa-plus"></i> Add news</a>
          <a class="nm-quick--primary" href="news.php"><i class="fas fa-newspaper"></i> <?php echo $nmIsAdmin ? 'All news' : 'My news'; ?></a>
          <?php if ($nmIsAdmin && $nmMine > 0) { ?>
          <a href="news.php?author=me"><i class="fas fa-user-edit"></i> By me</a>
          <a href="news.php?author=others"><i class="fas fa-users"></i> By others</a>
          <?php } ?>
          <?php if ($nmIsAdmin) { ?>
          <a href="cleanup_news.php"><i class="fas fa-broom"></i> Cleanup</a>
          <a href="categories.php"><i class="fas fa-folder-open"></i> Categories</a>
          <?php } else { ?>
          <a href="my_profile.php"><i class="fas fa-user"></i> My profile</a>
          <?php } ?>
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