<?php
require('config.php');
$page1 = $_GET['page'];
$page = ($page1 - 1)*1;
$num = 1;
$sql="SELECT p.newsid, p.title, p.description,p.videoid, p.image, p.videoid, p.newsurl, p.short_description, p.newstype, p.date, p.time, p.metat, p.metad, p.metad FROM news p  WHERE category='4' AND status='Published' AND pub_date_time < '$now' GROUP BY newsid order by newsid DESC";
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

