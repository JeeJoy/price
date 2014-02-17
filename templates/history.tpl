<div class="row" id="scanMail">
    <div class="col-md-12">
<?php if (DEVELOP_MODE): ?>
    	<a href="/core/get.php?dev=0" class="btn btn-primary" role="button" title="Вернуться в обычный режим">Вернуться</a>
<?php else: ?>
        <button type="button" class="btn btn-primary" title="Ручная проверка" onclick="loadMail(this);">Сканировать</button>
        <a href="/core/get.php?dev=1" class="btn btn-primary" role="button" title="Перейти в режим просмотра отчетов">Logs</a>
<?php endif; ?>
    </div>
</div><br>
<div class="row">
<?php
	if (!DEVELOP_MODE):
		$updater = new Update();
		$result = $updater->check();
?>
	<div class="col-md-12">
		<div id="contextMenu" class="dropdown clearfix" style="position: absolute; display:none;">
			<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu" style="display:block;position:static;margin-bottom:5px;">
				<li>123</li>
				<li>456</li>
				<li>789</li>
			</ul>
		</div>

		<table class="table table-bordered table-condensed" id="rules">
			<thead>
				<tr>
					<th width="20%">Поставщик</th>
					<th width="20%">Обновление</th>
					<th>Статус</th>
					<th width="100%">Результат</th>
				</tr>
			</thead>
			<tbody>
<?php
	for ($i=0; $i < count($result); $i++) {
		$fontTR = '';
		$statusTR = '';
		$statusTD = '';
		$icon = 'ok';
		$iconColor = 'green';
		
		$updateText = $update = (int)$result[$i]['Update'];
		if (!$update) $updateText = "Обновлений <nobr>не было</nobr>";
		else $updateText = date('Y-m-d H:i:s', $update);
		
		$status = 1;
		if ($result[$i]['Status'] == 'off') $status = 0;
		if ($status) {
			//$update = $result[$i]['Update'];
			if (!strlen($update) or (strlen($result[$i]['Error']) and $result[$i]['Error'] != 14)) {
				$statusTR = ' class="danger"';
				$icon = 'exclamation-sign';
				$iconColor = 'red';
			} else {
				$statusTR = '';
				$nowTime = time();
				$oneDay = 86400;
				$twoDays = $oneDay*2;
				$diff = $nowTime - $update;
				if ($diff >= $twoDays)
					$statusTD = ' class="danger"';
				elseif ($diff >= $oneDay)
					$statusTD = ' class="warning"';
				else
					$statusTD = '';
			}
		} else {
			$fontTR = ' style="color: #999999;"';
			$icon = 'minus-sign';
			$iconColor = 'black';
		}
		
		echo("<tr" . $statusTR . $fontTR . " id='rule".($i+1)."' oncontextmenu=\"mycontextMenu(this); return false;\">
			<td>".$result[$i]['Provider']."</td>
			<td align='center'$statusTD>".$updateText."</td>
			<td align='center'><span class='glyphicon glyphicon-$icon' style='color: $iconColor;'></span></td>
			<td>".$result[$i]['Comment']."</td>
		</tr>");
	}
?>
			</tbody>
		</table>
	</div>
<?php else: ?>
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
<?php endif; ?>
</div>