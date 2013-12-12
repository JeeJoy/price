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
    
    <script src="../../js/main.js"></script>
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
          <!-- <a class="navbar-brand" href="#">Script for prices</a> -->
        </div>
        <div class="collapse navbar-collapse">
          <ul id="myTab" class="nav navbar-nav">
            <li><a href="#home" data-toggle="tab">Инфо</a></li>
			<li class="active"><a href="#history" data-toggle="tab">История</a></li>
			<li><a href="#rules" data-toggle="tab">Правила</a></li>
			<li class="dropdown">
	        	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Настройки <b class="caret"></b></a>
	        	<ul class="dropdown-menu">
	        		<li class="disabled"><a href="#settings." data-toggle="tab">Основные</a></li>
	          		<li class="disabled"><a href="#log." data-toggle="tab">Системный лог</a></li>
	          	</ul>
	      	</li>
			<li><a href="/logout.php">Выйти</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">
<?php
	if ($error == '1') {
      echo('<div class="alert alert-danger fade in">
        <strong>Alert!</strong> Error in rules.
      </div>');
	}
?>
	<div class="row" id="messagesRow" style="display: none;">
		<div class="col-md-12">
			<div class="alert alert-info" id="messages"></div>
	    </div>
	</div>
      <div class="tab-content">
		<div class="tab-pane" id="home">
		  <div class="starter-template">
			<h1>Скрипт обработки прайс-листов.</h1>
			<p class="lead">В процессе разработки и отладки.</p>
			<h1>Над чем идет работа:</h1>
			<div style="display: inline-block;">
				<div align="left">
					<ul>
						<li><p class="lead">Обработка архивов.</p></li>
						<li><p class="lead">Добавление подсказок.</p></li>
						<li><p class="lead">Оптимизация лога.</p></li>
						<li><p class="lead">Общая оптимизация кода.</p></li>
					</ul>
				</div>
			</div>
			<h1>Подробнее:</h1>
			<div style="display: inline-block;">
				<div align="left">
					<ul>
						<li>В текущий момент скрипт пока не умеет распаковывать архивы. Поэтому он, без проверки по имени файла, перемещает полученый архив (при условии, что адрес с темой совпали) в конечную папку следуя правилу.
							Лог с обработкой данного архива окажется в "Bad" разделе. Архив нужно вручную распаковать и проверить в нем файлы.</li>
						<li>Отсутствует проверка по дате. В скором времени добавлю.</li>
					</ul>
				</div>
			</div>
		  </div>
		</div>
		<div class="tab-pane active" id="history">
			<div class="row" id="scanMail">
				<div class="col-md-12">
					<button type="button" class="btn btn-primary" title="Ручная проверка" onclick="loadMail(this);">Сканировать почту</button>
				</div>
			</div><br>
			<div class="row">
				<div class="col-md-3">
					<ul id="myMenu" class="nav nav-pills nav-stacked">
					  <li class="active"><a href="#good">Good (<?php echo($logGB->getCount('good')); ?>)</a></li>
					  <li><a class="bad" href="#bad">Bad (<?php echo($logGB->getCount('bad')); ?>)</a></li>
					</ul>
				</div>
				<div class="col-md-9">
					<div class="tab-content">
						<div class="tab-pane active" id="good">
							<?php $logGB->printList('good'); ?>
						</div>
						<div class="tab-pane" id="bad">
							<?php $logGB->printList('bad'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="rules">
			<button type="button" class="btn btn-primary" onclick="setModal('newRule');")>Создать</button>
<?php
	$rules = loadRule();
	if (count($rules[0])) {
		echo('<table class="table table-hover">
			<thead>
				<tr><th>ID</th><th>Provider</th><th width="60%">Rule</th><th width="65px"></th></tr>
			</thead>
			<tbody>');
		$i = 0;
		foreach($rules as $index => $val)
	    {
	        echo('<tr>
				<td>'.$rules[$i]['ID'].'</td>
				<td>'.$rules[$i]['Provider'].'</td>
				<td>От "'.$rules[$i]['From'].'" с темой "'.$rules[$i]['Subject'].'".');
				
			if ($rules[$i]['Text'])
				echo (' Текст письма "'.$rules[$i]['Text'].'".');
				
			if ($rules[$i]['TextInFilename'])
				echo (' Слова в имени файла "'.$rules[$i]['TextInFilename'].'".');
				
			echo (' Переименовать в "'.$rules[$i]['NewFilename'].'" и сохранить в "'.$rules[$i]['Path'].'".</td>
				<td>
					<div class="btn-group" style="display: inline-block;">
						<button title="Редактировать" type="button" class="btn btn-default btn-xs" onClick="editRule('.$rules[$i]['ID'].');">
							<span class="glyphicon glyphicon-pencil"></span>
						</button>
						<button title="Удалить" type="button" class="btn btn-default btn-xs" onClick="preDelRule('.$rules[$i]['ID'].');">
							<span class="glyphicon glyphicon-remove"></span>
						</button>
					</div>
				</td>
			</tr>');
				
			$i++;
	    }
		echo ('</tbody>
			</table>');
	} else {
		echo('<center><h1>Cписок правил пуст.</h1></center>');
	}
?>
		</div>
		<div class="tab-pane" id="settings"><!-- Text --></div>
	  </div>


    </div><!-- /.container -->
	
	<div id="modal" class="modal fade"></div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
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
			$('#myPopover').popover(options);
		});
	</script>
  </body>
</html>
