<?php
	require_once('database.inc.php');
	require_once("mysql_connect_data.inc.php");
	
	$host = "puccini.cs.lth.se";
	$username = "";
	$password = "";
	$database = "";


	$db = new Database($host, $username, $password, $database);
	$db->openConnection();
	if (!$db->isConnected()) {
		header("Location: cannotConnect.html");
		exit();
	}
	
	$userId = $_REQUEST['userId'];
	if (!$db->userExists($userId)) {
		$db->closeConnection();
		header("Location: noSuchUser.html");
		exit();
	}
	$db->closeConnection();
	
	session_start();
	$_SESSION['db'] = $db;
	$_SESSION['userId'] = $userId;
	header("Location: booking1.php");
?>
