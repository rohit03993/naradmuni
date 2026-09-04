<?php
require('config.php');
$sql = "SELECT `name`,`code` FROM `version` ORDER BY `id` DESC LIMIT 1";
$result = $mysqli->query($sql);
$json = [];	   
   if ($result->num_rows > 0) {
        // output data of each row
        
        while ($row = $result->fetch_assoc()) {
 
			array_push($json, $row);
        }
        echo json_encode($json);
    } else {
        echo json_encode([]);
    }
   
    $mysqli->close();
?>