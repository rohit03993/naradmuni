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
                            $designation = mysqli_real_escape_string($con,$_POST['designation']);
                            $email = mysqli_real_escape_string($con,$_POST['email']);
                            $short = mysqli_real_escape_string($con,$_POST['short']);
                            $fb_link = mysqli_real_escape_string($con,$_POST['fb_link']);
                            $tw_link = mysqli_real_escape_string($con,$_POST['tw_link']);
                            $img1 = basename($_FILES["image"]["name"]);
                            $imageFileType1 = pathinfo($img1,PATHINFO_EXTENSION);
    
                            $targetpath1="../team/".$img1.pathinfo($imageFileType1, PATHINFO_EXTENSION);

                            // form validation: ensure that the form is correctly filled
                            if (empty($img1)) { array_push($errors, "Photo is required"); }
    
                            if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "gif" &&
                            $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "GIF") { array_push($errors, "Photo fileformate not supported"); }

                            if (empty($short)) { array_push($errors, "Kindly fill short order"); }
                            if (empty($name)) { array_push($errors, "Kindly fill name"); }
                            //if (empty($email)) { array_push($errors, "Kindly fill email"); }
                            if (empty($designation)) { array_push($errors, "Kindly fill designation"); }
                        
                            
                        if (count($errors) == 0) {
                            
                            move_uploaded_file($_FILES["image"]["tmp_name"], $targetpath1);
                            
                            $qry="INSERT INTO `team`(`name`, `email`, `designation`, `image`, `short`, `fb_link`, `tw_link`) VALUES ('$name','$email','$designation','$img1','$short','$fb_link','$tw_link')";
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
    
    $qry="SELECT * FROM `team` WHERE `t_id`='$id'";
                            
    $ex=mysqli_query($con,$qry);
    
    $rs=mysqli_fetch_array($ex);
    
?>
            <div class="col-md-2 form-group group">
              <label class="control-label">Short Order:</label>
              <input class="form-control"  type="text" name="short" value="<?php echo $rs['short']; ?>" autocomplete="off">
            </div>
            
            <div class="col-md-10 form-group group">
              <label class="control-label">Name:</label>
              <input class="form-control"  type="text" name="name" value="<?php echo $rs['name']; ?>" autocomplete="off">
            </div>
                
            <div class="col-md-6 form-group group">
              <label class="control-label">Designation:</label>
              <input class="form-control"  type="text" name="designation" value="<?php echo $rs['designation']; ?>" autocomplete="off">
            </div>
            
           <div class="col-md-6 form-group group">
              <label class="control-label">Image:</label>
              <input class="form-control"  type="file" name="image" autocomplete="off">
            </div>

             <div class="col-md-6 form-group group">
              <label class="control-label">FB Link:</label>
              <input class="form-control"  type="text" name="fb_link" value="<?php echo $rs['fb_link']; ?>" autocomplete="off">
            </div>

             <div class="col-md-6 form-group group">
              <label class="control-label">Twitter Link:</label>
              <input class="form-control"  type="text" name="tw_link" value="<?php echo $rs['tw_link']; ?>" autocomplete="off">
            </div>
                
            <div class="col-md-12 form-group group">
              <label class="control-label">About:</label>
              <textarea class="form-control"  type="text" name="email"><?php echo $rs['email']; ?></textarea>
            </div>
                
            <div class="col-md-12 form-group group">
                
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
    $designation = mysqli_real_escape_string($con,$_POST['designation']);
    $email = mysqli_real_escape_string($con,$_POST['email']);
    $short = mysqli_real_escape_string($con,$_POST['short']);
    $fb_link = mysqli_real_escape_string($con,$_POST['fb_link']);
    $tw_link = mysqli_real_escape_string($con,$_POST['tw_link']);
    $tmp_file = $_FILES['image']['tmp_name'];
    $ex1=mysqli_query($con,"SELECT `image` FROM `team` WHERE `t_id`='$id'");
    $rs1=mysqli_fetch_array($ex1);
  
    
    if (empty($tmp_file)) { $post_image=$rs1['image']; }else{
                            unlink("../team/".$rs1['image']);
                            // Compress image
                            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                            $rand = md5(uniqid().rand());
                            $post_image = $rand.".".$ext;
                            echo  move_uploaded_file($tmp_file,"../team/".$post_image);
                            
                           }
    
    $date=date("Y-m-d");
    $month=date('M');
    $year=date('Y');
    
   $qry="UPDATE `team` SET `name`='$name',`email`='$email',`designation`='$designation',`image`='$post_image',`short`='$short',`fb_link`='$fb_link',`tw_link`='$tw_link' WHERE `t_id`='$id'";
    
    $ex=mysqli_query($con,$qry);
                            
    if ($ex>0) { array_push($sucs, "User Updated Successfuly."); }
    else{ array_push($errors, "Sorry, there was an error"); }
    
    include('errors.php');
    include('sucsess.php');
}

if($_POST["actions"] =="delete")  
{  
    $id = mysqli_real_escape_string($con,$_POST['id']);
    $ex1=mysqli_query($con,"SELECT `image` FROM `team` WHERE `t_id`='$id'");
    $rs1=mysqli_fetch_array($ex1);
    
    unlink("../team/".$rs1['image']);
   
   $qry2="delete from team where `t_id`='$id'";

        $ex2=mysqli_query($con,$qry2);

        if($ex2>0)
        {

            echo 'Data Deleted.';
        }
}
?>