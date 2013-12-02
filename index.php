<?php
	include_once "core/config.php";

	if($_SERVER['REQUEST_URI'] == '/index.php') { // "Убираем" index.php из адресной строки
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: http://'.$_SERVER['HTTP_HOST']);
		exit();
	}

	if (empty($_SESSION['login'])) { // Если не авторизовались, то переходим на страничку авторизации
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/signin.php');
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
    <link rel="shortcut icon" href="../../ico/favicon.ico">

    <title>Script for prices</title>

    <!-- Bootstrap core CSS -->
    <link href="../../css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="theme.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Script for prices</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul id="myTab" class="nav navbar-nav">
            <li><a href="#home" data-toggle="tab">Home</a></li>
			<li><a href="#history" data-toggle="tab">History</a></li>
			<li class="active"><a href="#rules" data-toggle="tab">Rules</a></li>
			<li><a href="#settings" data-toggle="tab">Settings</a></li>
			<li><a href="/logout.php">Logout</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">

      <div class="tab-content">
		<div class="tab-pane" id="home">
		  <div class="starter-template">
			<h1>Bootstrap starter template</h1>
			<p class="lead">Use this document as a way to quickly start any new project.<br> All you get is this text and a mostly barebones HTML document.
			<br>Ку-ку</p>
		  </div>
		</div>
		<div class="tab-pane" id="history">
			<div class="row">
				<div class="col-md-3">
					<ul id="myMenu" class="nav nav-pills nav-stacked">
					  <li class="active"><a href="#good">Good (99)</a></li>
					  <li><a class="bad" href="#bad">Wrong (99)</a></li>
					</ul>
				</div>
				<div class="col-md-9">
					<div class="tab-content">
						<div class="tab-pane active" id="good">
							<table class="table table-hover">
								<thead>
									<tr><th>ID</th><th>Message</th></tr>
								</thead>
								<tbody>
									<tr class="success"><td>1</td><td>Bla-bla-bla</td></tr>
									<tr class="success"><td>2</td><td>Bla-bla-bla</td></tr>
									<tr class="success"><td>3</td><td>Bla-bla-bla</td></tr>
								</tbody>
							</table>
						</div>
						<div class="tab-pane" id="bad">
							<table class="table table-hover">
								<thead>
									<tr><th>ID</th><th>Message</th></tr>
								</thead>
								<tbody>
									<tr class="danger"><td>1</td><td>Bla-bla-bla</td></tr>
									<tr class="danger"><td>2</td><td>Bla-bla-bla</td></tr>
									<tr class="danger"><td>3</td><td>Bla-bla-bla</td></tr>
									<tr class="danger"><td>1</td><td>Bla-bla-bla</td></tr>
									<tr class="danger"><td>2</td><td>Bla-bla-bla</td></tr>
									<tr class="danger"><td>3</td><td>Bla-bla-bla</td></tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane active" id="rules">
			<button type="button" class="btn btn-primary">New</button>
			
			<table class="table table-hover">
				<thead>
					<tr><th>ID</th><th width="100%">Rule</th><th></th></tr>
				</thead>
				<tbody>
					<tr><td>1</td><td>Bla-bla-bla</td><td><button title="Delete rule" type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button></td></tr>
					<tr><td>2</td><td>Bla-bla-bla</td><td><button title="Delete rule" type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button></td></tr>
					<tr><td>3</td><td>Bla-bla-bla</td><td><button title="Delete rule" type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button></td></tr>
					<tr><td>1</td><td>Bla-bla-bla</td><td><button title="Delete rule" type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button></td></tr>
					<tr><td>2</td><td>Bla-bla-bla</td><td><button title="Delete rule" type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button></td></tr>
					<tr><td>3</td><td>Bla-bla-bla</td><td><button title="Delete rule" type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button></td></tr>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="settings">444</div>
	  </div>


    </div><!-- /.container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
	<script>
		$(document).ready(function()
		{
			$('#myTab a').click(function (e) {
			  //e.preventDefault() // Аналог "return false" или типа того
			  $(this).tab('show')
			});
			$('#myMenu a').click(function (e) {
			  //e.preventDefault() // Аналог "return false" или типа того
			  $(this).tab('show')
			});
		});
	</script>
  </body>
</html>
