<?php	ini_set('display_errors', 1);  	error_reporting(E_ALL);	session_start();	header("Content-Type: text/html; charset=utf-8");	$alogin = "admin";	$apass = "ef4ddf645aa223c0b2b1356fbb4fd90a";		define("MAX_MAIL", 30);	define("SCRIPT_DEBUG", FALSE);	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];	$DOCUMENT_ROOT = '/var/www/rudin/data/www/saascom.ru';		include_once $DOCUMENT_ROOT."/core/func.php";?>