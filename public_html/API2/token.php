<?php
require('config.php');
if(isset($_POST['tokens'])){
    $tokens = $_POST['tokens'];
    $device_id = $_POST['device_id'];
    $dev = mysqli_query($mysqli,"SELECT `device_id` FROM `tokens` WHERE `device_id`='$device_id'");
    $json=[];
    if(mysqli_num_rows($dev)>0){
       $ex = mysqli_query($mysqli,"UPDATE `tokens` SET `token`='$tokens',`cdate`=current_timestamp() WHERE `device_id`='$device_id'");
    }else{
        $sql="INSERT INTO `tokens`(`token`, `type`, `device_id`) VALUES ('$tokens','App','$device_id')";
	    $ex = mysqli_query($mysqli,$sql);
    }
	   if($ex > 0){
        $json['status']="Success";
    } else {
       $json['status']="fail";
    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	
}
?>