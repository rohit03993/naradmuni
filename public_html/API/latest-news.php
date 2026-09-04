<?php
require('config.php');
$page1 = $_GET['page'];
$page = ($page1 - 1)*10;
$num = 10;
$sql="SELECT newsid, description,videoid,title, image, newsurl, short_description, newstype, latest_news, date, time, metat, metad, metad FROM news WHERE newstype != 'Video' AND status='Published' AND pub_date_time < '$now' GROUP BY newsid ORDER BY newsid DESC";
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

