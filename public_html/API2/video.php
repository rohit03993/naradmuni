<?php
require('config.php');
    $page1 = $_GET['page'];
    $page = ($page1 - 1)*10;
    $num = 10;
$sql="SELECT `newsid`,`videoid`,`title`,`description`,`description`,`image`,`category` FROM `news` WHERE `newstype`='Video' AND `status`='Published' ORDER BY `newsid` DESC LIMIT " .$page.",".$num;
    $result = $mysqli->query($sql);
$json = [];	   
   if ($result->num_rows > 0) {
        // output data of each row
        
        while ($row = $result->fetch_assoc()) {
 
			array_push($json, $row);
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([]);
    }
   
    $mysqli->close();
?>
