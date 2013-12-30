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
                    <li><a href="/core/logout.php">Выйти</a></li>
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
                <?php include "templates/home.tpl"; ?>
            </div>
            <div class="tab-pane active" id="history">
                <?php include "templates/history.php"; ?>
            </div>
            <div class="tab-pane" id="rules">
                <?php include "templates/rules.php"; ?>
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
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/mainB.js"></script>
</body>
  