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

$usersession=$_SESSION['aemail'];

if($_POST["actions"] =="Add")  
{  
                            $name = mysqli_real_escape_string($con,$_POST['name']);
                            $num = mysqli_real_escape_string($con,$_POST['num']);
                            $email = mysqli_real_escape_string($con,$_POST['email']);
                            $address = mysqli_real_escape_string($con,$_POST['address']);
                            $cpwd = mysqli_real_escape_string($con,$_POST['cpwd']);
                            $dob=mysqli_real_escape_string($con,$_POST['dob']);
                            $img1 = basename($_FILES["image"]["name"]);
                            $imageFileType1 = pathinfo($img1,PATHINFO_EXTENSION);
    
                            $targetpath1="../users/profile/".$img1.pathinfo($imageFileType1, PATHINFO_EXTENSION);

                            // form validation: ensure that the form is correctly filled
                            if (empty($img1)) { array_push($errors, "Photo is required"); }
    
                            if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "gif" &&
                            $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "GIF") { array_push($errors, "Photo fileformate not supported"); }

                            if (empty($name)) { array_push($errors, "Kindly fill name"); }
                            if (empty($email)) { array_push($errors, "Kindly fill email"); }
                            if (empty($num)) { array_push($errors, "Kindly fill number"); }
                            if (empty($address)) { array_push($errors, "Kindly fill address"); }
                            if (empty($cpwd)) { array_push($errors, "Kindly fill password"); }
                        
                            
                        if (count($errors) == 0) {
                            
                            move_uploaded_file($_FILES["image"]["tmp_name"], $targetpath1);
                            
                           $qry="INSERT INTO `users`(`uname`, `uemail`, `unum`, `cpwd`, `image`, `dob`, `address`) VALUES ('$name','$email','$num','$cpwd','$img1','$dob','$address')";
                            $ex=mysqli_query($con,$qry);
                            
                            if ($ex>0) { array_push($sucs, "added Successfuly."); }
                            else{ array_push($errors, "Sorry, there was an error"); }
                    }
                        
    include('errors.php');
    include('sucsess.php');
}
if($_POST["actions"] =="Edit")  
{  
    $id = mysqli_real_escape_string($con,$_POST['id']);
    
    $qry="SELECT * FROM `users` WHERE `u_id`='$id'";
                            
    $ex=mysqli_query($con,$qry);
    
    $rs=mysqli_fetch_array($ex);
    
?>
            
            <div class="col-md-6 form-group group">
              <label class="control-label">Name:</label>
              <input class="form-control"  type="text" name="name" value="<?php echo $rs['name']; ?>" autocomplete="off">
            </div>
                
            <div class="col-md-6 form-group group">
              <label class="control-label">Number:</label>
              <input class="form-control"  type="text" name="num" value="<?php echo $rs['num']; ?>" autocomplete="off">
            </div>
                
            <div class="col-md-6 form-group group">
              <label class="control-label">Emails:</label>
              <input class="form-control"  type="text" name="email" value="<?php echo $rs['email']; ?>" autocomplete="off">
            </div>
                
            <div class="col-md-6 form-group group">
              <label class="control-label">Password:</label>
              <input class="form-control"  type="text" name="cpwd" value="<?php echo $rs['password']; ?>" autocomplete="off">
            </div>
            
                

                
            <div class="col-md-12 form-group group">
                <input type="text" name="image" value="<?php echo $usersession; ?>" class="hidden" hidden="true">
                <input type="text" name="id" value="<?php echo $id; ?>" class="hidden" hidden="true">
                <input type="text" name="actions" id="actions" class="hidden" value="Edit_User" hidden>
                <button type="submit" name="submit" class="btn btn-info">Edit User <i class="fas fa-edit"></i></button>
            </div>
<?php
}

if($_POST["actions"] =="Edit_User")  
{  
    $id = mysqli_real_escape_string($con,$_POST['id']);
    $name = mysqli_real_escape_string($con,$_POST['name']);
    $num = mysqli_real_escape_string($con,$_POST['num']);
    $email = mysqli_real_escape_string($con,$_POST['email']);
    $cpwd = mysqli_real_escape_string($con,$_POST['cpwd']);

    $date=date("Y-m-d");
    $month=date('M');
    $year=date('Y');
    
    $qry="UPDATE `users` SET `name`='$name',`email`='$email',`num`='$num',`password`='$cpwd' WHERE `u_id`='$id'";
    
    $ex=mysqli_query($con,$qry);
                            
    if ($ex>0) { array_push($sucs, "User Updated Successfuly."); }
    else{ array_push($errors, "Sorry, there was an error"); }
    
    include('errors.php');
    include('sucsess.php');
}
?>