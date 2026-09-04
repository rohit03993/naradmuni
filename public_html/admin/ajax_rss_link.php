<?php
include"config.php";
$newsid = $_POST['id'];
$qry = mysqli_query($con,"SELECT * FROM `rss_feed` WHERE `id`='$newsid'");
$row= mysqli_fetch_array($qry);
$ex1=mysqli_query($con,"DELETE FROM `rss_feed` WHERE `id`='$newsid'");
        if($ex1>0)
        {

            echo 'Data Deleted.';
        } 
?>