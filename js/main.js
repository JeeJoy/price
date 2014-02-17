/*$(function() {
	var $contextMenu = $("#contextMenu");
	$("body").on("contextmenu", "table tr", function(e) {
		$contextMenu.css({
			display: "block",
			left: e.pageX,
			top: e.pageY
		});
		return false;
	});
});*/

function sleep(ms) {
	ms += new Date().getTime();
	while (new Date() < ms){}
}

function mycontextMenu(id) {
	/*if (!e.which && e.button) {
		if (e.button & 1) e.which = 1
		else if (e.button & 4) e.which = 2
		else if (e.button & 2) e.which = 3*/
		//e.style.display = 'none';
		//$('.dropdown-toggle').dropdown();
		//e.setAttribute('onclick', 'return false;');
	//}
	var $contextMenu = $("#contextMenu");
	var e = window.event;
	$contextMenu.css({
		display: "block",
		left: e.clientX,
		top: e.clientY
	});
	/*$("body").on("contextmenu", "tr", function(e) {
		$contextMenu.css({
			display: "block",
			left: e.clientX,
			top: e.clientY
		});
		return false;
	});*/
}

function preSubmit() {
	var err = false; // После теста исправить на false
	var titles = ['Имя поставщика', 'От кого', 'Тема письма', 'Слова в имени файла', 'Путь'];
	var names = ['provider', 'from', 'subject', 'textinfilename', 'path'];
	for (var i = 0; i < names.length; i++) {
		var list = document.getElementsByName(names[i]);
		if ($.trim(list[0].value) == "") {
			err = true;
			alert('Заполните поле "' + titles[i] + '".');
			break;
		}
	}
	
	if (err)
		return false;
}

function MsgCls() { // Плавное закрытие уведомления
	$("#messagesRow").hide('slide',{ direction: 'up' }, 500,{ });
}

function loadMail(elm) { // Загрузка почты
	elm.setAttribute('onclick', 'return false;');
	elm.innerHTML = 'Загрузка';
	elm.disabled = 'disabled';
	
	var o=document.getElementById('rules');
	var count = o.rows.length;
	var done = 0;
	for (i=1; i < count; i++) {
		o.rows[i].className = '';
		o.rows[i].cells[2].innerHTML = '';
		o.rows[i].cells[3].innerHTML = '';
	}
	
	$("#messagesRow").hide('slide',{ direction: 'up' }, 500,function() {
		//alert(count);
		var progressBar = '<br><div class="progress progress-striped active" style="margin-bottom: 0px;"><div id="progressBar" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div></div>';
		document.all.messages.innerHTML = '<strong>Сканирование!</strong> Пожалуйста, подождите.' + progressBar;
		document.all.messages.className = 'alert alert-info';
		//alert( $('#container').width() );
		//alert( $('#messagesRow').width() );
		//alert( $('#container').width() - $('#messagesRow').width() );
		document.all.messagesRow.style.left = ( $(document).width() - $('#messagesRow').width() ) / 2 + 'px';
		$("#messagesRow").show('slide',{ direction: 'up' }, 500,function() {
			//return;
			$.post("core/script.php",{ sdo: 'getr' }, function(data) {
				try {
					// Получаем массив с правилами
					var rules = jsonDecode(data); // Если вернет не json, сработает исключение
					//alert(rules[0].ID);
					// Обрабатываем письма
					var o=document.getElementById('rules');
					for(var i=1; i<=rules.length; i++) {
						//$.post("core/script2.php",{ sdo: 'process', id: rules[i].ID }, function(data) {
						//	alert("Прислали ID " + data);
						//});
						o.rows[i].className = 'success';
						o.rows[i].cells[2].innerHTML = '<img src="img/loading.gif"/>';
						//o.rows[i].cells[3].innerHTML = 'In processing...';
						o.rows[i].cells[3].innerHTML = 'В обработке...';
						$.ajax({
							type: "POST",
							url: "core/script.php",
							data: "sdo=process&id=" + rules[i-1].ID,
							async: false,
							success: function (msg) {
								//if (msg != "off")
								//alert(rules[i].ID + ": " + msg);
								var result = jsonDecode(msg);
								if (result.Comment == "off") {
									o.rows[i].className = '';
									o.rows[i].cells[2].innerHTML = '<span class="glyphicon glyphicon-minus-sign" style="color: black;"></span>';
								} else if (result.Error == 14) {
									o.rows[i].className = '';
									o.rows[i].cells[2].innerHTML = '<span class="glyphicon glyphicon-ok" style="color: green;"></span>';
									o.rows[i].cells[3].innerHTML = 'Обновлений не было';
								} else if (result.Error != false) {
									o.rows[i].className = 'danger';
									o.rows[i].cells[2].innerHTML = '<span class="glyphicon glyphicon-exclamation-sign" style="color: red;"></span>';
								} else {
									//o.rows[i].className = '';
									var d = new Date(result.Update*1000);
									var val = [ d.getFullYear(), d.getMonth()+1, d.getDate(), d.getHours(), d.getMinutes(), d.getSeconds() ];
									for( var id in val ) {
										val[ id ] = val[ id ].toString().replace( /^([0-9])$/, '0$1' );
									}
									o.rows[i].cells[1].innerHTML = val[0] + '-' + val[1] + '-' + val[2] + ' ' + val[3] + ':' + val[4] + ':' + val[5];
									o.rows[i].cells[2].innerHTML = '<span class="glyphicon glyphicon-ok" style="color: green;"></span>';
									o.rows[i].cells[3].innerHTML = 'Обновлено';
								}
								if (result.Comment.length)
									o.rows[i].cells[3].innerHTML = result.Comment;
							}
						});
						//o.rows[i].cells[2].innerHTML = '';
						done = done + 1;
						var perc = (100 / count) * done
						document.getElementById('progressBar').style.width = perc + "%";
						//alert(done);
					}
					document.getElementById('progressBar').style.width = "100%";
					
					$("#messagesRow").hide('slide',{ direction: 'up' }, 500,function() {
						document.all.messages.innerHTML = '<strong>Готово!</strong>';
						document.all.messages.className = 'alert alert-success';
						document.all.messagesRow.style.left = ( $(document).width() - $('#messagesRow').width() ) / 2 + 'px';
						$("#messagesRow").show('slide',{ direction: 'up' }, 500,function() {
							//location.reload();
		    			});
						
						elm.innerHTML = 'Сканировать';
						elm.disabled = '';
						elm.setAttribute('onclick', 'loadMail(this);');
		    		});
				}
				catch (e) { // Не json
					$("#messagesRow").hide('slide',{ direction: 'up' }, 500,function() {
						document.all.messages.innerHTML = '<strong>Ошибка! Исключение.</strong>';
						document.all.messages.className = 'alert alert-danger';
						document.all.messagesRow.style.left = ( $(document).width() - $('#messagesRow').width() ) / 2 + 'px';
						$("#messagesRow").show('slide',{ direction: 'up' }, 500,{ });
						
						elm.innerHTML = 'Сканировать';
						elm.disabled = '';
						elm.setAttribute('onclick', 'loadMail(this);');
		    		});
				}
    		})
    		.fail(function(data) {
    			$("#messagesRow").hide('slide',{ direction: 'up' }, 500,function() {
					document.all.messages.innerHTML = '<button type="button" class="close" aria-hidden="true" onclick="MsgCls();">&times;</button><strong>Внимание!</strong> Скрипт выполнен с ошибкой.';
					document.all.messages.className = 'alert alert-warning';
					document.all.messagesRow.style.left = ( $(document).width() - $('#messagesRow').width() ) / 2 + 'px';
					$("#messagesRow").show('slide',{ direction: 'up' }, 500,{ });
					elm.innerHTML = 'Сканировать';
					elm.disabled = '';
					elm.setAttribute('onclick', 'loadMail(this);');
				});
			})
			.always(function() {
				//
			});
		});
	});
}

function jsonDecode(json) { // Из json в массив
	return JSON.parse(json);
}

function editRule(id) { // Загрузка параметров правила
	$.post("core/post.php",{ jquery: 1, query: 'editRule', id: id }, function(data) { // Загрузка удалась
		setModal('editRule', jsonDecode(data));
	})
	.fail(function() { // Загрузка НЕ удалась
		alert('core/post.php error');
	});
}

function preDelRule(id) { // Вызов вопроса перед удалением правила
	setModal('delRule', id);
}

function delRule(id) { // Удаление правила
	$.post("core/post.php",{ jquery: 1, query: 'delRule', id: id }, function(data) { // Загрузка удалась
		if (data != 'Deleted')
			alert('Ошибка');
			
		location.reload();
			
	})
	.fail(function() { // Загрузка НЕ удалась
		alert('core/post.php error');
	});
}

function find(array, value) {
	for(var i=0; i<array.length; i++)
    	if (array[i] == value) return i;
   
  	return false;
}

function setModal(query, params) {
	$.post("templates/modals/" + query + ".tpl",{ }, function(data) { // Шаблон найден
	//document.all.modal.innerHTML = data; // Перерисовываем окно
	document.getElementById('modal').innerHTML = data;

	var queries = ['editRule', 'delRule'];
	var index = find(queries, query);
	if (index !== false) {
		switch (index) {
			case 0:
				$.each(params, function(key, val) {
					if (key == 'ID') key = 'ruleID';
				    if (elm = document.getElementById(key)) elm.value = val;
				});
				break;
			case 1:
				document.getElementById('modalBody').innerHTML = '<p><h4>Вы собираетесь удалить правило #' + params + '. Согласны с этим?</h4></p>';
				document.getElementById('modalDelRule').setAttribute('onclick', 'delRule(' + params + ');');
				break;
			}
		}
				
		$('#modal').modal('show'); // Выводим
	})
	.fail(function() { // Шаблон НЕ найден
		alert("templates/modals/" + query + ".tpl not found");
	});
}
