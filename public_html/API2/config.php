<?php
$mysqli = new mysqli("localhost", "thenaradmunicom_db", "RIzx63ZTUNeqx", "thenaradmunicom_db");
mysqli_set_charset($mysqli,"utf8");
// Check connection
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}
 
//$mysqli->set_charset("utf8");
//header('Content-type: application/json');
//mysqli_set_charset( $mysqli, 'utf8');
header('Content-type: text/plain; charset=utf-8');
$now = date('y-m-d H:i');
?>
