<?php

function connect(){
	$connection =mysqli_connect('localhost', 'thenaradmunicom_db', 'RIzx63ZTUNeqx', 'thenaradmunicom_db');
    mysqli_set_charset($connection,"utf8");
	if(!$connection){
		die('Failed to connect Database');
	}
	return $connection;
}

function show_menu(){
	$connection = connect();
	$menus = '';
	$menus .= generate_multilevel_menus($connection);
	return $menus;
}

function generate_multilevel_menus($connection, $parent=NULL ){
	global $urlroot;
	$menu = "";

	if (is_null($parent)) {
		$sql = "SELECT * FROM `categories` WHERE `parent` IS NULL AND `menu`='Yes' ORDER BY `short` ASC";
	} else {
		$sql = "SELECT * FROM `categories` WHERE `parent`=$parent AND `menu`='Yes' ORDER BY `short` ASC";
	}

	$result = mysqli_query($connection, $sql);

	while ($row = mysqli_fetch_assoc($result)) {
		$menu .= '<li><a href="'.$urlroot.'category/'.$row['cat_url'].'">'.$row['hindi_name'].'</a>';
		$menu .= '<ul class="nav-dropdown">'.generate_multilevel_menus($connection, $row['id']).'</ul></li>';
	}
	return $menu;
}
?>

