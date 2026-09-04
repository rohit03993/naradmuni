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
                            $post_name = mysqli_real_escape_string($con,$_POST['post_name']);
                            $location = mysqli_real_escape_string($con,$_POST['location']);
                            $description = mysqli_real_escape_string($con,$_POST['description']);
                            $date = date('d-m-Y');
                            $status = 'Active';
                         
                            if (empty($post_name)) { array_push($errors, "Kindly fill post name"); }
                            if (empty($location)) { array_push($errors, "Kindly fill job location"); }
                            if (empty($description)) { array_push($errors, "Kindly fill job desciption"); }
                        
                            
                        if (count($errors) == 0) {
                            
                            
                            $qry="INSERT INTO `jobs`(`post_name`, `location`, `description`, `date`, `status`) VALUES ('$post_name','$location','$description','$date','$status')";
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
    
    $qry="SELECT * FROM `jobs` WHERE `j_id`='$id'";
                            
    $ex=mysqli_query($con,$qry);
    
    $rs=mysqli_fetch_array($ex);
    
?>       
            <div class="col-md-4 form-group group">
              <label class="control-label">Post Name:</label>
              <input class="form-control"  type="text" name="post_name" autocomplete="off" value="<?php echo $rs['post_name']; ?>">
            </div>
                
            <div class="col-md-4 form-group group">
              <label class="control-label">Job Location:</label>
              <input class="form-control"  type="text" name="location" autocomplete="off" value="<?php echo $rs['location']; ?>">
            </div>
            
            <div class="col-md-4 form-group group">
              <label class="control-label">Status:</label>
              <select class="custom-select" name="status" id="status">
                <option><?php echo $rs['status']; ?></option>
                 <option>Active</option>
                 <option>Deactive</option>
              </select>
            </div>
                
            <div class="col-md-12 form-group group">
              <label class="control-label">Job Description:</label>
         <textarea class="form-control ckeditor" name="description" ><?php echo $rs['description']; ?></textarea>
            </div>
                
            <div class="col-md-12 form-group group">
                <input type="text" name="image" value="<?php echo $usersession; ?>" class="hidden" hidden="true">
                <input type="text" name="id" value="<?php echo $id; ?>" class="hidden" hidden="true">
                <input type="text" name="actions" id="actions" class="hidden" value="Edit_User" hidden>
                <button type="submit" name="submit" class="btn btn-info">Edit Job <i class="fas fa-edit"></i></button>
            </div>
<?php
}

if($_POST["actions"] =="Edit_User")  
{  
    $id = mysqli_real_escape_string($con,$_POST['id']);
    $post_name = mysqli_real_escape_string($con,$_POST['post_name']);
    $location = mysqli_real_escape_string($con,$_POST['location']);
    $description = mysqli_real_escape_string($con,$_POST['description']);
    $status = mysqli_real_escape_string($con,$_POST['status']);

    $qry="UPDATE `jobs` SET `post_name`='$post_name',`location`='$location',`description`='$description',`status`='$status' WHERE `j_id`='$id'";
    
    $ex=mysqli_query($con,$qry);
                            
    if ($ex>0) { array_push($sucs, "Updated Successfuly."); }
    else{ array_push($errors, "Sorry, there was an error"); }
    
    include('errors.php');
    include('sucsess.php');
}

if($_POST["actions"] =="delete")  
{  
    $id = mysqli_real_escape_string($con,$_POST['id']);
    $qry2="delete from jobs where `j_id`='$id'";

        $ex2=mysqli_query($con,$qry2);

        if($ex2>0)
        {

            echo 'Data Deleted.';
        }
}
?>