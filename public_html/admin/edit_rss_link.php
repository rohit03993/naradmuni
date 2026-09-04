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

$srid=$_GET['eid'];
$qry="SELECT * FROM `rss_feed` WHERE id='$srid'";
$ex=mysqli_query($con,$qry);
$rs=mysqli_fetch_array($ex);

if(isset($_POST['update']))
                    {     
                            $title = mysqli_real_escape_string($con,$_POST['title']);
                            $link=mysqli_real_escape_string($con,$_POST['link']);
                            $sequence=mysqli_real_escape_string($con,$_POST['sequence']);
                            $small_description=mysqli_real_escape_string($con,$_POST['small_description']);
                              $tmp_file = $_FILES['image']['tmp_name'];
                        
						if (empty($tmp_file)) { $post_image=$rs['image_name']; }else{
    
                           
                            // Compress image
                            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                            $rand = md5(uniqid().rand());
                            $post_image = $rand.".".$ext;
                            
                            // Get Image Dimension
                            $fileinfo = @getimagesize($_FILES["image"]["tmp_name"]);
                            $width = $fileinfo[0];
                            $height = $fileinfo[1];
                            
                             move_uploaded_file($tmp_file,"../images/gallery/".$post_image);
                            
                           }
                             /*   Linkname starts  */
                            $replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
                    		$linkStr_Replace = str_replace($replace,"-",trim($_POST['url']));
                    		$linkStr_Replace = str_replace("----","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("---","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("--","-",$linkStr_Replace);
                    		$linkname =  $linkStr_Replace;
						
                            if (empty($title)) { array_push($errors, "Kindly fill Video title"); }
                           
                            if (empty($link)) { array_push($errors, "Kindly fill Link"); }
                         
                            // if (empty($sequence)) { array_push($errors, "Kindly fill Sequence"); }
                           
                             if (empty($small_description)) { array_push($errors, "Kindly fill Description"); }
                             if (empty($linkname)) { array_push($errors, "Kindly fill Url"); }
                           
                             
                        if (count($errors) == 0) {
                  
                $up=("UPDATE `rss_feed` SET `link`='$link',`sequence`='$sequence',`small_description`='$small_description',`url`='$linkname',`image_name`='$post_image',`title`='$title' WHERE id='$srid'");
                            
                            $ex= mysqli_query($con,$up);
                            
                              if($ex>0) {
                            
                            
                                 echo ("<script language='javascript'>
                                  window.alert('Updated Successfully') 
                                  window.location.href='rss_link.php';
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
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Edit Rss Link</li>
            </ol>
<div class="container-fluid page-content">
<?php include('errors.php'); ?>
                            <?php include('sucsess.php'); ?>
            <form id="SubmitForm" method="post" enctype="multipart/form-data"> 
                
               
            <div class="col-md-6 form-group group">
              <label class="control-label">Title:</label>
              <input class="form-control" type="text" name="title" value="<?php echo $rs['title']; ?>">
            </div>
              
			 <div class="col-md-6 form-group group">
              <label class="control-label">Sequence:</label>
              <input class="form-control" type="text" name="sequence" value="<?php echo $rs['sequence']; ?>">
            </div>
            <div class="col-md-6 form-group group">
              <label class="control-label">URL:</label>
              <input class="form-control" type="text" name="url" value="<?php echo $rs['url']; ?>">
            </div>
			<div class="col-md-6 form-group group">
              <label class="control-label">Image (400X400 Pixels):</label>
                <input class="form-control" type="file" name="image" >
            </div>
               <div class="col-md-12 form-group group">
              <label class="control-label">Link URL:</label>
              <input class="form-control" type="text" name="link" value="<?php echo $rs['link']; ?>">
            </div>
              <div class="col-md-12 form-group group">
              <label class="control-label">Small Description:</label>
              <input class="form-control" type="text" name="small_description" value="<?php echo $rs['small_description']; ?>">
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