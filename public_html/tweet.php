<?php
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');

//require_once __DIR__ . '/vendor/autoload.php';

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
    'oauth_access_token' => "1346687269462179840-B5l5uMX6XsUz5whwnOL0REs1DK1Snn",
    'oauth_access_token_secret' => "iBNoNUJ0Kga76T3sc8hVttnf47Wj2iUvqimWgGmNs0mNF",
    'consumer_key' => "7LOF23icbqUcyqvCW2d0RQT14",
    'consumer_secret' => "jzOwe91yEqf8wazSs2Y2Dg4SY3ljgxx06JcviBnOXidVu4BRqr"
);

/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
$url = 'https://api.twitter.com/1.1/blocks/create.json';
$requestMethod = 'POST';

/** POST fields required by the URL above. See relevant docs as above **/
$postfields = array(
    'screen_name' => 'usernameToBlock', 
    'skip_status' => '1',
    'status' => 'This tweet is comming from an awesome script written using php and the Twitter API! #Geek #PHP #TwitterAPI',
);

/** Perform a POST request and echo the response **/
$twitter = new TwitterAPIExchange($settings);
echo $twitter->buildOauth($url, $requestMethod)
             ->setPostfields($postfields)
             ->performRequest();

/** Perform a GET request and echo the response **/
/** Note: Set the GET field BEFORE calling buildOauth(); **/
/*$url = 'https://api.twitter.com/1.1/followers/ids.json';
$getfield = '?screen_name=J7mbo';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
echo $twitter->setGetfield($getfield)
             ->buildOauth($url, $requestMethod)
             ->performRequest(); */