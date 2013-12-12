<?php 
	include_once "core/config.php";
	
	header("Location: index.php");
	
	if (empty($_SESSION['login']))
		exit();
	
	unset($_SESSION['login']);
?>
