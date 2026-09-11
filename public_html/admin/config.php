<?php
    // Browser URLs are on :3000. Apache :8080 is only the PHP/image engine (proxied by Next).
    $urlroot = "http://localhost:3000/naradmuni/";   // admin + static assets via Next proxy
    $publicroot = "http://localhost:3000/";          // public Next.js site (SEO paths)
    $adminLoginUrl = $urlroot . "manage.php";        // ONE admin login only


	// variable declaration
if(!isset($_SESSION)){
session_start();
}

	// variable declaration
	$aemail = "";
	$apwd    = "";
	$errors = array(); 
    $sucs = array();
	$_SESSION['success'] = "";

	// connect to database
    $server = "localhost";
    $user   = "thenaradmunicom_db";
    $pass   = "RIzx63ZTUNeqx";
    $db_name = "thenaradmunicom_db";

 $con=mysqli_connect($server,$user,$pass,$db_name) or die("Could not connect DB S");
 mysqli_set_charset($con,"utf8");
if(isset($_SESSION['u_id'])){
    try {
        $uqry = mysqli_query($con,"SELECT `name` FROM `users` WHERE `u_id`='".mysqli_real_escape_string($con, (string)$_SESSION['u_id'])."'");
        $ur = $uqry ? mysqli_fetch_array($uqry) : null;
    } catch (Throwable $e) {
        $ur = null; // local DB may not have `users`
    }
}
 date_default_timezone_set("Asia/Kolkata");
 header( 'Content-Type: text/html; charset=utf-8' );
 require_once __DIR__ . '/admin_helpers.php';
?>
