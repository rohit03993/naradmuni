<?php
require('config.php');

$sql="SELECT p.newsid, p.title, p.image, p.newsurl, p.short_description FROM news p  WHERE status='Published' GROUP BY newsid order by newsid DESC LIMIT 20";
$result = $mysqli->query($sql);
$json = [];	   
   if ($result->num_rows > 0) {
        // output data of each row
        
        while ($row = $result->fetch_assoc()) {
			
			$a = htmlentities($row['short_description']);

$row['short_description'] = html_entity_decode($a);
 //$row['short_description']=htmlspecialchars_decode($row['short_description']);
			array_push($json, $row);
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([]);
    }
   
    $mysqli->close();