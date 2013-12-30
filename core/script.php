<?php
	ini_set("max_execution_time", "60");
	include_once "config.php";
	include_once "imap.php";
	
	ini_set('display_errors', 1);
  	error_reporting(E_ALL);
	
	// Пример записи вывода в переменную
  	//ob_start();
	//echo 'Hello askdev';
	//$myStr = ob_get_contents();
	//ob_end_clean();
	
	$echo = '';
	
	$logGB = new LogGoodBad();
	
	$arr = loadRule();
	
	// Коннектимся
	//$imap = new receiveMail('address', 'password', 'address', 'server', 'protocol', 'port', 'ssl true/false');
	$imap = new receiveMail('address', 'password', 'address', 'server', 'protocol', 'port', 'ssl true/false');
	$source = $imap->connect();
	$num = $imap->getTotalMails();
	
	// Смотрим письмо
	for ($i = $num; $i > ($num - MAX_MAIL); $i--) {
		$uid = imap_uid($source, $i);
		//if ($uid == 451)
		//{
			ob_start(); // Запускаем буферизацию вывода
			echo("<b>Смотрим письмо ".$i."</b><br>");
			$echo = '';
			$header = imap_header($source, $i);
			$fromInfo = $header->from[0];
			$replyInfo = $header->reply_to[0];
			$email = $fromInfo->mailbox . "@" . $fromInfo->host;
			//$subject = explode("?", $header->subject);
			//$text = $arr[2] == 'Q' ? quoted_printable_decode($text) : base64_decode($text);
			
			echo('Проверяем заголовок темы: ');
			var_dump($header->subject);
			
			// ----------------------------------------------------------------
			// Разбираем тему
			// ----------------------------------------------------------------
			$newSubject = '';
			if (substr($header->subject, 0, 2) == '=?' and substr($header->subject, -2) == '?=') {
				$subject = substr($header->subject, 1, strlen($header->subject)-2); // Удаляем "=" в начале и конце
				echo('<br>Удаляем в начале и конце "=": ');
				var_dump($subject);
				
				$subject = preg_replace('/[\s]{1,}/', ' ', $subject);
				echo('<br>Удаляем двойные пробелы: ');
				var_dump($subject);
				
				$subject = explode("= =", $subject); // Разбиваем в массив по разделителю "= ="
				echo('<br>Разбиваем по "= =": ');
				var_dump($subject);
				
				// Разбиваем строки на подмассивы по разделителю "?"
				for ($j=0; $j < count($subject); $j++)
					$subject[$j] = explode("?", $subject[$j]);
				echo('<br>Разбиваем на подмассивы по "?": ');
				var_dump($subject);
				
				// Декодируем массив и собираем его обратно
				echo('<br>Собираем тему.');
				for ($j=0; $j < count($subject); $j++) {
					$subject[$j][3] = ($subject[$j][2] == 'Q' ? quoted_printable_decode($subject[$j][3]) : base64_decode($subject[$j][3]));
					if 		(strtolower($subject[$j][1]) == strtolower('windows-1251')) $subject[$j][3] = iconv('cp1251', 	'utf-8', $subject[$j][3]);
					elseif 	(strtolower($subject[$j][1]) == strtolower('KOI8-R')) 		$subject[$j][3] = iconv('KOI8-R', 	'utf-8', $subject[$j][3]);
					elseif 	(strtolower($subject[$j][1]) != strtolower('utf-8')) 		$subject[$j][3] = iconv('', 			'utf-8', $subject[$j][3]);
					$newSubject .= $subject[$j][3];
					echo("<br>Часть темы #$j: ");
					var_dump($subject[$j][3]);
				}
			} else {
				$newSubject = $header->subject;
			}
			echo('<br>Новая тема: ');
			var_dump($newSubject);
			// ----------------------------------------------------------------
			
			echo('<br><br>');
			$uid = imap_uid ($source, $i);
			$body = getBody($uid, $source);
			$structure = imap_fetchstructure($source, $i);
			echo('От: '.$email.'<br>
			Тема: '.$newSubject.'<br>');
			echo('Тема64: ');
			var_dump($header->subject);
			echo('<br>ТемаA: ');
			var_dump($subject);
			echo('<br>UID: ');
			var_dump($uid);
			echo('<br>');
			
			// Скачиваем вложения
			echo('Скачиваю вложения<br>');
			$filenames = $imap->GetAttach(false, $i, true); // Получаем имена вложений
			$part = 0;
			foreach ($filenames as $filename)
			{
				echo('Смотрю вложение '.$filename.'<br>');
				$pos = strrpos($filename, ".");
				$ext = substr($filename, $pos, strlen($filename));
				echo('Проверим файл по БД.<br>');
				checkMail($email, $newSubject, $body, $filename);
				unlink($DOCUMENT_ROOT.'/files/tmp/'.$filename);
				$part++;
				echo('<br>');
			}
			echo('<br>');
		//}
		
		$echo = ob_get_contents();
		ob_end_clean(); // Останавливаем буферизацию вывода и очищаем буфер
		
		//if (SCRIPT_DEBUG)
			echo($echo);
	}
	
	$imap->close_mailbox();
	
	//if (!SCRIPT_DEBUG)
		echo('Done');
	
	function checkMail($email, $subject, $body, $filename)
	{
		global $arr;
		global $echo;
		global $logGB;
		global $DOCUMENT_ROOT;
	
		// Перебираем правила
		foreach ($arr as $value)
		{
			$email = strtolower($email);
			$subject = strtolower($subject);
			$vemail = strtolower($value['From']);
			$vsubject = strtolower($value['Subject']);
			
			echo('$value[\'From\']: '.$value['From'].'<br>');
			echo('$email: '.$email.'<br>');
			echo('$value[\'Subject\']: '.$value['Subject'].'<br>');
			echo('$subject: '.$subject.'<br><br>');
			// Если поле "От" совпадает
			if ($vemail == $email)
			{
				// Если поле "Тема" совпадает
				if (!strlen($vsubject) or strpos($subject, $vsubject) !== false) {
					echo("\"От кого\" и \"Тема\" совпадают. Смотрим дальше.<br>");
					$body = strtolower($body);
					$text = strtolower($value['Text']);
					if (strlen($text))
						$tmpText = substr($body, strpos($body, $text), strlen($text));
					else
						$tmpText = '';
					if (!strlen($text) or $text == $tmpText)
					{
						if (!strlen($text))
							echo('Проверки по тексту нет.<br>');
						elseif ($text == $tmpText)
							echo('Проверка по тексту прошла успешно.<br>');
						
						echo('Письмо подходит! Проверяем имя вложения.<br>
						$value["TextInFilename"]: '.$value["TextInFilename"].'<br>
						$filename: '.$filename.'<br>');
						$lfilename = strtolower($filename);
						$vlfilename = strtolower($value['TextInFilename']);
						$pos = strrpos($filename, ".");
						$ext = strtolower(substr($filename, $pos+1, strlen($filename)));
						$sourceFile = $DOCUMENT_ROOT.'/files/tmp/'.$filename;
						if ($ext == 'rar') // Архив или нет?
						{
							// Если архив и обработка архивов запрещена, то просто перемещаем и уведомляем о ручной обработке
							var_dump($sourceFile);
							echo('<br>');
							if (strpos($filename, '_') == 8)
								$filename = substr($filename, 9, strlen($filename));
							$outFile = $DOCUMENT_ROOT.$value['Path'].$filename;
							echo('<br>');
							echo('Это '.$ext.' архив. Я не могу их обрабатывать. Распакуйте вручную.<br>
							Пытаюсь сохранить в '.$outFile.'<br>');
							if (copy($sourceFile, $outFile)) { // Если rename() вместо copy(), то придется отключить unlink()
								echo('Перемещение прошло успешно.<br>');
								$echo = ob_get_contents();
								echo($logGB->write('bad', $email, $echo));
								echo('<br>');
							} else {
								echo('Я не смог переместить =(<br>');
								$echo = ob_get_contents();
								echo($logGB->write('bad', $email, $echo));
								echo('<br>');
							}
							return;
						} elseif ($ext == 'zip' || $ext == 'xls' || $ext == 'xlsx') {
							if (strpos($lfilename, $vlfilename) !== false)
							{
								// Если файл совпадает, то перемещаем в нужное место
								echo('Перемещаем файл!!!<br>');
								var_dump($sourceFile);
								echo('<br>');
								if (strlen($value['NewFilename']))
									$filename = $value['NewFilename'];
								$outFile = $DOCUMENT_ROOT.$value['Path'].$filename;
								var_dump($outFile);
								echo('<br>');
								if (copy($sourceFile, $outFile)) { // Если rename() вместо copy(), то придется отключить unlink()
									echo('Перемещение прошло успешно.<br>');
									$echo = ob_get_contents();
									echo($logGB->write('good', $email, $echo));
									echo('<br>');
								} else {
									echo('Я не смог переместить =(<br>');
									$echo = ob_get_contents();
									echo($logGB->write('bad', $email, $echo));
									echo('<br>');
								}
								return;
							} else {
								echo('Совпадений в имени не найдено.<br><br>');
								// При отсутствии файла в части вложения, $lfilename может быть равен ~ "a1b2c3d4_"
								if (substr($lfilename, -1) != "_") { // Если последний символ в имени файла не является "_"
									$echo = ob_get_contents();
									echo($logGB->write('bad', $email, $echo));
									echo('<br><br>');
								}
							}
						} else {
							echo("Неизвестный формат $ext.");
							if (substr($lfilename, -1) != "_") { // Если последний символ в имени файла не является "_"
								$echo = ob_get_contents();
								echo($logGB->write('bad', $email, $echo));
								echo('<br>');
							}
						}
					} else {
						echo("Проверка по тексту провалилась.<br>");
						$echo = ob_get_contents();
						echo($logGB->write('bad', $email, $echo));
						echo('<br>');
					}
				} else {
					echo("Проверка по теме провалилась.<br>");
					$echo = ob_get_contents();
					echo($logGB->write('bad', $email, $echo));
					echo('<br>');
					
				}
			} else {
				echo("Правило не подошло.<br>");
			}
		}
		return 0;
	}
	
	function getAttachments($imap, $mailNum, $part, $partNum) {
		$attachments = array();
	 
		if (isset($part->parts)) {
			foreach ($part->parts as $key => $subpart) {
				if($partNum != "") {
					$newPartNum = $partNum . "." . ($key + 1);
				}
				else {
					$newPartNum = ($key+1);
				}
				$result = getAttachments($imap, $mailNum, $subpart,
					$newPartNum);
				if (count($result) != 0) {
					 array_push($attachments, $result);
				 }
			}
		}
		else if (isset($part->disposition)) {
			if ($part->disposition == "ATTACHMENT") {
				$partStruct = imap_bodystruct($imap, $mailNum,
					$partNum);
				$attachmentDetails = array(
					"name"    => $part->dparameters[0]->value,
					"partNum" => $partNum,
					"enc"     => $partStruct->encoding
				);
				return $attachmentDetails;
			}
		}
	 
		return $attachments;
	}
	
	function getBody($uid, $imap) {
		$body = get_part($imap, $uid, "TEXT/HTML");
		// if HTML body is empty, try getting text body
		if ($body == "") {
			$body = get_part($imap, $uid, "TEXT/PLAIN");
		}
		return $body;
	}
	 
	function get_part($imap, $uid, $mimetype, $structure = false, $partNumber = false) {
		if (!$structure) {
			   $structure = imap_fetchstructure($imap, $uid, FT_UID);
		}
		if ($structure) {
			if ($mimetype == get_mime_type($structure)) {
				if (!$partNumber) {
					$partNumber = 1;
				}
				$text = imap_fetchbody($imap, $uid, $partNumber, FT_UID);
				switch ($structure->encoding) {
					case 3: return imap_base64($text);
					case 4: return imap_qprint($text);
					default: return $text;
			   }
		   }
	 
			// multipart
			if ($structure->type == 1) {
				foreach ($structure->parts as $index => $subStruct) {
					$prefix = "";
					if ($partNumber) {
						$prefix = $partNumber . ".";
					}
					$data = get_part($imap, $uid, $mimetype, $subStruct, $prefix . ($index + 1));
					if ($data) {
						return $data;
					}
				}
			}
		}
		return false;
	}
	 
	function get_mime_type($structure) {
		$primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
	 
		if ($structure->subtype) {
		   return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
		}
		return "TEXT/PLAIN";
	}
?>
