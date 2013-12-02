<?php 
	include_once "core/config.php";
	
	if (isset($_POST['login'])) {
		$login = $_POST['login'];
		if ($login == '')
			unset($login);
	}
    if (isset($_POST['pass'])) {
		$password=$_POST['pass'];
		if ($password =='')
			unset($password);
	}
	if (empty($login) or empty($password))
	{
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/signin.php');
		exit();
	}
	
	$login = stripslashes($login);
	$login = htmlspecialchars($login);
	$password = stripslashes($password);
	$password = htmlspecialchars($password);
	//удаляем лишние пробелы
    $login = trim($login);
	$password = trim($password);
	
	if ((md5(md5($password)) == $apass) and $login == $alogin) {
		$_SESSION['login'] = $alogin;
		header('Location: /');
	} else {
		//header('Location: /signin/?err=1');
		header('Location: /signin.php?err=1');
	}
?>
