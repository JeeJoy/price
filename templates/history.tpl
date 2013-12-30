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