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
      
echo '<url>
  <loc>https://www.thenaradmuni.com/</loc>
  <changefreq>always</changefreq>
</url>
<url>
<loc>https://www.thenaradmuni.com/team.php</loc>
<changefreq>always</changefreq>
</url>
<url>
<loc>https://www.thenaradmuni.com/about.php</loc>
<changefreq>always</changefreq>
</url>' . PHP_EOL;  

$result11 = mysqli_query($con,"SELECT `page_url` FROM `pages`");
while($row11 = mysqli_fetch_array($result11))
{
    echo '<url>' .PHP_EOL;
    echo '<loc>'.$base_url.'page/'.$row11["page_url"] .'</loc>' . PHP_EOL;
    echo '<changefreq>always</changefreq>' . PHP_EOL;
    echo '</url>' .PHP_EOL;
}

$query1 = "SELECT cat_url FROM `categories` WHERE `cat_url` IS NOT NULL ORDER BY `short` ASC";

$result1 = mysqli_query($con,$query1);

while($row1 = mysqli_fetch_array($result1))
{
    echo '<url>' .PHP_EOL;
    echo '<loc>'.$base_url.'category/'.$row1["cat_url"] .'</loc>' . PHP_EOL;
    echo '<changefreq>always</changefreq>' . PHP_EOL;
    echo '</url>' .PHP_EOL;
}


echo '</urlset>' .PHP_EOL;

?>