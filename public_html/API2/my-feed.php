<?php
require('config.php');
date_default_timezone_set('Asia/Kolkata');

$qry="SELECT * FROM `rss_feed` ORDER BY `sequence` ASC";
$ex_rss=mysqli_query($mysqli,$qry);
 while($row=mysqli_fetch_array($ex_rss)){ 
        $url=$row['link'];	
        $newsoutput = new SimpleXMLElement($url, LIBXML_NOCDATA, true);
        $newsoutput = json_decode(json_encode($newsoutput), TRUE);
        $i =0;
        $json = [];
        foreach ($newsoutput['channel']['item'] as $item) {
            $pubDate = date('D, d M Y',strtotime($item['pubDate']));
            $now = date('D, d M Y');
            if($i>=2) break;
                $item["images"]=$row['image_name'];
                array_push($json,$item);
        $i++;}
         }
        echo json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
?>