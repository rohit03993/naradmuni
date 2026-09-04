<?php
require('config.php');

if (isset($_GET['news_id'])) {
    $page1 = $_GET['page'];
    $page = ($page1 - 1)*10;
    $num = 10;
    $news_id = $_GET['news_id'];
    $sql = "SELECT news.*,team.name  FROM news inner join team on news.team_id=team.t_id WHERE news.`newsid` = '$news_id' LIMIT " .$page.",".$num ;
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
