<?php
header('Content-Type: text/html; charset=utf-8');
include("admin/config.php");
error_reporting(E_ALL);
$postdata = array('key'=>'thenaradmuni@com','date'=>time());
$url = 'https://www.ujjwalpradesh.in/rest/v1/post.php?id='.time();
$ch = curl_init();	
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
//print_r($output);
//exit();
if($output) {
	$json = json_decode($output,true);
	if(count($json['data']) > 0) {
		foreach($json['data'] as $key=>$data) {
			$posts = mysqli_query($con, "SELECT upnewsid FROM upnewsid WHERE upnewsid='".$data['id']."'");
			if(mysqli_num_rows($posts) == 0) {
				$post_category = ss_get_post_category($data['code']);
				if(!empty($post_category)) {
					$upnewsid = mysqli_real_escape_string($con,$data['id']);				
					$newsurl = mysqli_real_escape_string($con,$data['slug']);
					$newsurl = get_slug($newsurl);
					$slugs = mysqli_query($con, "SELECT newsid FROM news WHERE newsurl='".$newsurl."'");
					if(mysqli_num_rows($slugs) > 0) {
						$newsurl = $newsurl."-2";
					}					
					$post_image = "";
					if($data['image']!='') {
						$img_dir = "images/news/";						
						$ext = pathinfo($data['image'], PATHINFO_EXTENSION);
						$rand = md5(uniqid().rand());
						$post_image = $rand.".".$ext;
						$img_data = @getimagesize($data['image']);
						if($img_data['mime']=='image/jpeg') {
							$img_saved = imagecreatefromjpeg($data['image']);				
							imagejpeg($img_saved,$img_dir.$post_image);
						} else if($img_data['mime']=='image/png') {
							$img_saved = imagecreatefrompng($data['image']);				
							imagepng($img_saved,$img_dir.$post_image);
						} else if($img_data['mime']=='image/gif') {
							$img_saved = imagecreatefromgif($data['image']);				
							imagegif($img_saved,$img_dir.$post_image);
						}
					}					
					$title = mysqli_real_escape_string($con,stripslashes($data['title']));
					$folder = $post_category.'/';
					$seolink = $post_category.'/'.$newsurl;
					$latest_news = "Yes";
					$description = mysqli_real_escape_string($con,$data['content']);
					$metat = $title;
					$metad = $title;
					$metak = $title;
					$hashtags = "";
					$category = $post_category;
					$top_side_bar = "";
					$slider = "No";
					if($data['type']=='featured') {
						$slider = "Yes";
					}
					$slider_priority = 0;
					$latest_priority = 0;
					$show_home = "";
					$short_description = get_excerpt($description,50);					
					$newstype = "Content";
					$img_source = "";
					$img_abt = "";
					$team_id = "2";					
					$date = date("d-m-Y",strtotime($data['date']));
					$time = date("H:i",strtotime($data['date']));
					$pub_date_time = date("y-m-d H:i",strtotime($data['date']));
					$status = 'Published';
					/* Update Feed ID */
					$qry_upnewsid = "INSERT INTO `upnewsid`(`upnewsid`) VALUES('$upnewsid')";
					mysqli_query($con, $qry_upnewsid); 
					/* Query */
					$qry = "INSERT INTO `news`(`newsurl`, `folder`, `seolink`, `metat`, `metad`, `metak`, `hashtags`, `title`, `short_description`, `description`, `show_home`, `image`, `img_abt`, `img_source`, `newstype`, `category`, `team_id`, `latest_news`, `top_side_bar`, `slider`, `latest_priority`, `slider_priority`, `date`, `time`, `status`, `pub_date_time`) VALUES ('$newsurl', '$folder', '$seolink', '$metat', '$metad', '$metak', '$hashtags', '$title', '$short_description', '$description', '$show_home', '$post_image', '$img_abt', '$img_source', '$newstype', '$category', '$team_id', '$latest_news', '$top_side_bar', '$slider', '$latest_priority', '$slider_priority', '$date', '$time', '$status', '$pub_date_time')";
					$insert = mysqli_query($con,$qry);
					print_r($insert);
					print_r(mysqli_error($con));
					echo "<hr>";
					$news_id = mysqli_insert_id($con);					
					if($insert > 0) {
						$qry_newscat = "INSERT INTO `news_cat`(`category`, `news_id`) VALUES('$category','$news_id')";
						mysqli_query($con, $qry_newscat); 
						if($category=='5' || $category=='6' || $category=='53' || $category=='42') {
							$qry_newscat = "INSERT INTO `news_cat`(`category`, `news_id`) VALUES('2','$news_id')";
							mysqli_query($con, $qry_newscat);
						}
						if($category=='7' || $category=='8') {
							$qry_newscat = "INSERT INTO `news_cat`(`category`, `news_id`) VALUES('3','$news_id')";
							mysqli_query($con, $qry_newscat);
						}
					}
				}
			}
		}
	}
}
function ss_get_post_category($category) {
	$result = "";
	$args = array('national'=>'9','international'=>'9','politics'=>'9','state'=>'1','mp'=>'2','bhopal'=>'5','indore'=>'6','jabalpur'=>'53','gwalior'=>'42','cg'=>'3','raipur'=>'7','bilaspur'=>'8','up'=>'1','bihar'=>'1','delhincr'=>'1','sports'=>'11','cricket'=>'16','tennis'=>'11','othersport'=>'11','business'=>'13','entertainment'=>'12','bollywood'=>'12','hollywood'=>'12','tv'=>'12','health'=>'30','socialmedia'=>'78','career'=>'38','science'=>'9');
	foreach ($args as $key=>$data) {
		if($category == $key) {
			$result = $data;
		}
	}	
	return $result;	
}
function get_excerpt($string, $limit) {
	$string = strip_tags($string); 
	$words = explode(" ", $string);
	$result = implode(" ", array_slice($words, 0, $limit));
	return $result;
}
function get_slug($title) {
	$replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");	
	$title = stripslashes($title);
	$linkStr_Replace = str_replace($replace,"-",trim($title));
	$linkStr_Replace = str_replace("----","-",$linkStr_Replace);
	$linkStr_Replace = str_replace("---","-",$linkStr_Replace);
	$linkStr_Replace = str_replace("--","-",$linkStr_Replace);
	$linkStr_Replace = preg_replace('/&.+?;/','',$linkStr_Replace);
	return $linkStr_Replace;
}