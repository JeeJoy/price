function sleep(ms) {
	ms += new Date().getTime();
	while (new Date() < ms){}
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
	$("#messagesRow").hide('slide',{ direction: 'up' }, 500,function() {
		document.all.messages.innerHTML = '<strong>Сканирование почты!</strong> Подождите, пожалуйста. Страничка скоро перезагрузится автоматически.';
		document.all.messages.className = 'alert alert-info';
		$("#messagesRow").show('slide',{ direction: 'up' }, 500,function() {
			$.post("core/script.php",{ }, function(data) {
				$("#messagesRow").hide('slide',{ direction: 'up' }, 500,function() {
					if (data.indexOf('Error: Connecting to mail server') > -1) {
						document.all.messages.innerHTML = '<button type="button" class="close" aria-hidden="true" onclick="MsgCls();">&times;</button><strong>Ошибка!</strong> Не удалось установить соединение с почтой. Попробуйте позже, либо свяжитесь с сис.администратором.';
						document.all.messages.className = 'alert alert-danger';
						$("#messagesRow").show('slide',{ direction: 'up' }, 500,function() {
							elm.setAttribute('onclick', 'loadMail(this);');
						});
					} else if (data.indexOf('Error') > -1 || data.indexOf('error') > -1) {
						document.all.messages.innerHTML = '<button type="button" class="close" aria-hidden="true" onclick="MsgCls();">&times;</button><strong>Внимание!</strong> Обнаружена ошибка. Пожалуйста, сообщите об этом сис.администратору.';
						document.all.messages.className = 'alert alert-danger';
						$("#messagesRow").show('slide',{ direction: 'up' }, 500,function() {
							elm.setAttribute('onclick', 'loadMail(this);');
						});
					} else if (data.indexOf('Call to undefined function imap_open') > -1) {
						document.all.messages.innerHTML = '<strong>Ошибка!</strong> Не установлено расширение "imap".';
						document.all.messages.className = 'alert alert-danger';
						$("#messagesRow").show('slide',{ direction: 'up' }, 500,function() {
							sleep(3000);
							location.reload();
		    			});
					} else if (data.indexOf('Done') > -1) {
						document.all.messages.innerHTML = '<strong>Готово!</strong> Обновляю страничку.';
						document.all.messages.className = 'alert alert-success';
						$("#messagesRow").show('slide',{ direction: 'up' }, 500,function() {
							sleep(3000);
							location.reload();
		    			});
		    		} else {
						document.all.messages.innerHTML = '<strong>Внимание!</strong> Скрипт долгое время не отвечал. Попробуйте еще раз, либо обратитесь к сис.администратору.';
						document.all.messages.className = 'alert alert-warning';
						$("#messagesRow").show('slide',{ direction: 'up' }, 500,{ });
		    		}
		    		elm.innerHTML = 'Сканировать почту';
					elm.disabled = '';
	    		});
    		})
    		.fail(function() {
    			$("#messagesRow").hide('slide',{ direction: 'up' }, 500,function() {
					document.all.messages.innerHTML = '<button type="button" class="close" aria-hidden="true" onclick="MsgCls();">&times;</button><strong>Внимание!</strong> Скрипт выполнен с ошибкой.';
					document.all.messages.className = 'alert alert-warning';
					$("#messagesRow").show('slide',{ direction: 'up' }, 500,function() {
						elm.setAttribute('onclick', 'loadMail(this);');
						elm.innerHTML = 'Сканировать почту';
						elm.disabled = '';
					});
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
	//return eval('(' + json + ')');
}

function editRule(id) { // Загрузка параметров правила
	$.post("core/post.php",{ jquery: 1, query: 'editRule', id: id }, function(data) { // Загрузка удалась
		//alert(data);
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
	document.all.modal.innerHTML = data; // Перерисовываем окно

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
