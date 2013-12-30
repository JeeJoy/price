<?php
	include_once "core/config.php";
	
	/*if(substr($_SERVER['REQUEST_URI'], 0, 8) != '/signin/') {
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/signin/');
		exit();
	}*/
	
	if (!empty($_SESSION['login'])) { // Если уже авторизовались, то переходим на главную страничку
		header('Location: index.php');
		exit();
	}
    
    include "signin.tpl";
?>
