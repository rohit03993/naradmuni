<?php    session_start(); 

	if (!isset($_SESSION['an'])) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../manage.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['an']);
		header("location: ../manage.php");
	}
?>