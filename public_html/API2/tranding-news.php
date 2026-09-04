<?php
require('config.php');
$sql = "SELECT p.newsid, p.title, p.image, p.newsurl, COUNT(pc.`count_id`) AS total_views FROM news p INNER JOIN news_views pc ON p.newsid = pc.newsid WHERE p.newstype!='Video' AND p.status='Published' AND p.date = DATE_FORMAT(now(), '%d-%m-%Y') GROUP BY p.`newsid` ORDER BY total_views DESC LIMIT 20";
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
