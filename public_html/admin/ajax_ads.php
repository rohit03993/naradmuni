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
                            $title = mysqli_real_escape_string($con,$_POST['title']);
                            $link = mysqli_real_escape_string($con,$_POST['link']);
                            $position = mysqli_real_escape_string($con,$_POST['position']);
                            $img1 = basename($_FILES["image"]["name"]);
                            $imageFileType1 = pathinfo($img1,PATHINFO_EXTENSION);
    
                            $targetpath1="../ads/".$img1.pathinfo($imageFileType1, PATHINFO_EXTENSION);

                            // form validation: ensure that the form is correctly filled
                            if (empty($img1)) { array_push($errors, "Photo is required"); }
    
                            if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "gif" &&
                            $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "GIF") { array_push($errors, "Photo fileformate not supported"); }

                            if (empty($position)) { array_push($errors, "Kindly fill Position"); }
                            if (empty($title)) { array_push($errors, "Kindly fill Title"); }
                            if (empty($link)) { array_push($errors, "Kindly fill URL"); }
                        
                            
                        if (count($errors) == 0) {
                            
                            move_uploaded_file($_FILES["image"]["tmp_name"], $targetpath1);
                            
                            $qry="INSERT INTO `ads`(`title`, `link`, `position`, `image`) VALUES ('$title','$link','$position','$img1')";
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
    
    $qry="SELECT * FROM `ads` WHERE `ad_id`='$id'";
                            
    $ex=mysqli_query($con,$qry);
    
    $rs=mysqli_fetch_array($ex);
    
?>
            <div class="col-md-12 form-group group">
              <label class="control-label">Title:</label>
              <input class="form-control"  type="text" name="title" value="<?php echo $rs['title']; ?>" autocomplete="off">
            </div>
                               
            <div class="col-md-6 form-group group group">
            <label class="control-label">Ad Position:</label>
            <select class="custom-select" id="position" name="position">
                <option value="<?php echo $rs['position']; ?>"><?php echo $rs["position"]; 
                                    if($rs["position"]=='1'){
                                        echo 'Left Side (265 x 353)';
                                    }elseif($rs["position"]=='2'){
                                        echo 'Middle Banner (815 x 120)';
                                    }else{
                                        echo 'Right Side (265 x 353)';
                                    }
                                
                                ?>
                </option>
                <option value="1">Left Side (265 x 353)</option>
                <option value="3">Right Side (265 x 353)</option>
            	<option value="2">Middle Banner (815 x 120)</option>
            </select>
            </div>
                
            <div class="col-md-6 form-group group">
              <label class="control-label">Image:</label>
              <input class="form-control"  type="file" name="image" autocomplete="off">
            </div>
            
            <div class="col-md-12 form-group group">
              <label class="control-label">Ad URL:</label>
              <input class="form-control"  type="text" name="link" value="<?php echo $rs['link']; ?>" autocomplete="off">
            </div>
                
           
             
                
            <div class="col-md-12 form-group group">
                
                <input type="text" name="id" value="<?php echo $id; ?>" class="hidden" hidden="true">
                <input type="text" name="actions" id="actions" class="hidden" value="Edit_Ads" hidden>
                <button type="submit" name="submit" class="btn btn-info">Edit Ads <i class="fas fa-edit"></i></button>
            </div>
<?php
}

if($_POST["actions"] =="Edit_Ads")  
{  
    $id = mysqli_real_escape_string($con,$_POST['id']);
    $title = mysqli_real_escape_string($con,$_POST['title']);
    $link = mysqli_real_escape_string($con,$_POST['link']);
    $position = mysqli_real_escape_string($con,$_POST['position']);
    $tmp_file = $_FILES['image']['tmp_name'];
    $ex1=mysqli_query($con,"SELECT `image` FROM `ads` WHERE `ad_id`='$id'");
    $rs1=mysqli_fetch_array($ex1);
  
    
    if (empty($tmp_file)) { $post_image=$rs1['image']; }else{
                            unlink("../ads/".$rs1['image']);
                            // Compress image
                            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                            $rand = md5(uniqid().rand());
                            $post_image = $rand.".".$ext;
                            echo  move_uploaded_file($tmp_file,"../ads/".$post_image);
                            
                           }
    
    $date=date("Y-m-d");
    $month=date('M');
    $year=date('Y');
    
   $qry="UPDATE `ads` SET `title`='$title',`link`='$link',`position`='$position',`image`='$post_image' WHERE `ad_id`='$id'";
    
    $ex=mysqli_query($con,$qry);
                            
    if ($ex>0) { array_push($sucs, "Updated Successfuly."); }
    else{ array_push($errors, "Sorry, there was an error"); }
    
    include('errors.php');
    include('sucsess.php');
}

if($_POST["actions"] =="delete")  
{  
    $id = mysqli_real_escape_string($con,$_POST['id']);
    $ex1=mysqli_query($con,"SELECT `image` FROM `ads` WHERE `ad_id`='$id'");
    $rs1=mysqli_fetch_array($ex1);
    
    unlink("../ads/".$rs1['image']);
   
   $qry2="delete from ads where `ad_id`='$id'";

        $ex2=mysqli_query($con,$qry2);

        if($ex2>0)
        {

            echo 'Data Deleted.';
        }
}
?>