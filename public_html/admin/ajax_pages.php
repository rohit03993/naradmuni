<?php
include"config.php";
$newsid = $_POST['id'];
$ex1=mysqli_query($con,"DELETE FROM `pages` WHERE `p_id`='$newsid'");
        if($ex1>0)
        {

            echo 'Data Deleted.';
        } 
?>