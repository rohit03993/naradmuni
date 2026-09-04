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
                            $name = $_FILES['video']['name'];
                            
                           
                            if (empty($name)) { array_push($errors, "Kindly Choose video"); }
                            if (empty($title)) { array_push($errors, "Kindly fill title"); }
                        
                            
                        if (count($errors) == 0) {
                            
                            $name = $_FILES['video_file']['name'];
                            $target_dir = "../videos/";
                            $target_file = $target_dir . $_FILES["video_file"]["name"];
                            move_uploaded_file($_FILES['video_file']['tmp_name'],$target_file);
                            
                            $qry="INSERT INTO `video`(`title`, `video`) VALUES ('$title','$name')";
                            $ex=mysqli_query($con,$qry);
                            
                            if ($ex>0) { array_push($sucs, "added Successfuly."); }
                            else{ array_push($errors, "Sorry, there was an error"); }
                    }
                        
    include('errors.php');
    include('sucsess.php');
}

if($_POST["actions"] =="delete")  
{  
    $id = mysqli_real_escape_string($con,$_POST['id']);
    $ex1=mysqli_query($con,"SELECT `video` FROM `video` WHERE `v_id`='$id'");
    $rs1=mysqli_fetch_array($ex1);
    
    unlink("../videos/".$rs1['video']);
   
   $qry2="delete from video where `v_id`='$id'";

        $ex2=mysqli_query($con,$qry2);

        if($ex2>0)
        {

            echo 'Data Deleted.';
        }
}
?>