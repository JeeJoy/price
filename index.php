<?php
	include_once "core/config.php";
    
    if($_SERVER['REQUEST_URI'] == '/index.php') { // "Убираем" index.php из адресной строки
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: /');
        exit();
    }
    
    if (!empty($_SESSION['login'])) {
    	require_once('./core/meta.php');
        require_once('./templates/top.php');
        require_once('./core/body.php');
        require_once('./templates/bottom.php');
    } else {
        require_once('./templates/signin.php');
    }
?>
