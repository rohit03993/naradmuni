<?php

    include"config.php";
 error_reporting(0);
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

$srid = isset($_GET['eid']) ? $_GET['eid'] : (isset($_GET['id']) ? $_GET['id'] : '');
$qry="SELECT * FROM `rashifal` WHERE r_id='".mysqli_real_escape_string($con, (string)$srid)."'";
$ex=mysqli_query($con,$qry);
$rs=mysqli_fetch_array($ex);
if (!is_array($rs)) { $rs = array(); }

if(isset($_POST['update']))
                    {     
                            $title = mysqli_real_escape_string($con,$_POST['title']);
                            $metat = mysqli_real_escape_string($con,$_POST['metat']);
                            $metad = mysqli_real_escape_string($con,$_POST['metad']);
                            $short_description = mysqli_real_escape_string($con,$_POST['short_description']);
                            
                        
                            if (empty($title)) { array_push($errors, "Kindly fill Video title"); }
                           
                            if (empty($short_description)) { array_push($errors, "Kindly fill description"); }
                            if (empty($metat)) { array_push($errors, "Kindly fill meta title"); } 
                             
    if (count($errors) == 0) {
        
                $up=("UPDATE `rashifal` SET `title`='$title',`short_description`='$short_description',`metat`='$metat',`metad`='$metad',`date_time`=current_timestamp() WHERE `r_id`='$srid'");
                 $ex= mysqli_query($con,$up);
                            
                              if($ex>0) {
                                 echo ("<script language='javascript'>
                                  window.alert('Updated Successfully') 
                                  window.location.href='rashifal.php';
                                  </script>");
                                  
                                  array_push($sucs, "Updated Successfuly."); }
                              else{ array_push($errors, "Sorry, there was an error."); }
                            
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
<?php include('errors.php'); ?>
                            <?php include('sucsess.php'); ?>
            <form id="SubmitForm" method="post" enctype="multipart/form-data"> 
     
            <div class="col-md-12 form-group group">
              <label class="control-label">Title:</label>
              <input class="form-control" type="text" name="title" value="<?php echo nm_h(isset($rs['title']) ? $rs['title'] : ''); ?>">
            </div>
                
            
                 
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Title:</label>
              <input class="form-control" type="text" name="metat" value="<?php echo nm_h(isset($rs['metat']) ? $rs['metat'] : ''); ?>">
            </div>
                
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Description:</label>
             <textarea class="form-control" cols="20" rows="5" name="metad"><?php echo nm_h(isset($rs['metad']) ? $rs['metad'] : ''); ?></textarea>
            </div>
            
            <div class="col-md-12 form-group group">
              <label class="control-label">Description</label>
              <textarea class="ckeditor form-control" id="description"  name="short_description"><?php echo nm_h(isset($rs['short_description']) ? $rs['short_description'] : ''); ?></textarea>
            </div>
                
            <div class="col-md-12 form-group group">
                <input type="text" name="update" id="actions" class="hidden" value="update" hidden>
                <button type="submit" name="update" class="btn btn-info">Update</button>
            </div>
           
        </form>
</div>
</div>			

    

</div>
<?php include"footer.php"; ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript">
<?php echo nm_ckeditor_js('description'); ?>
</script>
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