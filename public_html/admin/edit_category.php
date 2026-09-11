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

$srid=$_GET['eid'];
$qry="SELECT * FROM `categories` WHERE id='$srid'";
$ex=mysqli_query($con,$qry);
$rs=mysqli_fetch_array($ex);

if(isset($_POST['update']))
                    {     
                            $category = mysqli_real_escape_string($con,$_POST['category']);
                            $cat_url=mysqli_real_escape_string($con,$_POST['cat_url']);
                            $short=mysqli_real_escape_string($con,$_POST['short']);
                            $menu=mysqli_real_escape_string($con,$_POST['menu']);
                            $metat = mysqli_real_escape_string($con,$_POST['metat']);
                            $metad = mysqli_real_escape_string($con,$_POST['metad']);
                            $hindi_name = mysqli_real_escape_string($con,$_POST['hindi_name']);
                           /*   Linkname starts  */
                            $replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
                    		$linkStr_Replace = str_replace($replace,"-",trim($cat_url));
                    		$linkStr_Replace = str_replace("----","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("---","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("--","-",$linkStr_Replace);
                    		$linkname =  $linkStr_Replace;
                            $parent = mysqli_real_escape_string($con,$_POST['parent']);
                            $latter = mysqli_real_escape_string($con,$_POST['latter']);
                            
                             if($parent==0){
                                
                                $up="UPDATE `categories` SET `short`='$short',`menu`='$menu',`maincat`='$category',`cat_url`='$linkname',`metat`='$metat',`metad`='$metad',`hindi_name`='$hindi_name' WHERE `id`='$srid'";
                                
                            }else{
                                 
                               $up="UPDATE `categories` SET `short`='$short',`menu`='$menu',`maincat`='$category',`parent`='$parent',`cat_url`='$linkname',`metat`='$metat',`metad`='$metad',`hindi_name`='$hindi_name', `latter`='$latter' WHERE `id`='$srid'";
                                
                            }
                            
                            $ex= mysqli_query($con,$up);
                            
                              if($ex>0) {
                                  
                                   echo("<script language='javascript'>
                                  window.alert('added Successfully') 
                                  window.location.href='categories.php';
                                  </script>");
                                  
                                  array_push($sucs, "Updated Successfuly."); }
                              else{ array_push($errors, "Sorry, there was an error."); }
                            
                             
                        
                                
 
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

            <form id="SubmitForm" method="post" enctype="multipart/form-data"> 
            
                <div class="col-md-4 form-group group">
              <label class="control-label">Parent Category:</label>
        
               <select  class="custom-select" id="maincat" name="parent">
                <option><?php echo $rs["parent"]; ?></option>
            	<?php
                
                $query = nm_categories_result($con);
                if ($query instanceof mysqli_result && $query->num_rows > 0) {
                    while($row = $query->fetch_assoc()){
                        echo '<option value="'.(int)$row['id'].'">'.nm_h(nm_cat_label($row)).'</option>';
                    }
                }else{
                    echo '<option value="0">no data available</option>';
                }
                
                ?>
            </select>
            </div>
                
            <div class="col-md-4 form-group group">
              <label class="control-label">Sort Order:</label>
              <input class="form-control" type="text" name="short" value="<?php echo $rs["short"]; ?>">
            </div>
                
            <div class="col-md-4 form-group group group">
            <label class="control-label">Show In Menu</label>
            <select class="custom-select" id="breaking" name="menu">
                <option><?php echo $rs["menu"]; ?></option>
                <option>No</option>
            	<option>Yes</option>
            </select>
            </div>
                
            <div class="col-md-3 form-group group">
              <label class="control-label">English Name:</label>
              <input  type="text" class="form-control" name="category" value="<?php echo nm_h(isset($rs["maincat"]) ? $rs["maincat"] : ''); ?>">
            </div>
                
            <div class="col-md-3 form-group group">
              <label class="control-label">Hindi Name:</label>
              <input  type="text" class="form-control" name="hindi_name" value="<?php echo nm_h(isset($rs["hindi_name"]) ? $rs["hindi_name"] : ''); ?>">
            </div>
                
            <div class="col-md-2 form-group group">
              <label class="control-label">Starting Alphabate:</label>
              <input  type="text" class="form-control" name="latter" value="<?php echo $rs["latter"]; ?>">
            </div>
                
            <div class="col-md-4 form-group group">
              <label class="control-label">Category URL:</label>
              <input  type="text" class="form-control" name="cat_url" value="<?php echo $rs["cat_url"]; ?>">
            </div>
                
            
                 
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Title:</label>
              <input class="form-control" type="text" name="metat" value="<?php echo $rs["metat"]; ?>">
            </div>
                
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Description:</label>
             <textarea class="form-control" cols="20" rows="5" name="metad"><?php echo $rs["metad"]; ?></textarea>
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