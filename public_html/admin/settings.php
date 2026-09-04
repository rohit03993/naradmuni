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
            
if(isset($_POST['save']))
{
   $currentpass=$_POST['pwd'];
   $newpass=$_POST['newpassword'];
   $uname=$_SESSION['aemail'];
   //$e=mysql_query("select * from admin_login where uname='$uname'");
   //$row=mysql_fetch_array($e);

   if($userRow['apwd']==$currentpass)
   {
       
      $up=("update admin set apwd='$newpass' where aemail='$usersession'");
      $ex= mysqli_query($con,$up);
       
      //$w=mysql_query("UPDATE `admin_login` SET `pwd` = '$newpass' WHERE uname = '$uname'");
      if($ex)
      {
         echo("<script language='javascript'>
        window.alert(' Your password has been changed successfully.')
        </script>");
        
      }
   }
   else
   {
        echo("<script language='javascript'>
        window.alert('password not correct')
        </script>");
   }
}


?>
<div class="container">
        <form class="" method="post">
            
            <div class="form-group col-md-6">
              <label class="control-label">User Name</label>
              <input class="form-control" name="uname" type="text" placeholder="Firstname" value="<?php echo $_SESSION['aemail'] ?>" required="">
            </div>
            
            <div class="form-group col-md-6">
              <label class="control-label">Old password</label>
              <input class="form-control" name="pwd" type="password" placeholder="Enter Old password" required="">
            </div>
                 
            <div class="form-group col-md-6">
              <label class="control-label">Create New password</label>
              <input class="form-control" name="newpassword" type="password" placeholder="Create New password" required="">
            </div>
            <div class="form-group col-md-6">
              <button type="submit" name="save" class="btn btn-primary">Submit</button>
              <button type="reset" class="btn btn-default">Reset</button>
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
<script src="../include/js/jquery-ui.js"></script>
</body>
</html>