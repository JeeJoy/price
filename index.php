<?php
    include_once "core/config.php";
    
    if (!empty($_SESSION['login'])) {
    	require_once('./core/meta.php');
        require_once('./templates/top.php');
        require_once('./core/body.php');
        require_once('./templates/bottom.php');
    } else {
        require_once('./templates/signin.php');
    }
?>
