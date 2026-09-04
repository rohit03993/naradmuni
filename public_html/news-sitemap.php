<?php
//include"config.php";
include 'admin/config.php' ;

$base_url = "https://www.thenaradmuni.com/";

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;

echo '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="https://www.sitemaps.org/schemas/sitemap/0.9
      https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL; 

$query = "SELECT newsurl FROM `news` WHERE breaking='No' ORDER BY `newsid` ASC LIMIT 500";

$result = mysqli_query($con,$query);

while($row = mysqli_fetch_array($result))
{
  $link="news/";
    
    echo '<url>' .PHP_EOL;
    echo '<loc>'.$base_url.$link.$row["newsurl"] .'</loc>' . PHP_EOL;
    echo '<changefreq>always</changefreq>' . PHP_EOL;
    echo '</url>' .PHP_EOL;
}

echo '</urlset>' .PHP_EOL;

?>