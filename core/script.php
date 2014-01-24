<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
  	error_reporting(E_ALL);
	ini_set("max_execution_time", "180");
	ini_set("upload_max_filesize", "32M");
	ini_set("post_max_size", "32M");
	include_once "config.php";
	include_once "imap.php";
	
	$totalTime = new Timer();
	$echo = '';
	$logGB = new LogGoodBad();
	$timer = new Timer();
	$arr = loadRule();
	echo("<br>Time: Loading of rules ".$timer->get()." s");
	
	// Коннектимся
	$timer = new Timer();
	$imap = new receiveMail('address', 'password', 'address', 'server', 'protocol', 'port', 'ssl true/false');
	$source = $imap->connect();
	echo ("<br>Time: Connecting ".$timer->get()." s");
	$timer = new Timer();
	$num = $imap->getTotalMails();
	echo ("<br>Time: Getting a num of mails ".$timer->get()." s<br><br>");
	
	// Смотрим письмо
	for ($i = $num; $i > ($num - MAX_MAIL); $i--) {
		if (!$i) break; // Если письма кончились, то заканчиваем
		
		$timer = new Timer();
		$uid = imap_uid($source, $i);
		ob_start(); // Запускаем буферизацию вывода
		echo("<b>Смотрим письмо ".$i."</b><br>");
		$echo = '';
		$timer2 = new Timer();
		$header = imap_header($source, $i);
		echo ("Time: Getting a header ".$timer2->get()." s<br><br>");
		$fromInfo = $header->from[0];
		$replyInfo = $header->reply_to[0];
		$email = $fromInfo->mailbox . "@" . $fromInfo->host;
		
		// ----------------------------------------------------------------
		// Разбираем тему
		// ----------------------------------------------------------------
		$newSubject = '';
		if (substr($header->subject, 0, 2) == '=?' and substr($header->subject, -2) == '?=') {
			$subject = substr($header->subject, 1, strlen($header->subject)-2); // Удаляем "=" в начале и конце
			$subject = preg_replace('/[\s]{1,}/', ' ', $subject); // Удаляем двойные пробелы
			$subject = explode("= =", $subject); // Разбиваем в массив по разделителю "= ="
			
			// Разбиваем строки на подмассивы по разделителю "?"
			for ($j=0; $j < count($subject); $j++)
				$subject[$j] = explode("?", $subject[$j]);
			
			// Декодируем массив и собираем его обратно
			for ($j=0; $j < count($subject); $j++) {
				$subject[$j][3] = ($subject[$j][2] == 'Q' ? quoted_printable_decode($subject[$j][3]) : base64_decode($subject[$j][3]));
				if 		(strtolower($subject[$j][1]) == strtolower('windows-1251')) $subject[$j][3] = iconv('cp1251', 	'utf-8', $subject[$j][3]);
				elseif 	(strtolower($subject[$j][1]) == strtolower('KOI8-R')) 		$subject[$j][3] = iconv('KOI8-R', 	'utf-8', $subject[$j][3]);
				elseif 	(strtolower($subject[$j][1]) != strtolower('utf-8')) 		$subject[$j][3] = iconv('', 			'utf-8', $subject[$j][3]);
				$newSubject .= $subject[$j][3];
			}
		} else {
			$newSubject = $header->subject;
		}
		// ----------------------------------------------------------------
		
		$uid = imap_uid ($source, $i);
		$timer2 = new Timer();
		$body = getBody($uid, $source);
		echo ("Time: Getting a body ".$timer2->get()." s<br><br>");
		$structure = imap_fetchstructure($source, $i);
		echo('От: '.$email.'<br>Тема: '.$newSubject.'<br>');
		echo('UID: ');
		var_dump($uid);
		echo('<br><br>');
		
		// Скачиваем вложения
		echo('Скачиваю вложения<br>');
		$timer2 = new Timer();
		$filenames = $imap->GetAttach(false, $i, true); // Получаем имена вложений
		echo ("Time: Getting attachments ".$timer2->get()." s<br><br>");
		$part = 0;
		//foreach ($filenames as $filename)
		for ($j=0; $j < count($filenames); $j++) {
			$size = $filenames[$j]['size'];
			$filename = $filenames[$j]['name'];
			echo("Смотрю вложение $filename (".round($size/1024/1024, 2)." Мб)<br>");
			$pos = strrpos($filename, ".");
			$ext = substr($filename, $pos, strlen($filename));
			$timer2 = new Timer();
			checkMail($email, $newSubject, $body, $filename);
			echo ("Time: Checking of mail ".$timer2->get()." s<br><br>");
			$file = $DOCUMENT_ROOT.'/files/tmp/'.$filename;
			if (file_exists($file)) unlink($file);
			$part++;
			echo('<br>');
		}
		
		$echo = ob_get_contents();
		ob_end_clean(); // Останавливаем буферизацию вывода и очищаем буфер
		
		//if (SCRIPT_DEBUG)
			echo($echo);
		echo ("Time: Processing of mail ".$timer->get()." s<br><br>");
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
		foreach ($arr as $value) {
			$email = strtolower($email);
			$subject = strtolower($subject);
			$vemail = strtolower($value['From']);
			$vsubject = strtolower($value['Subject']);
			
			// Если поле "От" совпадает
			if ($vemail == $email) {
				// Если поле "Тема" совпадает
				if (!strlen($vsubject) or strpos($subject, $vsubject) !== false) {
					$body = strtolower($body);
					$text = strtolower($value['Text']);
					if (strlen($text))
						$tmpText = substr($body, strpos($body, $text), strlen($text));
					else
						$tmpText = '';
					if (!strlen($text) or $text == $tmpText)
					{
						$lfilename = strtolower($filename);
						$vlfilename = strtolower($value['TextInFilename']);
						$pos = strrpos($filename, ".");
						$ext = strtolower(substr($filename, $pos+1, strlen($filename)));
						$sourceFile = $DOCUMENT_ROOT.'/files/tmp/'.$filename;
						if ($ext == 'rar') // Архив или нет?
						{
							if (strpos($lfilename, $vlfilename) !== false)
							{
								if ($sourceFile = rarToZip($sourceFile)) {
									var_dump($sourceFile);
									echo('<br>');
									if (strlen($value['NewFilename']))
										$filename = $value['NewFilename'];
									$outFile = $DOCUMENT_ROOT.$value['Path'].$filename;
									var_dump($outFile);
									echo('<br>');
									makeDir($DOCUMENT_ROOT.$value['Path']);
									if (file_exists($DOCUMENT_ROOT.$value['Path'])
										and copy($sourceFile, $outFile)) { // Если rename() вместо copy(), то придется отключить unlink()
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
									// Удаляем архив, если создался
									if (file_exists($sourceFile))
										unlink($sourceFile);
								} else {
									echo("Архив $sourceFile перепаковать не удалось.<br>");
									$echo = ob_get_contents();
									echo($logGB->write('bad', $email, $echo));
									echo('<br>');
								}
							}
							/*// Если архив и обработка архивов запрещена, то просто перемещаем и уведомляем о ручной обработке
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
							}*/
							return;
						} elseif ($ext == 'zip' || $ext == 'xls' || $ext == 'xlsx') {
							if (strpos($lfilename, $vlfilename) !== false)
							{
								// Если файл совпадает, то перемещаем в нужное место
								//echo('Перемещаем файл!!!<br>');
								//var_dump($sourceFile);
								//echo('<br>');
								if (strlen($value['NewFilename']))
									$filename = $value['NewFilename'];
								$outFile = $DOCUMENT_ROOT.$value['Path'].$filename;
								var_dump($outFile);
								echo('<br>');
								makeDir($DOCUMENT_ROOT.$value['Path']);
								if (file_exists($DOCUMENT_ROOT.$value['Path'])
									and copy($sourceFile, $outFile)) { // Если rename() вместо copy(), то придется отключить unlink()
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
							echo("Неизвестный формат $ext.<br><br>");
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
				//echo("Правило не подошло.<br><br>");
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
	
	function removeDirectory($dir) {
		if ($objs = glob($dir."/*")) {
			foreach($objs as $obj)
				is_dir($obj) ? removeDirectory($obj) : unlink($obj);
		}
		rmdir($dir);
	}
	
	function rarToZip($file) {
		global $DOCUMENT_ROOT;
		$dir = '';
		
		if (file_exists($file)) {
			$fullName = substr($file, strrpos($file, '/')+1, strlen($file)); // Отсекаем путь
			$fileName = substr($fullName, 0, strrpos($fullName, '.')); // Отсекаем расширение
			$ext = substr($fullName, strrpos($fullName, '.')+1, strlen($fullName)); // Отсекаем имя
			// Если RAR архив, то обрабатываем
			if (strtolower($ext) == 'rar') {
				// Пробуем открыть архив
				if (($rar_file = rar_open("$file")) !== false) {
					// Получаем список содержимого
					$entries = rar_list($rar_file);
					
					// Распаковываем
					$dir = $DOCUMENT_ROOT."/files/tmp/$fileName/";
					foreach ($entries as $entry)
						$entry->extract($dir);
					
					rar_close($rar_file);
				} else {
					 echo("Невозможно открыть $file<br><br>");
					 return false;
				}
			} else {
				echo("Файл $file не является RAR архивом.<br>");
				return false;
			}
		} else {
			echo("Архив $file не найден.<br>");
			return false;
		}
		
		if (file_exists($dir)) {
			// Запоминаем имя папки без уникального ID
			$zipName = substr($dir, 0, strlen($dir)-1); // Отсекаем слэш в конце
			$zipName = substr($zipName, strrpos($zipName, '/')+1, strlen($zipName)); // Отсекаем путь с уникальным ID в начале
			echo('<br><br>Проверяем: ');
			var_dump($zipName);
			echo('<br><br>');
			$zipFile = $DOCUMENT_ROOT."/files/tmp/zip/$zipName.zip";
			// Создаем новый ZIP архив
			$zip = new ZipArchive();
			$zip->open($zipFile, ZIPARCHIVE::CREATE);
			
			foreach(scandir($dir, 0) as $file) {
				if ($file != "." && $file != "..") {
					if ($zip->addFile($dir."/$file", $file))
						echo("Файл $file перепакован.<br>");
					else {
						echo("Файл $file перепаковать не удалось.<br>");
						return false;
					}
				}
			}
			
			$zip->close();
			
			// Удаляем распакованную папку от RAR архива
			removeDirectory($dir);
		} else {
			echo("Каталог $dir не найден.<br>");
			return false;
		}
		
		return $zipFile;
	}
	
	echo ("<br><br>Total time: ".$totalTime->get()." s");
?>
