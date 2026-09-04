<?php
require('config.php');

$sql = "SELECT 	id,	maincat,hindi_name,IFNULL(parent,'0') as parent ,cat_url,menu FROM categories ORDER BY `short` ASC ";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  $json = [];
 $row["id"]="00";
	 $row["maincat"]="Home";
	 $row["hindi_name"]="00";
	 $row["parent"]="p";
	 $row["cat_url"]="00";
	$row["menu"]="yes";
	 array_push($json, $row);
  while ($row = $result->fetch_assoc()) {
    array_push($json, $row);
  }
   
  echo json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} else {
  echo json_encode([]);
}
$mysqli->close();
