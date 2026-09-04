<?php  
 //actions.php  
include"config.php"; 
$date = date_default_timezone_set('Asia/Kolkata');

$output = '';



if($_POST["actions"] == "delete")  
      {  
        $id= $_POST["id"];
    
        $qry2="delete from users where `u_id`='$id'";

        $ex2=mysqli_query($con,$qry2);

        if($ex2>0)
        {

            echo 'Data Deleted.';
        } 
      }


?>