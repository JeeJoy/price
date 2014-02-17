<?php
	/*------------------------------------------------------------------------+
	 * ID сообщений обработки письма:                                         |
	 *  false - письмо обработано                                             |
	 *  2 - соединение с почтой не установлено                                |
	 *  3 - правило отключено                                                 |
	 *  4 - не нашел письмо                                                   |
	 *------------------------------------------------------------------------+
	 * ID сообщений обработки вложения:                                       |
	 *  5 - не удалось создать директорию для прайса                          |
	 *  6 - не смог переместить прайс                                         |
	 *  7 - архив не найден                                                   |
	 *  8 - вложение не является архивом, однако пыталось обработаться как он |
	 *  9 - не удалось обработать архив: не открылся                          |
	 * 10 - папка с распакованным архивом не найдена                          |
	 * 11 - не удалось создать папку для создания ZIP-архива                  |
	 * 12 - вложение скачать не удалось                                       |
	 * 13 - не удалось получить список вложений или письмо без них            |
	 * 14 - письмо с вложением слишком старое (существует больше 24 часов)    |
	 *------------------------------------------------------------------------+
	 */

	//ini_set('display_errors', 1);
	//ini_set('display_startup_errors', 1);
	//error_reporting(E_ALL);
	ini_set("max_execution_time", "60");
	ini_set("upload_max_filesize", "32M");
	ini_set("post_max_size", "32M");
	include_once "config.php";
	include_once "imap.php";
	
	$logGB = new LogGoodBad();
	$logOutput = 'Запуск скрипта';
	
	if (isset($_GET['cron'])) {
		$logOutput .= "<br>Автоматическая обработка";
		startProcess(true);
	} elseif (!empty($_SESSION['login'])) { // Проверяем авторизацию
		if (isset($_REQUEST['sdo'])) {
			$sdo = $_REQUEST['sdo'];
			if ($sdo == 'getr') // Если sdo=getr - получаем список правил
				echo(json_encode(loadRule()));
			elseif ($sdo == 'process') { // Если sdo=process - обрабатываем конкретное правило по id
				if (isset($_REQUEST['id'])) {
					$logOutput .= "<br>Ручная обработка";
					startProcess();
				}
				else
					echo("wrong id");
			}
		}
		//else
			//
	} else {
		echo("Not authorized");
	}
	
	function startProcess($cron = false) {
		if (!$cron)
			processRule($_REQUEST['id']); //processRule($_POST['id']); //outputModule($_POST['id']);
		else {
			$rules = loadRule();
			//var_dump($rules);
			for ($i=0; $i < count($rules); $i++) { 
				processRule($rules[$i]['ID']); //outputModule($rules[$i]['ID']);
			}
		}
	}

	function outputModule($source, $i, $id, $error) {
		global $logOutput;
		$arr = Array();
		$output = '';
		$nowDate = time();
		switch ($error) {
			case false:
				$header = imap_header($source, (int)$i);
				
				//$output = 'done';
				$output = '';
				//$arr['Update'] = $nowDate;
				//$arr['MailDate'] = $header->udate;
				$arr['Update'] = $header->udate;
				break;
			case '2':
				//$output = "Error: can't make a connection";
				$output = "Error: не могу установить соединение с почтой";
				break;
			case '3':
				$output = 'off';
				break;
			case '4':
				//$output = 'Warning: mail not found';
				$output = 'Warning: письма не найдены';
				break;
			case '5':
				//$output = "Error: can't to create dir for price";
				$output = "Error: не могу создать папку для прайса";
				break;
			case '6':
				//$output = "Error: can't to move a price";
				$output = "Error: не могу переместить прайс";
				break;
			case '7':
				//$output = "Error: archive not found";
				$output = "Error: архив не найден";
				break;
			case '8':
				//$output = "Error: attachment isn't an archive";
				$output = "Error: вложение не является архивом";
				break;
			case '9':
				//$output = "Error: can't to process an archive: can't be opened";
				$output = "Error: не могу обработать архив: не открывается";
				break;
			case '10':
				//$output = "Error: dir with files from an archive not found";
				$output = "Error: папка с файлами из архива не найдена";
				break;
			case '11':
				//$output = "Error: can't to create a dir for new a ZIP";
				$output = "Error: не могу создать папку для нового ZIP-архива";
				break;
			case '12':
				//$output = "Error: can't to download an attachment";
				$output = "Error: не могу скачать вложение";
				break;
			case '13':
				//$output = "Error: can't to get attachment list or mail without it";
				$output = "Error: не могу получить список вложений или они отсутствуют в письме";
				break;
			case '14':
				//$output = "Error: new mails not income more than 24 hours";
				//$output = "Error: новые письма не поступали уже больше 24 часов";
				//$output = "Warning: обновлений не было";
				$output = "";
				break;
			default:
				//$output = "Warning: unknown result - $error";
				$output = "Warning: неизвестный результат - $error";
				$arr['Update'] = $header->udate; //$arr['Update'] = $nowDate;
				break;
		}
		$arr['ID'] = $id;
		$arr['Error'] = $error;
		$arr['Comment'] = $output;
		$json = json_encode($arr);
		ruleJSON($json);
		
		//echo($output);
		echo($json);
	}
	
	function processRule($id) {
		global $logGB;
		global $logOutput;
		$source = '';
		
		// Подгружаем нужное правило
		$rule = loadRule($id);
		//var_dump($rule);
		// Если правило отключено - выходим
		if (isset($rule['Status']) and $rule['Status'] == 'off') {
			outputModule('', 0, $rule['ID'], 3); //return 3;
			exit();
		}
		
		/*$logOutput2 = $logOutput;
		$logOutput .= "<br>Сканируем папку INBOX";
		connecting($rule, $id, 'INBOX');
		// Повторно подгружаем нужное правило, т.к. оно могло измениться
		$rule = loadRule($id);
		$logOutput = $logOutput2;
		$logOutput .= "<br>Сканируем папку Spam";
		connecting($rule, $id, 'Spam');*/
		
		// Коннектимся
		$imap = new receiveMail('address', 'password', 'address', 'server', 'protocol', 'port', 'ssl true/false', 'folder');
		$source = $imap->connect();
		$logOutput .= "<br>Устанавливаем соединение с почтовым ящиком";
		if ($source) {
			$logOutput .= "<br>Соединение установлено";
			// Определяем кол-во писем
			$num = $imap->getTotalMails();
			$logOutput .= "<br>Найдено $num писем";
			
			// Ищем письмо
			$logOutput .= "<br>Начинаю поиск письма";
			for ($i = $num; ; $i--) {
				if (!$i) {
					$logOutput .= "<br>Письма кончились!";
					break; // Если письма кончились, то заканчиваем
				}
				
				// Проверяем письмо
				//$logOutput .= "<br>Проверяю письмо $i";
				if ($file = compareData($imap, $source, $i, $rule)) {
					$header = imap_header($source, $i);
					$fromInfo = $header->from[0];
					$replyInfo = $header->reply_to[0];
					$email = $fromInfo->mailbox . "@" . $fromInfo->host;
					if (!is_numeric($file)) {
						//echo("Нашел\n");
						
						//$arr = Array();
						//$error = false;
						//$comment = ''; 
						$logOutput .= "<br>Начинаем обработку вложения";
						$error = processFile($imap, $i, $file, $rule);
						/*if ($error = processFile($imap, $num, $file, $rule)) {
							$error = true;
							//$comment = "не смог обработать";
							//echo($file." ".$comment."\n");
						} else {
							//echo($file." обработал\n");
						}
						
						$arr['ID'] = $id;
						$arr['Update'] = date('Y-m-d H:i:s');
						$arr['Error'] = $error;
						//$arr['Comment'] = $comment;
						$json = json_encode($arr);
						ruleJSON($json);*/
						$logOutput .= "<br>Записываем результат работы";
						outputModule($source, $i, $rule['ID'], $error); //return $error;
						$logOutput .= "<br>Закрываем IMAP-соединение<br>";
						$imap->close_mailbox();
						
						$logGB->write('good', $email, $logOutput);
						if (isset($_GET['debug'])) echo("<br>".$logOutput);
						
						exit();
					} else {
						$logOutput .= "<br>Функция вернула ошибку!";
						outputModule($source, 0, $rule['ID'], $file); //return $file;
						$logOutput .= "<br>Закрываем IMAP-соединение<br>";
						$imap->close_mailbox();
						
						$logGB->write('bad', $email, $logOutput);
						if (isset($_GET['debug'])) echo("<br>".$logOutput);
						
						exit();
					}
				}
			}
			//echo("Не нашел");
			
			outputModule($source, 0, $rule['ID'], 4); //return 4;
			$logOutput .= "<br>Закрываем IMAP-соединение<br>";
			$imap->close_mailbox();
			
			$logGB->write('bad', "rule-$id-not-found", $logOutput);
			if (isset($_GET['debug'])) echo("<br>".$logOutput);
		} else { // Если не смог установить соединение
			//echo "Error: Connecting to mail server";
			outputModule($source, 0, $rule['ID'], 2); //return 2;
			
			$logGB->write('bad', 'disconnect', $logOutput);
			if (isset($_GET['debug'])) echo("<br>".$logOutput);
		}
		exit();
	}

	/*function connecting($rule, $id, $folder) {
		
	}*/
	
	function compareData($imap, $source, $num, $rule) {
		global $DOCUMENT_ROOT;
		global $logOutput;
		
		// Получаем заголовок письма
		$header = imap_header($source, (int)$num);
		// Получаем адрес отправителя
		$email = $header->from[0];
		$email = $email->mailbox . "@" . $email->host;
		
		//$logOutput .= '<br>$email: '.$email;
		//$fromaddress = $header->fromaddress;
		//$logOutput .= '<br>$fromaddress: '.$fromaddress;
		//$fromaddress = str_replace("\"", "", $fromaddress);
		//$fromaddress = str_replace(" ", "", $fromaddress);
		//$logOutput .= '<br>$fromaddress: '.$fromaddress;
		//if (isset($_GET['debug'])) var_dump($header);
		
		// Проверяем адрес
		//if ($email == $rule['From'] or $fromaddress == $rule['From']) {
		if ($email == $rule['From']) {
			$logOutput .= "<br>Письмо найдено";
			$logOutput .= "<br>Отправитель: $email";
			$logOutput .= '<br>Сверяю "'.$email.'" с "'.$rule['From'].'"';
			$logOutput .= "<br>Адреса совпадают";
			// Получаем и проверяем тему
			$subject = strtolower(decodeHeader($header->subject));
			$ruleSubject = strtolower($rule['Subject']);
			$logOutput .= '<br>Сверяю темы: "'.$subject.'" с "'.$ruleSubject.'"';
			if (!strlen($ruleSubject) or strpos($subject, $ruleSubject) !== false) {
				$logOutput .= "<br>Темы совпадают";
				// Если есть проверка по тексту, получаем текст письма
				$text = strtolower($rule['Text']);
				$logOutput .= '<br>Ищу "'.$text.'" в письме';
				if (strlen($text)) {
					$uid = imap_uid ($source, $num);
					$body = strtolower(getBody($uid, $source));
					$body = substr($body, strpos($body, $text), strlen($text));
				}
				
				if (!strlen($text) or $text == $body) {
					$logOutput .= "<br>Текст найден";
					// Получаем имена вложений
					$logOutput .= "<br>Получаю имена вложений";
					$attachments = $imap->GetAttach(true, $num, false);
					if (!count($attachments)) {
						$logOutput .= "<br>Вложений нет!";
						return 13;
					}
					
					$logOutput .= "<br>Начинаю перебирать вложения";
					for ($i=0; $i < count($attachments); $i++) {
						$filename = strtolower($attachments[$i]['name']);
						$ruleFilename = strtolower($rule['TextInFilename']);
						
						// Проверяем имя вложения
						$logOutput .= "<br>Сверяю имена: \"$filename\" с \"$ruleFilename\"";
						if (strpos($filename, $ruleFilename) !== false) {
							$logOutput .= "<br>Имена совпадают";
							$dateLastMail = 0;
							//if (isset($rule['MailDate']) and $rule['MailDate'])
							//	$dateLastMail = (int)$rule['MailDate'];
							if (isset($rule['Update']) and $rule['Update'])
								$dateLastMail = (int)$rule['Update'];
							$date = (int)$header->udate;
							$diff = $date - $dateLastMail;
							$logOutput .= "<br>Дата последнего обновления: ".date('d-m-Y H:i:s', $dateLastMail);
							$logOutput .= "<br>Дата в письме: ".date('d-m-Y H:i:s', $date);
							$logOutput .= "<br>Разница в днях: ".floor($diff/86400)." дн.";
							//if (isset($_GET['cron']) and $diff <= 0) {
							if ($diff >= 0) {
								$logOutput .= "<br>Вложение подходит";
								return $filename;
							} else {
								$logOutput .= "<br>Вложение слишком старое";
								return 14;
							}
						}
					}
				}
			} else {
				$logOutput .= "<br>Темы не совпадают. Продолжаю поиск";
			}
		}
		
		return false;
	}

	function processFile($imap, $num, $filename, $rule) {
		global $DOCUMENT_ROOT;
		global $logOutput;
		
		// Скачиваем вложения
		$logOutput .= "<br>Скачиваем";
		$attachment = $imap->GetAttach(false, $num, true, 0, $filename);
		if (!count($attachment)) {
			$logOutput .= "<br>Скачать не удалось";
			return 12;
		}
		// Определяем имя и месторасположение файла
		$filename = $attachment[0]['name'];
		$pos = strrpos($filename, ".");
		$ext = substr($filename, $pos+1, strlen($filename));
		$sourceFile = $DOCUMENT_ROOT.'/files/tmp/'.$filename;
		$logOutput .= "<br>Имя вложения: $filename";
		$logOutput .= "<br>Месторасположение: $sourceFile";
		
		if ($ext != 'rar' or ($sourceFile = rarToZip($sourceFile) and (!is_numeric($sourceFile)))) {
			if (strlen($rule['NewFilename'])) {
				$logOutput .= "<br>Переименовываем в ".$rule['NewFilename'];
				$filename = $rule['NewFilename'];
			}
			$outFile = $DOCUMENT_ROOT.$rule['Path'].$filename;
			// Если не смог переместить - false
			//if (!(file_exists($DOCUMENT_ROOT.$rule['Path']) and copy($sourceFile, $outFile)))
			$logOutput .= "<br>Проверяем наличие директории ".$DOCUMENT_ROOT.$rule['Path'];
			if (makeDir($DOCUMENT_ROOT.$rule['Path'])) {
				$logOutput .= "<br>Директория найдена. Пробуем переместить";
				if (!copy($sourceFile, $outFile)) {
					$logOutput .= "<br>Перемещение не удалось";
					return 6;
				}
			} else {
				$logOutput .= "<br>Не удалось создать директорию";
				return 5;
			}
			$logOutput .= "<br>Перемещение удалось";
		} else {
			return $sourceFile;
		}
		if (file_exists($sourceFile)) unlink($sourceFile);
		return false;
	}
	
	function decodeHeader($header) {
		$output = '';
		if (substr($header, 0, 2) == '=?' and substr($header, -2) == '?=') {
			$header = substr($header, 1, strlen($header)-2); // Удаляем "=" в начале и конце
			$header = preg_replace('/[\s]{1,}/', ' ', $header); // Удаляем двойные пробелы
			$header = explode("= =", $header); // Разбиваем в массив по разделителю "= ="
			
			// Разбиваем строки на подмассивы по разделителю "?"
			for ($j=0; $j < count($header); $j++)
				$header[$j] = explode("?", $header[$j]);
			
			// Декодируем массив и собираем его обратно
			for ($j=0; $j < count($header); $j++) {
				$header[$j][3] = ($header[$j][2] == 'Q' ? quoted_printable_decode($header[$j][3]) : base64_decode($header[$j][3]));
				if 		(strtolower($header[$j][1]) == strtolower('windows-1251')) $header[$j][3] = iconv('cp1251', 	'utf-8', $header[$j][3]);
				elseif 	(strtolower($header[$j][1]) == strtolower('KOI8-R')) 		$header[$j][3] = iconv('KOI8-R', 	'utf-8', $header[$j][3]);
				elseif 	(strtolower($header[$j][1]) != strtolower('utf-8')) 		$header[$j][3] = iconv('', 			'utf-8', $header[$j][3]);
				$output .= $header[$j][3];
			}
		} else {
			$output = $header;
		}
		
		return $output;
	}

	function rarToZip($file) {
		global $DOCUMENT_ROOT;
		global $logOutput;
		$dir = '';
		
		$logOutput .= "<br>Пробуем перепаковать архив";
		if (file_exists($file)) {
			$fullName = substr($file, strrpos($file, '/')+1, strlen($file)); // Отсекаем путь
			$fileName = substr($fullName, 0, strrpos($fullName, '.')); // Отсекаем расширение
			$ext = substr($fullName, strrpos($fullName, '.')+1, strlen($fullName)); // Отсекаем имя
			$logOutput .= "<br>Имя архива: $fullName";
			// Если RAR архив, то обрабатываем
			if (strtolower($ext) == 'rar') {
				$logOutput .= "<br>Это действительно архив!";
				// Пробуем открыть архив
				if (($rar_file = rar_open("$file")) !== false) {
					// Получаем список содержимого
					$entries = rar_list($rar_file);
					
					// Распаковываем
					$dir = $DOCUMENT_ROOT."/files/tmp/$fileName/";
					$logOutput .= "<br>Получаем список файлов";
					$logOutput .= "<br>Распаковываем в $dir";
					foreach ($entries as $entry)
						$entry->extract($dir);
					
					rar_close($rar_file);
					$logOutput .= "<br>Закрываем архив";
				} else {
					 //echo("Невозможно открыть $file<br><br>");
					 $logOutput .= "<br>Не удалось открыть архив!";
					 return 9;
				}
			} else {
				//echo("Файл $file не является RAR архивом.<br>");
				$logOutput .= "<br>Вложение не является архивом! Почему я здесь?!";
				return 8;
			}
		} else {
			//echo("Архив $file не найден.<br>");
			$logOutput .= "<br>Архив не найден!";
			return 7;
		}
		
		$logOutput .= "<br>Начинаем сборку ZIP-архива";
		if (file_exists($dir)) {
			// Запоминаем имя папки без уникального ID
			$zipName = substr($dir, 0, strlen($dir)-1); // Отсекаем слэш в конце
			$zipName = substr($zipName, strrpos($zipName, '/')+1, strlen($zipName)); // Отсекаем путь с уникальным ID в начале
			$logOutput .= "<br>Имя нового архива: $zipName";
			//echo('<br><br>Проверяем: ');
			//var_dump($zipName);
			//echo('<br><br>');
			if (makeDir($DOCUMENT_ROOT."/files/tmp/zip/")) {
				$zipFile = $DOCUMENT_ROOT."/files/tmp/zip/$zipName.zip";
				// Создаем новый ZIP архив
				$zip = new ZipArchive();
				$zip->open($zipFile, ZIPARCHIVE::CREATE);
				
				$packStatus = zipPacking($zip, $dir, $dir);
				
				$zip->close();
				
				// Удаляем распакованную папку от RAR архива
				removeDirectory($dir);
				// Удаляем RAR архив
				if (file_exists($file)) unlink($file);
				// Если не получилось перепаковать - удаляем ZIP архив
				if (!$packStatus and file_exists($zipFile)) {
					$logOutput .= "<br>Не удалось создать новый архив!";
					unlink($zipFile);
				}
			} else {
				$logOutput .= "<br>Директория ".$DOCUMENT_ROOT."/files/tmp/zip/ не найдена!";
				return 11;
			}
		} else {
			//echo("Каталог $dir не найден.<br>");
			$logOutput .= "<br>Директория $dir не найдена!";
			return 10;
		}
		
		return $zipFile;
	}

	function zipPacking($zip, $base, $dir) {
		if (substr($dir, -1) != '/') $dir .= '/';
		foreach(scandir($dir, 0) as $afile) {
			if ($afile != "." && $afile != "..") {
				if (is_dir($dir.$afile)) {
					if (!zipPacking($zip, $base, $dir."$afile"))
						return false;
				} elseif (!$zip->addFile($dir."/$afile", substr($dir, strlen($base), strlen($dir)).$afile)) {
					return false;
				}
			}
		}
		return true;
	}

	function removeDirectory($dir) {
		if ($objs = glob($dir."/*")) {
			foreach($objs as $obj)
				is_dir($obj) ? removeDirectory($obj) : unlink($obj);
		}
		rmdir($dir);
	}
?>
