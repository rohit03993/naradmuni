<?php
    include"config.php";
    if (!function_exists('nm_h')) {
        require_once __DIR__ . '/admin_helpers.php';
    }

	if (!isset($_SESSION['aemail'])) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../manage.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['aemail']);
		header("location: ../manage.php");
	}

 if(!isset($_SESSION['aemail']))
 {
  echo ("<script language='javascript'>
                   window.location.href='logout.php';
                        </script>");
 }

$productsession=$_SESSION['aemail'];

$res=mysqli_query($con,"SELECT * FROM admin WHERE aemail='$productsession'");

$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);

$srid=(int) (isset($_GET['eid']) ? $_GET['eid'] : 0);
$qry="SELECT * FROM `categories` WHERE id='$srid' LIMIT 1";
$ex=mysqli_query($con,$qry);
$rs=($ex instanceof mysqli_result) ? mysqli_fetch_array($ex) : null;
if (!$rs) {
	header('location: categories.php');
	exit;
}

if(isset($_POST['update']))
                    {
                            $category = mysqli_real_escape_string($con, (string) ($_POST['category'] ?? ($rs['maincat'] ?? '')));
                            $short=mysqli_real_escape_string($con, (string) ($_POST['short'] ?? ($rs['short'] ?? '0')));
                            $menu = (isset($_POST['menu']) && $_POST['menu'] === 'Yes') ? 'Yes' : 'No';
                            $metat = mysqli_real_escape_string($con, (string) ($_POST['metat'] ?? ($rs['metat'] ?? '')));
                            $metad = mysqli_real_escape_string($con, (string) ($_POST['metad'] ?? ($rs['metad'] ?? '')));
                            $hindi_name = mysqli_real_escape_string($con, trim((string) ($_POST['hindi_name'] ?? '')));
                            $linkname = mysqli_real_escape_string($con, (string) ($rs['cat_url'] ?? ''));
                            $parent = isset($_POST['parent']) ? (string) $_POST['parent'] : '0';
                            if (!ctype_digit($parent)) {
                                $parent = '0';
                            }
                            $latter = mysqli_real_escape_string($con, (string) ($_POST['latter'] ?? ($rs['latter'] ?? '')));

                            if ($hindi_name === '') {
                                nm_js_notice('Hindi name is required.', 'edit_category.php?eid=' . (int) $srid, 'error');
                                exit;
                            }

                            $escP = mysqli_real_escape_string($con, $parent);
                            $up="UPDATE `categories` SET `short`='$short',`menu`='$menu',`maincat`='$category',`parent`='$escP',`cat_url`='$linkname',`metat`='$metat',`metad`='$metad',`hindi_name`='$hindi_name',`latter`='$latter' WHERE `id`='$srid' LIMIT 1";

                            $ex= mysqli_query($con,$up);

                              if($ex) {
                                   nm_js_notice('Updated Successfully', 'categories.php');
                                   exit;
                              } else {
                                   nm_js_notice('Sorry, there was an error.', 'edit_category.php?eid=' . (int) $srid, 'error');
                                   exit;
                              }




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
<link rel="stylesheet" href="../include/css/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.5/css/responsive.dataTables.min.css">
<script src="../include/js/jquery.min.js"></script>
<script>

    $( function() {
    $( "#datepicker" ).datepicker({ yearRange: "-100:+0",changeMonth: true,
    changeYear: true,
    dateFormat: 'yy-mm-dd' });
    $( "#datepicker1" ).datepicker({ dateFormat: 'yy-mm-dd' });
    $( "#datepicker2" ).datepicker({ dateFormat: 'yy-mm-dd' });
    $( "#datepicker3" ).datepicker({ dateFormat: 'yy-mm-dd' });
    });
</script>
</head>
<body>
<div id="overlay"><div><img src="img/loading.gif" width="64px" height="64px"/></div></div>
    <div class="wrapper">
        <!-- Sidebar  -->
        <?php include"sidebar.php"; ?>

        <!-- Page Content  -->
<div id="content">
            <?php include"header.php"; ?>
            
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Edit Categories</li>
            </ol>
<div class="container-fluid page-content">
<?php
$curParent = isset($rs["parent"]) ? (string) $rs["parent"] : "0";
if ($curParent === "") {
    $curParent = "0";
}
$curMenu = (isset($rs["menu"]) && $rs["menu"] === "Yes") ? "Yes" : "No";
?>
            <form id="SubmitForm" method="post">

            <div class="col-md-12 form-group group">
              <label class="control-label">Hindi Name:</label>
              <input type="text" class="form-control" name="hindi_name" required value="<?php echo nm_h(isset($rs["hindi_name"]) ? $rs["hindi_name"] : ""); ?>">
            </div>

            <div class="col-md-12 form-group group">
              <label class="control-label">Category URL:</label>
              <input type="text" class="form-control" value="<?php echo nm_h(isset($rs["cat_url"]) ? $rs["cat_url"] : ""); ?>" readonly>
              <small class="form-text text-muted">Locked so existing /category/ and news links stay the same.</small>
            </div>

            <div class="col-md-6 form-group group">
              <label class="control-label">Parent Category:</label>
               <select class="custom-select" id="maincat" name="parent">
                <option value="0"<?php echo $curParent === "0" ? " selected" : ""; ?>>None</option>
            	<?php
                $query = nm_categories_result($con);
                if ($query instanceof mysqli_result && $query->num_rows > 0) {
                    while($row = $query->fetch_assoc()){
                        if ((int)$row["id"] === $srid) {
                            continue;
                        }
                        $sel = ((string)$row["id"] === $curParent) ? " selected" : "";
                        echo '<option value="'.(int)$row['id'].'"'.$sel.'>'.nm_h(nm_cat_label($row)).'</option>';
                    }
                }
                ?>
            </select>
            </div>

            <div class="col-md-6 form-group group">
            <label class="control-label">Show In Menu</label>
            <select class="custom-select" name="menu">
                <option value="No"<?php echo $curMenu === "No" ? " selected" : ""; ?>>No</option>
            	<option value="Yes"<?php echo $curMenu === "Yes" ? " selected" : ""; ?>>Yes</option>
            </select>
            </div>

            <div class="col-md-12 form-group group">
            <details>
              <summary style="cursor:pointer;font-weight:600;margin-bottom:12px;">Advanced (existing values, rarely needed)</summary>
            <div class="col-md-4 form-group group">
              <label class="control-label">Sort Order:</label>
              <input class="form-control" type="text" name="short" value="<?php echo nm_h(isset($rs["short"]) ? $rs["short"] : ""); ?>">
            </div>
            <div class="col-md-4 form-group group">
              <label class="control-label">English Name:</label>
              <input type="text" class="form-control" name="category" value="<?php echo nm_h(isset($rs["maincat"]) ? $rs["maincat"] : ""); ?>">
            </div>
            <div class="col-md-4 form-group group">
              <label class="control-label">Starting Alphabate:</label>
              <input type="text" class="form-control" name="latter" value="<?php echo nm_h(isset($rs["latter"]) ? $rs["latter"] : ""); ?>">
            </div>
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Title:</label>
              <input class="form-control" type="text" name="metat" value="<?php echo nm_h(isset($rs["metat"]) ? $rs["metat"] : ""); ?>">
            </div>
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Description:</label>
             <textarea class="form-control" cols="20" rows="5" name="metad"><?php echo nm_h(isset($rs["metad"]) ? $rs["metad"] : ""); ?></textarea>
            </div>
            </details>
            </div>

            <div class="col-md-12 form-group group">
                <input type="text" name="update" id="actions" class="hidden" value="update" hidden>
                <button type="submit" name="submit" class="btn btn-info">Update</button>
            </div>

        </form>
</div>
</div>			

    

</div>
<?php include"footer.php"; ?>
<script type="text/javascript">
        $(document).ready(function () {
            $('#sidebar').toggleClass('');
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="../include/js/bootstrap.min.js"></script>
  <!-- Font Awesome JS -->
    <script src="js/all.js"></script>
<script src="../include/js/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.5/js/dataTables.responsive.min.js"></script>
    
</body>
</html>