<?php

include"config.php";

	if (!isset($_SESSION['aimage'])) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../manage.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['aimage']);
		header("location: ../manage.php");
	}

 if(!isset($_SESSION['aimage']))
 {
  echo ("<script language='javascript'>
                   window.location.href='logout.php';
                        </script>");
 }

$usersession=$_SESSION['aimage'];

if($_POST["actions"] =="Add")  
{  
                            $title = mysqli_real_escape_string($con,$_POST['title']);
                            $description = mysqli_real_escape_string($con,$_POST['description']);
                            $image = mysqli_real_escape_string($con,$_POST['image']);
                            $link = mysqli_real_escape_string($con,$_POST['link']);
                            $date=date("Y-m-d");
                            $time=date('h:i:s:A');
                            if (empty($title)) { array_push($errors, "Kindly fill title"); }
                            if (empty($description)) { array_push($errors, "Kindly fill description"); }
                        
                            
                        if (count($errors) == 0) {
                            
    define('SERVER_API_KEY', 'AAAAnf23Pf0:APA91bHnaKtqFnWUnu2K0CITgNWFXxWUuT3OVswnRgSJ17XwcGu9TVNClf74cC-APFD2JDw1KjVx7Fbxrqcb62cNv1TLg9nRQqnYkOCz_his7BJs0FSvVUPaRk0ql-xF4HI_o1kRxdZr');
	require '../DbConnect.php';
	$db = new DbConnect;
	$conn = $db->connect();
	$stmt = $conn->prepare("SELECT * FROM `tokens`");
	$stmt->execute();
	$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($tokens as $token) {
		$registrationIds[] = $token['token'];
	}
	
	$header = [
		'Authorization: Key=' . SERVER_API_KEY,
		'Content-Type: Application/json'
	];

	$msg = [
	    
		'title' => $title,
		'body' => $description,
		'icon' => 'https://www.thenaradmuni.com/images/icon/AppIcon4x.png',
		'image' => $image,
		'click_action'=> $link,
	];

	$payload = [
		'registration_ids' 	=> $registrationIds,
		'data'				=> $msg
	];

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => json_encode( $payload ),
	  CURLOPT_HTTPHEADER => $header
	));
                            
    $qry="INSERT INTO `notification`(`title`, `description`, `image`, `link`, `date`, `time`) VALUES ('$title','$description','$image','$link','$date','$time')";
    $ex=mysqli_query($con,$qry);
                            
    if ($ex>0) { array_push($sucs, "Notification Sent."); }
    else{ array_push($errors, "Sorry, there was an error"); }
    
    }
                        
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