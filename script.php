<?php
	ini_set("max_execution_time", "60");
	//include_once "pclzip.lib.php";
	include_once "imap.php";
	include_once "core/func.php";
	//header("Content-Type: text/html; charset=koi8-r");
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
  </head>

  <body>

<?php
	//include_once "core/config.php";
	
	//ini_set('display_errors', 1);
  	//error_reporting(E_ALL);
	
	$arr = loadRules();
	var_dump($arr);
	echo('<br><br>');
	
	// Коннектимся
	//$imap = new receiveMail('address', 'password', 'address', 'server', 'protocol', 'port', ssl true/false);
	$imap = new receiveMail('address', 'password', 'address', 'server', 'protocol', 'port', ssl true/false);
	$source = $imap->connect();
	$num = $imap->getTotalMails();
	$uid = imap_uid($source, $num);
	//$structure = imap_fetchstructure($source, $num);
	//$attachments = getAttachments($source, $num, $structure, "");
	
	// Смотрим письмо
	//echo('<table border="1">');
	for ($i = $num; $i > ($num - 10); $i--) {
		echo('<b>Смотрим письмо</b><br>');
		$header = imap_header($source, $i);
		$fromInfo = $header->from[0];
		$replyInfo = $header->reply_to[0];
		$email = $fromInfo->mailbox . "@" . $fromInfo->host;
		$subject = explode("?", $header->subject);
		//$text = $arr[2] == 'Q' ? quoted_printable_decode($text) : base64_decode($text);
		$subject[3] = base64_decode($subject[3]);
		if ($subject[1] != 'utf-8')
			$subject[3] = iconv('', 'utf-8', $subject[3]);
		$uid = imap_uid ($source, $i);
		$body = getBody($uid, $source);
		$structure = imap_fetchstructure($source, $i);
		//$attachments = getAttachments($source, $i, $structure, '');
		//$attachments = $imap->GetAttach(false, $i,'./testfiles/');
		//$attachments = explode("?", $attachments);
		//$attachments[3] = base64_decode($attachments[3]);
		//if ($attachments[1] != 'utf-8')
			//$attachments[3] = iconv('', 'utf-8', $attachments[3]);
		echo('От: '.$email.'<br>Тема: '.$subject[3].'<br>');
		
		// Скачиваем вложения
		echo('Скачиваю вложения<br>');
		$filenames = $imap->GetAttach(false, $i, true); // Получаем имена вложений
		$part = 0;
		foreach ($filenames as $filename)
		{
			echo('Смотрю вложение '.$filename.'<br>');
			$pos = strrpos($filename, ".");
			$ext = substr($filename, $pos, strlen($filename));
			if ($ext == '.rar' or $ext == '.zip') // Архив или нет?
			{
				echo('Это архив! С архивами, пока, ничего не делаем.<br>');
				//$tmpName = md5(uniqid());
				// Скачиваем
				//$imap->GetAttach(false,$i,$_SERVER['DOCUMENT_ROOT'].'/testfiles/tmp/',$tmpName.$ext);
				// Распаковываем
				//echo('Распаковываю.<br>');
				//$archive = new PclZip($_SERVER['DOCUMENT_ROOT'].'/testfiles/tmp/',$tmpName.$ext);
				//if ($archive->extract() == 0) {
				//	echo("Error : ".$archive->errorInfo(true));
				//}else{
				//	echo('ok');
				//}
				//$zip = new ZipArchive;
				//$zip->open('./testfiles/tmp/'.$tmpName.$ext, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
				//$zip->extractTo('./testfiles/tmp/'.$tmpName.'/');
				//$zip->close();
			} else {
				echo('Это не архив. Проверим файл по БД.<br>');
				// Проверяю вложение по БД
				checkMail($email, $subject[3], $body, $filename);
			}
			unlink($_SERVER['DOCUMENT_ROOT'].'/testfiles/tmp/'.$filename);
			$part++;
			echo('<br>');
		}
		echo('<br>');
		
		/*echo('<tr><td>
			От: '. $email .'<br>
			Тема64: '. $header->subject .'<br>
			Тема: '. $subject[3] .'<br>
			Cod: '. $subject[1] .'<br>
			$body: ');
		//var_dump($body);
		echo('<br>$attachments: ');
		var_dump($attachments);
		echo('<br>That same: '. (($attachments == 'LRService(Руб)_09.12.2013.zip') ? 'Yes' : 'No'));
		echo('</td></tr>');*/
	}
	//echo('<table>');
	
	function checkMail($email, $subject, $body, $filename)
	{
		global $arr;
	
		// Перебираем правила
		foreach ($arr as $value)
		{
			// Если поля "От" и "Тема" совпадают
			if (strtolower($value['From']) == strtolower($email) and strtolower($value['Subject']) == strtolower($subject))
			{
				echo("\"От кого\" и \"Тема\" совпадают. Смотрим дальше.<br>");
				$body = strtolower($body);
				var_dump($body);
				echo('<br>');
				$text = strtolower($value['Text']);
				var_dump($text);
				echo('<br>');
				if (strlen($text))
					$tmpText = substr($body, strpos($body, $text), strlen($text));
				else
					$tmpText = '';
				var_dump($tmpText);
				echo('<br>');
				if (!strlen($text) or $text == $tmpText)
				{
					if (!strlen($text))
						echo('Проверки по тексту нет.<br>');
					if ($text == $tmpText)
						echo('Проверка по тексту прошла успешно.<br>');
					echo('$value[\'From\']: '.$value['From'].'<br>');
					echo('$email: '.$email.'<br>');
					echo('$value[\'Subject\']: '.$value['Subject'].'<br>');
					echo('$subject: '.$subject.'<br><br>');
				
					echo("Письмо подходит! Проверяем имя вложения.<br>");
					if (strpos($value['TextInFilename'], substr($filename, 9, strlen($filename))) !== false)
					{
						// Если файл совпадает, то перемещаем в нужное место
						echo('Перемещаем файл!!!<br>');
						$sourceFile = $_SERVER['DOCUMENT_ROOT'].'/testfiles/tmp/'.$filename;
						var_dump($sourceFile);
						echo('<br>');
						var_dump($_SERVER['DOCUMENT_ROOT'].$value['Path'].$filename);
						echo('<br>');
						if (strlen($value['NewFilename']))
							$filename = $value['NewFilename'];
						if (copy($sourceFile, $_SERVER['DOCUMENT_ROOT'].$value['Path'].$filename))
							echo('Перемещение прошло успешно.<br>');
						else
							echo('Я не смог переместить =(<br>');
					}
					/*foreach ($names as $name)
					{
						$pos = strrpos($name, ".");
						$ext = substr($name, $pos, strlen($name));
						if ($ext == '.rar' or $ext == '.zip') // Архив или нет?
						{
							$tmpName = md5(uniqid());
							// Скачиваем
							//$imap->GetAttach(false,$i,'./testfiles/tmp/',$tmpName.$ext);
						}
					}*/
				} else {
					echo("Проверка по тексту провалилась.<br>");
				}
			} else {
				echo("Правило не подошло.<br>");
			}
		}
		return 0;
		//echo('<br><br>');
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*echo('$num: '.$num);
	echo('<br>$uid: '.$uid.'<br>');
	var_dump($attachments);
	echo "<br>Attachments:<br>";
	$i = 0;
	foreach ($attachments as $attachment) {
		$i++;
		echo '<a href="mail.php?func=' . $func . '&folder=' . $folder . '&uid=' . $uid .
			'&part=' . $attachment["partNum"] . '&enc=' . $attachment["enc"] . '">' . $i . ': ' .
			$attachment["name"] . "</a><br>";
	}*/
	
	$imap->close_mailbox();
	
	/*// Список писем с меткой просмотра письма
	$imap = imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}', 'gorelov@web-agency.ru', 'Gorpass123');
	$numMessages = imap_num_msg($imap);
	for ($i = $numMessages; $i > ($numMessages - 20); $i--) {
		$header = imap_header($imap, $i);
		
		$fromInfo = $header->from[0];
		$replyInfo = $header->reply_to[0];
	 
		$details = array(
			"fromAddr" => (isset($fromInfo->mailbox) && isset($fromInfo->host))
				? $fromInfo->mailbox . "@" . $fromInfo->host : "",
			"fromName" => (isset($fromInfo->personal))
				? $fromInfo->personal : "",
			"replyAddr" => (isset($replyInfo->mailbox) && isset($replyInfo->host))
				? $replyInfo->mailbox . "@" . $replyInfo->host : "",
			"replyName" => (isset($replyTo->personal))
				? $replyto->personal : "",
			"subject" => (isset($header->subject))
				? $header->subject : "",
			"udate" => (isset($header->udate))
				? $header->udate : ""
		);
		
		$uid = imap_uid($imap, $i);
		$class = ($header->Unseen == "U") ? "Не прочитано" : "Прочитано";
	 
		echo ('<ul class="'.$class.'">
			<li><strong>From:</strong>'.$details["fromName"].' '.$details["fromAddr"].'</li>
			<li><strong>Subject:</strong>'.$details["subject"].'</li>
			<li><a href="mail.php?folder='.$folder.'&uid='.$uid.'&func=read">'.$class.'</a> | ');
			echo ('<a href="mail.php?folder='.$folder.'&uid='.$uid.'&func=delete">Удалить</a></li>
		</ul>');
	}
	imap_close($imap);*/
	
	// Отображаем содержимое письма
	/*$imap = imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}', 'gorelov@web-agency.ru', 'Gorpass123');
	$numMessages = imap_num_msg($imap);
	$uid = imap_uid ($imap, $numMessages);
	$body = getBody($uid, $imap);
	echo('$body: ');
	var_dump($body);
	
	// Выводим список писем
	for ($i = $numMessages; $i > ($numMessages - 20); $i--) {
		$header = imap_header($imap, $i);
	 
		$fromInfo = $header->from[0];
		$replyInfo = $header->reply_to[0];
	 
		$details = array(
			"fromAddr" => (isset($fromInfo->mailbox) && isset($fromInfo->host))
				? $fromInfo->mailbox . "@" . $fromInfo->host : "",
			"fromName" => (isset($fromInfo->personal))
				? $fromInfo->personal : "",
			"replyAddr" => (isset($replyInfo->mailbox) && isset($replyInfo->host))
				? $replyInfo->mailbox . "@" . $replyInfo->host : "",
			"replyName" => (isset($replyTo->personal))
				? $replyto->personal : "",
			"subject" => (isset($header->subject))
				? $header->subject : "",
			"udate" => (isset($header->udate))
				? $header->udate : ""
		);
	 
		$uid = imap_uid($imap, $i);
	 
		echo "<ul>";
		echo "<li><strong>From:</strong>" . $details["fromName"];
		echo " " . $details["fromAddr"] . "</li>";
		echo "<li><strong>Subject:</strong> " . $details["subject"] . "</li>";
		echo '<li><a href="mail.php?folder=' . $folder . '&uid=' . $uid . '&func=read">Read</a>';
		echo " | ";
		echo '<a href="mail.php?folder=' . $folder . '&uid=' . $uid . '&func=delete">Delete</a></li>';
		echo "</ul>";
	}
	imap_close($imap);*/
	
	/*// Выводим список папок
	$mail = imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}', 'gorelov@web-agency.ru', 'Gorpass123');
	$folders = imap_list($mail, "{imap.gmail.com:993/imap/ssl}", "*");
	echo "<ul>";
	foreach ($folders as $folder) {
		$folder = str_replace("{imap.gmail.com:993/imap/ssl}", "", imap_utf7_decode($folder));
		echo '<li><a href="mail.php?folder=' . $folder . '&func=view">' . $folder . '</a></li>';
	}
	echo "</ul>";
	imap_close($mail);*/
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	/*// открываем IMAP-соединение
	$mail = imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}', 'gorelov@web-agency.ru', 'Gorpass123');
	// или открываем POP3-соединение
	//$mail = imap_open('{mail.server.com:110/pop3}', 'username', 'password');
	// берем список всех почтовых заголовков
	$headers = imap_headers($mail);
	// берем объект заголовка для последнего сообщения в почтовом ящике
	$last = imap_num_msg($mail);
	$header = imap_header($mail, $last);
	// выбираем тело для того же сообщения
	$body = imap_body($mail, $last);
	// закрываем соединение
	imap_close($mail);
	
	echo('$mail: ');
	var_dump($mail);
	echo('<br><br>$headers: ');
	var_dump($headers);
	echo('<br><br>$last: ');
	var_dump($last);
	echo('<br><br>$header: ');
	var_dump($header);
	echo('<br><br>$body: ');
	var_dump($body);*/
?>

  </body>
</html>
