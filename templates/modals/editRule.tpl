<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				&times;
			</button>
			<h4 class="modal-title" id="modalTitle">Редактирование правила</h4>
		</div>
		<form name="formEditRule" method="post" onSubmit="return preSubmit();">
			<input type="hidden" name="ruleID" id="ruleID">
			<div class="modal-body">
				<p>
					<div class="input-group">
						<span class="input-group-addon">Имя поставщика *</span>
						<input name="provider" id="Provider" type="text" class="form-control" placeholder="">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">От кого *</span>
						<input name="from" id="From" type="text" class="form-control" placeholder="">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Тема письма **</span>
						<input name="subject" id="Subject" type="text" class="form-control" placeholder="">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Слова в тексте</span>
						<input name="text" id="Text" type="text" class="form-control" placeholder="(необязательно)">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Слова в имени файла **</span>
						<input name="textinfilename" id="TextInFilename" type="text" class="form-control" placeholder="">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Новое имя файла</span>
						<input name="newfilename" id="NewFilename" type="text" class="form-control" placeholder="(необязательно)">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Путь *</span>
						<input name="path" id="Path" type="text" class="form-control" placeholder="Путь до места хранения файла">
					</div>
				</p>
				* - обязательно точное значение.
				<br>
				** - обязательно примерное значение.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					Закрыть
				</button>
				<input class="btn btn-primary" name="formEditRule" type="submit" value="Изменить">
			</div>
		</form>
	</div>
</div>