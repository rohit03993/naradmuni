<?php 

 include "config.php";
 
 if(!isset($_SESSION['aemail']))
 {
 echo ("<script language='javascript'>
        window.location.href='logout.php';
        </script>");
 }

$usersession=$_SESSION['aemail'];

$res=mysqli_query($con,"SELECT * FROM admin WHERE aemail='$usersession'");

$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);

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
<!--heder end here-->
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Profile</li>
</ol>	
<?php
if(isset($_POST['update'])) 
{
  $uname= mysqli_real_escape_string($con, $_POST['name']);
  $pwd= mysqli_real_escape_string($con, $_POST['pwd']);
  $email= mysqli_real_escape_string($con, $_POST['email']);
  $image1=$_FILES['image1']['name'];
 $i1=uniqid().$image1;
 $targetpath1="";
 $img1 = basename($_FILES["image1"]["name"]);
  if(!empty($img1))
      {
      
        $q=("SELECT `image` FROM `admin` WHERE `aemail`='$usersession'");

        $fd=mysqli_query($con,$q);

        $rs=mysqli_fetch_array($fd);

        unlink("profile/".$rs['image']);
      
       $img1= basename($_FILES["image1"]["name"]);
       $imageFileType1 = pathinfo($img1,PATHINFO_EXTENSION);
       if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "gif" &&
        $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "GIF")
       {
       echo "fileformate not supported";
        }
        else
        {
           $targetpath1="profile/".$img1.pathinfo($imageFileType1, PATHINFO_EXTENSION); 
             move_uploaded_file($_FILES["image1"]["tmp_name"], $targetpath1);
       }
    
    }
    else
    {
       $img1=$userRow['image'];
    }

$up=("update admin set apwd='$pwd',aname='$uname',aemail='$email',image='$img1' where aemail='$usersession'");
$ex= mysqli_query($con,$up);	
  if($ex>0)
  {
	 echo("<script language='javascript'>
  window.alert('Updated Successfully') 
  window.location.href='profile.php';
  </script>");
 }
  else
  {
	 echo "not updated";
  }
}
	

?>
    <div class="container">
          <form method="post" enctype="multipart/form-data">
              <div class="row">
                  <div class="col-md-6 form-group">
              <label class="control-label">User Name</label>
              <input class="form-control" name="name" type="text" value="<?php echo $userRow["aname"]; ?>" >
            </div>
            <div class="col-md-6 form-group">
              <label class="control-label">Password</label>
              <input class="form-control" name="pwd" type="text" value="<?php echo $userRow["apwd"]; ?>" >
            </div>
              
            <div class="col-md-6 form-group">
              <label class="control-label">Email</label>
              <input class="form-control" name="email" type="text" value="<?php echo $userRow["aemail"]; ?>" >
            </div>
                 
            <div class="col-md-3 form-group">
              <label class="control-label">Photo</label>
              <input class="form-control" name="image1" type="file">
            </div>
                 
            <div class="col-md-3 form-group">
              <label class="control-label">Old Photo</label>
              <img class="img-responsive" src="profile/<?php echo $userRow["image"]; ?>" width="70" height="50"/>
            </div>
          
            <div class="col-md-12 form-group">
              <button type="submit" name="update" class="btn btn-primary">Submit</button>
              <button type="reset" class="btn btn-default">Reset</button>
            </div>
              </div>
        </form>
    </div>
    
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="../include/js/bootstrap.min.js"></script>
  <!-- Font Awesome JS -->
    <script src="js/all.js"></script>

</body>
</html>