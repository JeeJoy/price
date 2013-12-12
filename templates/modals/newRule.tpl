<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				&times;
			</button>
			<h4 class="modal-title" id="modalTitle">Новое правило</h4>
		</div>
		<form name="formNewRule" method="post" onSubmit="return preSubmit();">
			<div class="modal-body">
				<p>
					<div class="input-group">
						<span class="input-group-addon">Имя поставщика *</span>
						<input name="provider" type="text" class="form-control" placeholder="">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">От кого *</span>
						<input name="from" type="text" class="form-control" placeholder="">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Тема письма **</span>
						<input name="subject" type="text" class="form-control" placeholder="">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Слова в тексте</span>
						<input name="text" type="text" class="form-control" placeholder="(необязательно)">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Слова в имени файла **</span>
						<input name="textinfilename" type="text" class="form-control" placeholder="">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Новое имя файла</span>
						<input name="newfilename" type="text" class="form-control" placeholder="(необязательно)">
					</div>
				</p>
				<p>
					<div class="input-group">
						<span class="input-group-addon">Путь *</span>
						<input name="path" type="text" class="form-control" placeholder="Путь до места хранения файла" value="/prices/">
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
				<input class="btn btn-primary" name="formNewRule" type="submit" value="Сохранить">
			</div>
		</form>
	</div>
</div>