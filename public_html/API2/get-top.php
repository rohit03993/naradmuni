<?php
require('config.php');
    $page1 = $_GET['page'];
    $page = ($page1 - 1)*10;
    $num = 10;
		

	$json = [];	
	$newsData="SELECT newsid, title, image,description,videoid,newsurl, short_description, newstype, date, time, metat, metad, metad  FROM news WHERE category=4 AND status='Published' AND pub_date_time < '$now' GROUP BY newsid order by newsid DESC LIMIT 1";
$result1 = $mysqli->query($newsData);
$row1 = $result1->fetch_assoc();
	  array_push($json, $row1);
	$queryCondition =" WHERE  p.category != '4' AND pc.category != '4' AND p.status='Published' AND p.pub_date_time < '$now'";

$orderby = " GROUP BY p.newsid ORDER BY p.newsid DESC";
$sql = "SELECT p.newsid, p.title,p.videoid, p.image, p.newsurl, p.description,p.short_description, p.newstype, p.date, p.time, p.metat, p.metad, p.metad FROM news p
INNER JOIN news_cat pc
ON p.newsid = pc.news_id ". $queryCondition .$orderby." LIMIT " .$page.",".$num;
 
    $result = $mysqli->query($sql);
	
	

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

