<?php
require('config.php');
if(isset($_POST['newsid'])){
    $newsid = $_POST['newsid'];
    $json=[];
	$sql="INSERT INTO `news_views`(`newsid`) VALUES ('$newsid')";
	$ex = mysqli_query($mysqli,$sql);
	   if($ex > 0){
        $json['status']="Success";
    } else {
       $json['status']="fail";
    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	
}
?>