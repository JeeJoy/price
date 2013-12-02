<?php
	include_once "core/config.php";
	
	/*if(substr($_SERVER['REQUEST_URI'], 0, 8) != '/signin/') {
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/signin/');
		exit();
	}*/
	
	if (!empty($_SESSION['login'])) { // Если уже авторизовались, то переходим на главную страничку
		header('Location: /');
		exit();
	}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../docs-assets/ico/favicon.png">

    <title>Signin Template for Bootstrap</title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/signin.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">

<?php
	if ($_GET['err'] == '1') {
      echo('<div class="alert alert-danger fade in">
        <strong>Alert!</strong> Wrong login or password.
      </div>');
	}
?>
	  
	  <form class="form-signin" method="post" action="/verific.php">
        <h2 class="form-signin-heading">Please sign in</h2>
        <input name="login" type="text" class="form-control" placeholder="Login" required autofocus>
        <input name="pass" type="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
  </body>
</html>
