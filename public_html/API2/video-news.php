<?php
require('config.php');
    $page1 = $_GET['page'];
    $page = ($page1 - 1)*10;
    $num = 10;
$sql="SELECT p.newsid, p.title, p.image, p.videoid, p.newsurl, p.short_description, p.newstype, p.date, p.time, p.metat, p.metad, p.metad FROM news p INNER JOIN news_cat pc ON p.newsid = pc.news_id WHERE p.newstype='Video' AND p.videoid!='' AND p.videoid!='NULL' AND p.status='Published' AND p.pub_date_time < '$now' GROUP BY p.newsid ORDER BY p.newsid DESC LIMIT " .$page.",".$num;
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

