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

if($_POST["actions"] == "delete")  
      {  
        $id= $_POST["id"];
        $qry2="delete from categories where `id`='$id'";

        $ex2=mysqli_query($con,$qry2);

        if($ex2>0)
        {

            echo 'Data Deleted.';
        } 
      }
?>