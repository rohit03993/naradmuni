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

 if(!isset($_SESSION['aemail']))
 {
  echo ("<script language='javascript'>
                   window.location.href='logout.php';
                        </script>");
 }

$productsession=$_SESSION['aemail'];

$res=mysqli_query($con,"SELECT * FROM admin WHERE aemail='$productsession'");

$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);

    $id = isset($_GET['eid']) ? $_GET['eid'] : (isset($_GET['id']) ? $_GET['id'] : '');
    
    $qry="SELECT * FROM `jobs` WHERE `j_id`='".mysqli_real_escape_string($con, (string)$id)."'";
                            
    $ex=mysqli_query($con,$qry);
    
    $rs=mysqli_fetch_array($ex);
    if (!is_array($rs)) { $rs = array(); }

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
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Edit Job</li>
            </ol>
<div class="container-fluid page-content">
<?php include('errors.php'); ?>
            <div class="EditstatusMsg"></div>
            <form id="EditForm" method="post" enctype="multipart/form-data"> 
                
            <div class="col-md-4 form-group group">
              <label class="control-label">Post Name:</label>
              <input class="form-control"  type="text" name="post_name" autocomplete="off" value="<?php echo nm_h(isset($rs['post_name']) ? $rs['post_name'] : ''); ?>">
            </div>
                
            <div class="col-md-4 form-group group">
              <label class="control-label">Job Location:</label>
              <input class="form-control"  type="text" name="location" autocomplete="off" value="<?php echo nm_h(isset($rs['location']) ? $rs['location'] : ''); ?>">
            </div>
            
            <div class="col-md-4 form-group group">
              <label class="control-label">Status:</label>
              <select class="custom-select" name="status" id="status">
                <option><?php echo nm_h(isset($rs['status']) ? $rs['status'] : ''); ?></option>
                 <option>Active</option>
                 <option>Deactive</option>
              </select>
            </div>
                
            <div class="col-md-12 form-group group">
              <label class="control-label">Job Description:</label>
         <textarea class="form-control ckeditor" id="description" name="description"><?php echo nm_h(isset($rs['description']) ? $rs['description'] : ''); ?></textarea>
            </div>
                
            <div class="col-md-12 form-group group">
                <input type="text" name="image" value="<?php echo nm_h(isset($usersession) ? $usersession : ''); ?>" class="hidden" hidden="true">
                <input type="text" name="id" value="<?php echo nm_h($id); ?>" class="hidden" hidden="true">
                <input type="text" name="actions" id="actions" class="hidden" value="Edit_User" hidden>
                <button type="submit" name="submit" class="btn btn-info">Edit Job <i class="fas fa-edit"></i></button>
            </div>
           
        </form>
</div>
</div>			
<script>
$(document).ready(function(e){
    $("#EditForm").on('submit', function(e){
        e.preventDefault();
        for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
        }
        $.ajax({
            type: 'POST',
            url: 'ajax_jobs.php',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){$("#overlay").show();$('.EditstatusMsg').delay(200).fadeIn();},
            success:function(data)  
                {
                    
                     $('.EditstatusMsg').html(data);
                     $('.EditstatusMsg').delay(1000).fadeOut();
                     $('#EditForm')[0].reset();
                     $('#edit').modal('hide');
                     setInterval(function() {$("#overlay").hide(); },500);
                     window.location.href='edit_jobs.php?eid=<?php echo $id; ?>';
                }
        });
    });
    });
    
</script>
    

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