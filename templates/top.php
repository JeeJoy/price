<?php
    include_once "core/config.php";

    if($_SERVER['REQUEST_URI'] == '/index.php') { // "Убираем" index.php из адресной строки
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: /');
        exit();
    }

    if (empty($_SESSION['login'])) { // Если не авторизовались, то переходим на страничку авторизации
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/signin.php');
        exit();
    }
    
    $error = 0; // Не ниже строки include_once "core/post.php";
    
    include_once "core/post.php";
    
    $logGB = new LogGoodBad();
    
    include "top.tpl"
?>