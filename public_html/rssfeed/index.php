<?php

include_once '../admin/config.php';

//index.php

header("Content-type: application/xml");

$url = $_GET['url'];

$qry = mysqli_query($con,"SELECT * FROM `categories` WHERE `cat_url`='$url'");
$cat = mysqli_fetch_array($qry);


$query = "SELECT `newsurl`,`title`,`short_description`,`image`,`category`,`team_id` FROM `news` WHERE status='Published' AND newstype = 'Content' AND category  = '".$cat['id']."'  ORDER BY `newsid` DESC LIMIT 10";
$result = mysqli_query($con,$query);

$base_url = "https://thenaradmuni.com/";

echo "<?xml version='1.0' encoding='utf-8'?>" . PHP_EOL;
echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>".PHP_EOL;
echo "<channel>".PHP_EOL;
echo "<atom:link href='https://thenaradmuni.com/rss.xml' rel='self' type='application/rss+xml' />".PHP_EOL;
echo "<title>News Puran</title>".PHP_EOL;
echo "<link>".$base_url."index.php</link>".PHP_EOL;
echo "<description>Find all News, Hindi News, India News, News in Hindi, News Headlines, Breaking News, Daily News, Hindi News portal, Local News in thenaradmuni.com. Find India news in hindi in The Narad Muni, No.1 hindi news portal &amp; largest hindi daily.</description>".PHP_EOL;
echo "<language>hi</language>".PHP_EOL;

while($row = mysqli_fetch_array($result)){
    
$qry88 = mysqli_query($con,"SELECT hindi_name FROM `categories` WHERE `id`='".$row['category']."'");
$cat = mysqli_fetch_array($qry88);
	
$tqry = mysqli_query($con,"SELECT `name`,`email` FROM `team` WHERE `t_id`='".$row['team_id']."'");
$tm = mysqli_fetch_array($tqry);
 $date = $row["date"];
 $time = $row["time"];
 $publish_Date = date('D, d M Y H:i:s T', strtotime($date.' '.$time));
 $publish_Date = gmdate(DATE_RFC822, strtotime($publish_Date));
 $image_size_array = get_headers($base_url . "images/news/".$row["image"], 1);
 $image_size = $image_size_array["Content-Length"];
 $image_mime_array = getimagesize($base_url . "images/news/".$row["image"]);
 $image_mime = $image_mime_array["mime"];
 
 echo "<item>".PHP_EOL;
 echo "<title>".$row["title"]."</title>".PHP_EOL;
 echo "<link>".$base_url.$row["newsurl"]."</link>".PHP_EOL;
 echo "<guid>".$base_url.$row["newsurl"]."</guid>".PHP_EOL;
 echo "<pubDate>".$publish_Date."</pubDate>".PHP_EOL;
 echo "<description><![CDATA[".$row["short_description"]."]]></description>".PHP_EOL;
 echo "<enclosure url='".$urlroot."images/news/".$row["image"]."' length='".$image_size."' type='".$image_mime."' />".PHP_EOL;
 echo "<category>".$cat['hindi_name']."</category>".PHP_EOL;
 echo "<author>".$tm['name']."</author>".PHP_EOL;
 echo "</item>".PHP_EOL;
}

echo '</channel>'.PHP_EOL;
echo '</rss>'.PHP_EOL;

?>
