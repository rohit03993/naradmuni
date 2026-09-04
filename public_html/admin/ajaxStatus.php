<?php
include"config.php";
 
	if (!isset($_SESSION['aemail'])) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../manage.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['aemail']);
		header("location: ../manage.php");
	}

 if(!isset($_SESSION['aemail']))
 {
  echo ("<script language='javascript'>
                   window.location.href='logout.php';
                        </script>");
 }

if(isset($_POST['c_id'])){
    
$c_id = mysqli_real_escape_string($con,$_POST['c_id']);
$status = mysqli_real_escape_string($con,$_POST['status']);
$status_date = date("Y-m-d");
$ex=mysqli_query($con,"UPDATE `comments` SET `status`='$status' WHERE `c_id`='$c_id'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}

if(isset($_POST['conatct_id'])){
    
$c_id = mysqli_real_escape_string($con,$_POST['conatct_id']);
$status = mysqli_real_escape_string($con,$_POST['status']);
$status_date = date("Y-m-d");
$ex=mysqli_query($con,"UPDATE `conatct` SET `status`='$status' WHERE `c_id`='$c_id'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}

if(isset($_POST['i_id'])){
    
$c_id = mysqli_real_escape_string($con,$_POST['i_id']);
$status = mysqli_real_escape_string($con,$_POST['status']);
$status_date = date("Y-m-d");
$ex=mysqli_query($con,"UPDATE `idea` SET `status`='$status' WHERE `i_id`='$c_id'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}

if(isset($_POST['j_id'])){
    
$j_id = mysqli_real_escape_string($con,$_POST['j_id']);
$status = mysqli_real_escape_string($con,$_POST['status']);

$ex=mysqli_query($con,"UPDATE `applications` SET `status`='$status' WHERE `a_id`='$j_id'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}

if(isset($_POST['newsid'])){
    
$newsid = mysqli_real_escape_string($con,$_POST['newsid']);
$status = mysqli_real_escape_string($con,$_POST['status']);
$cate = mysqli_query($con,"SELECT `category`,`title`,`newsurl`,`hashtags`,`short_description`,`image`,`status` AS `old_status` FROM `news` WHERE `newsid`='$newsid'");
$cat = mysqli_fetch_array($cate);
extract($cat);

// Push to all installed / subscribed devices when news becomes Published
if ($status === 'Published' && (!isset($old_status) || $old_status !== 'Published')) {
    include_once __DIR__ . '/push_news.php';
    naradmuni_send_news_push($con, $title, $short_description, $image, $newsurl);
}
  
if($category==4 && $status === 'Published'){
    
                                require_once __DIR__ . '/vendor/autoload.php'; // change path as needed
                                $fb = new Facebook\Facebook([
                                    'app_id' => '739461563659883',
                                    'app_secret' => 'b6d97e06fb7e32362bb6a63293d729d8',
                                    'default_graph_version' => 'v2.2',
                                ]);
                                //Post property to Facebook
                                $linkData = [
                                    'link' => $publicroot.'news/'.$newsurl,
                                    'message' => $title.' '.$hashtags
                                ];
                                $pageAccessToken ='EAAKgiUZCTtmsBOz4R6ZBjZC9ubsx61I9koJDIs9akZCOMZAqdDpr3knNWTMZAwfSetbE2czKWzvtyqbuoVCpzHZBihFagHOjSxkZBQZCetohXabYX93mjquPMu5GluCSFx2zftEEweDIflfdhqzZANpJPxV2XU3IdJTjQZAZCf6lOhEK2oY5NV9uiQYbw4yvhjXUaZCwZD';

                                try {
                                    $response = $fb->post('/me/feed', $linkData, $pageAccessToken);
                                } catch(Facebook\Exceptions\FacebookResponseException $e) {
                                    echo 'Graph returned an error: '.$e->getMessage();
                                    exit;
                                } catch(Facebook\Exceptions\FacebookSDKException $e) {
                                    echo 'Facebook SDK returned an error: '.$e->getMessage();
                                    exit;
                                }
                                $graphNode = $response->getGraphNode();
    
    
        // include autoload
        //require_once __DIR__ . '/vendor/autoload.php';

            // settings for twitter api connection
            $settings = array(
                'oauth_access_token' => "1346687269462179840-oO4eagZ0tmqmQjrU3rsOqwbapq4MOD",
                'oauth_access_token_secret' => "syji3vxA66C1KAgFb0KjDSGyJw5Ms97VbqbugSuv4h8nx",
                'consumer_key' => "CpQkHYojgbKslvnEo3jhn2HF6",
                'consumer_secret' => "daytbNBtmExISAh3aS7oSToDE4KmqQ5y6MbA27XkAHtYXH1uNF"
            );

            // twitter api endpoint
            $url = 'https://api.twitter.com/1.1/statuses/update.json';

            // twitter api endpoint request type
            $requestMethod = 'POST';

            // twitter api endpoint data
            $apiData = array(
                'status' => $title.' '.$hashtags.' '.$publicroot.'news/'.$newsurl,
            );

            // create new twitter for api communication
            $twitter = new TwitterAPIExchange( $settings );

            // make our api call to twiiter
            $twitter->buildOauth( $url, $requestMethod );
            $twitter->setPostfields( $apiData );
            $response = $twitter->performRequest( true, array( CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0 ) );

            // display response from twitter
            //echo '<pre>';
            //print_r( json_decode( $response, true ) );
    
}
$ex=mysqli_query($con,"UPDATE `news` SET `status`='$status' WHERE `newsid`='$newsid'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}
?>