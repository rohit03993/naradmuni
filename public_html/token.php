<?php 
include"admin/config.php";
	if(isset($_POST['token'])) {
	    $sql_u = "SELECT `token` FROM `tokens` WHERE `token`='".$_POST['token']."'";
        $res_u = mysqli_query($con, $sql_u);
        if (mysqli_num_rows($res_u) > 0) { echo "Sorry... Token already saved."; 
            
        }else{
            $stmt = mysqli_query($con,"INSERT INTO `tokens`(`token`, `type`) VALUES ('".$_POST['token']."','Web')");
    		if($stmt>0) {
    			echo 'Token Saved..';
    		} else {
    			echo 'Failed to saved token..';
    		}
		
        }
	}

 ?>