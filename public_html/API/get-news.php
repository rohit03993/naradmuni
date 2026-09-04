<?php
require('config.php');

if (isset($_GET['news_id'])) {
    $news_id = $_GET['news_id'];
	$page1 = $_GET['page'];
    $page = ($page1 - 1)*10;
    $num = 10;
    
	if($news_id=="00"){
	$json = [];	
	$newsData="SELECT newsid, title, image, newsurl, short_description, newstype, date, time, metat, metad, metad  FROM news WHERE category=4 AND status='Published' AND pub_date_time < '$now' GROUP BY newsid order by newsid DESC LIMIT 1";
$result1 = $mysqli->query($newsData);
$row1 = $result1->fetch_assoc();
	  array_push($json, $row1);
        
$queryCondition =" WHERE p.newsid != '".$row1['newsid']."' AND p.status='Published' AND p.pub_date_time < '$now'";

$orderby = " GROUP BY p.newsid ORDER BY p.newsid DESC";
        
$sql = "SELECT p.newsid, p.title, p.image, p.newsurl, p.short_description, p.newstype, p.date, p.time, p.metat, p.metad, p.metad FROM news p
INNER JOIN news_cat pc
ON p.newsid = pc.news_id ". $queryCondition .$orderby;
 
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
	}else{
		
$queryCondition =" WHERE pc.category= '$news_id' AND p.status='Published' AND p.pub_date_time < '$now'";	
$orderby = " GROUP BY p.newsid ORDER BY p.newsid DESC";
 $sql = "SELECT p.newsid, p.title, p.image,p.videoid, p.newsurl, p.description, p.short_description, p.newstype, p.date, p.time, p.metat, p.metad, p.metad FROM news p
INNER JOIN news_cat pc
ON p.newsid = pc.news_id ". $queryCondition .$orderby;
	  $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        $json = [];
        while ($row = $result->fetch_assoc()) {
				$utf8string = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $row['description']), ENT_NOQUOTES, 'UTF-8');
            $row['description']=$utf8string;
            array_push($json, $row);
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([]);
    }
	}
   
    $mysqli->close();
} else {
    echo "News not found!";
}
