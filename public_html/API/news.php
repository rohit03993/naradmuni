<?php
require('config.php');

if (isset($_GET['cat_id'])) {
    $page1 = $_GET['page'];
    $page = ($page1 - 1)*10;
    $num = 10;
    $catId = $_GET['cat_id'];
    $sql = "SELECT * FROM news WHERE `category` = '$catId' AND `status` = 'Published' AND pub_date_time < '$now' GROUP BY newsid order by newsid DESC";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        $json = [];
        while ($row = $result->fetch_assoc()) {
            array_push($json, $row);
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([]);
    }
    $mysqli->close();
} else {
    echo "News not found!";
}
