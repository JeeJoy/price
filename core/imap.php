<?php
	class receiveMail
	{
		var $server='';
		var $username='';
		var $password='';
		
		var $marubox='';                    
		
		var $email='';            
		
		function receiveMail($username,$password,$EmailAddress,$mailserver='localhost',$servertype='pop',$port='110',$ssl = false) //Constructure
		{
			if($servertype=='imap')
			{
				$sslStr = ($ssl) ? "ssl/" : "";
				if($port=='') $port='143'; 
				//$strConnect='{'.$mailserver.':'.$port. '}INBOX'; 
				//$strConnect='{'.$mailserver.':'.$port.'/imap/'.$sslStr.'novalidate-cert}INBOX';
				$strConnect='{'.$mailserver.':'.$port.'/imap/'.$sslStr.'notls}INBOX';
			}
			else
			{
				$strConnect='{'.$mailserver.':'.$port. '/pop3'.($ssl ? "/ssl" : "").'}INBOX'; 
			}
			$this->server            =    $strConnect;
			$this->username            =    $username;
			$this->password            =    $password;
			$this->email            =    $EmailAddress;
		}
		function connect() //Connect To the Mail Box
		{
			$source = $this->marubox=imap_open($this->server,$this->username,$this->password);
			
			if(!$this->marubox)
			{
				echo "Error: Connecting to mail server";
				exit;
			}
			
			return $source;
		}
		function getHeaders($mid) // Get Header info
		{
			if(!$this->marubox)
				return false;

			$mail_header=imap_header($this->marubox,$mid);
			$sender=$mail_header->from[0];
			$sender_replyto=$mail_header->reply_to[0];
			if(strtolower($sender->mailbox)!='mailer-daemon' && strtolower($sender->mailbox)!='postmaster')
			{
				$mail_details=array(
						'from'=>strtolower($sender->mailbox).'@'.$sender->host,
						'fromName'=>$sender->personal,
						'toOth'=>strtolower($sender_replyto->mailbox).'@'.$sender_replyto->host,
						'toNameOth'=>$sender_replyto->personal,
						'subject'=>$mail_header->subject,
						'to'=>strtolower($mail_header->toaddress)
					);
			}
			return $mail_details;
		}
		function get_mime_type(&$structure) //Get Mime type Internal Private Use
		{ 
			$primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER"); 
			
			if($structure->subtype) { 
				return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype; 
			} 
			return "TEXT/PLAIN"; 
		} 
		function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) //Get Part Of Message Internal Private Use
		{ 
			if(!$structure) { 
				$structure = imap_fetchstructure($stream, $msg_number); 
			} 
			if($structure) { 
				if($mime_type == $this->get_mime_type($structure))
				{ 
					if(!$part_number) 
					{ 
						$part_number = "1"; 
					} 
					$text = imap_fetchbody($stream, $msg_number, $part_number); 
					if($structure->encoding == 3) 
					{ 
						return imap_base64($text); 
					} 
					else if($structure->encoding == 4) 
					{ 
						return imap_qprint($text); 
					} 
					else
					{ 
						return $text; 
					} 
				} 
				if($structure->type == 1) /* multipart */ 
				{ 
					while(list($index, $sub_structure) = each($structure->parts))
					{ 
						if($part_number)
						{ 
							$prefix = $part_number . '.'; 
						} 
						$data = $this->get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1)); 
						if($data)
						{ 
							return $data; 
						} 
					} 
				} 
			} 
			return false; 
		} 
		function getTotalMails() //Get Total Number off Unread Email In Mailbox
		{
			if(!$this->marubox)
				return false;

			//$headers=imap_headers($this->marubox);
			//return count($headers);
			return imap_num_msg($this->marubox);
		}
		function GetAttach($getonlyname = true, $mid = '', $rename = false, $path = '') // Get Atteced File from Mail
		{
			global $DOCUMENT_ROOT;
			
			$path = $DOCUMENT_ROOT.'/files/tmp/';
			
			if(!$this->marubox)
				return false;

			$struckture = imap_fetchstructure($this->marubox,$mid);
			//$ar="";
			$ar = array();
			$partNum = 0;
			$i = 0;
			if($struckture->parts)
			{
				foreach($struckture->parts as $key => $value)
				{
					/*if ($partNum != $part) {
						$partNum++;
						continue;
					}*/
					$enc=$struckture->parts[$key]->encoding;
					if($struckture->parts[$key]->ifdparameters)
					{
						$name=$struckture->parts[$key]->dparameters[0]->value;
						//echo('<br>Закодированное имя: ');
						//var_dump($name);
						$newName = '';
						if (substr($name, 0, 2) == '=?' and substr($name, -2) == '?=') {
							$arrName = substr($name, 1, strlen($name)-2); // Удаляем "=" в начале и конце
							//echo('<br>Удаляем в начале и конце "=": ');
							//var_dump($arrName);
							
							$arrName = preg_replace('/[\s]{1,}/', ' ', $arrName);
							//echo('<br>Удаляем двойные пробелы: ');
							//var_dump($arrName);
							
							$arrName = explode("= =", $arrName); // Разбиваем в массив по разделителю "= ="
							//echo('<br>Разбиваем по "= =": ');
							//var_dump($arrName);
							
							// Разбиваем строки на подмассивы по разделителю "?"
							for ($j=0; $j < count($arrName); $j++)
								$arrName[$j] = explode("?", $arrName[$j]);
							//echo('<br>Разбиваем на подмассивы по "?": ');
							//var_dump($arrName);
							
							// Декодируем массив и собираем его обратно
							//echo('<br>Собираем имя.');
							for ($j=0; $j < count($arrName); $j++) {
								$arrName[$j][3] = ($arrName[$j][2] == 'Q' ? quoted_printable_decode($arrName[$j][3]) : base64_decode($arrName[$j][3]));
								if 		(strtolower($arrName[$j][1]) == strtolower('windows-1251')) $arrName[$j][3] = iconv('cp1251', 	'utf-8', $arrName[$j][3]);
								elseif 	(strtolower($arrName[$j][1]) == strtolower('KOI8-R')) 		$arrName[$j][3] = iconv('KOI8-R', 	'utf-8', $arrName[$j][3]);
								elseif 	(strtolower($arrName[$j][1]) != strtolower('utf-8')) 		$arrName[$j][3] = iconv('', 			'utf-8', $arrName[$j][3]);
								$newName .= $arrName[$j][3];
								//echo("<br>Часть имени #$j: ");
								//var_dump($arrName[$j][3]);
							}
						} else {
							$newName = $name;
						}
						if ($rename)
							$newName = substr(md5(uniqid()), 0, 8).'_'.$newName;
						//echo('<br>Новое имя: ');
						//var_dump($newName);
						/*$name = explode("?", $name);
						//var_dump($name);
						if (count($name) > 1) {
							$name[3] = base64_decode($name[3]);
							if ($name[1] != 'utf-8')
								$name[0] = iconv($name[1], 'utf-8', $name[3]);
							else
								$name[0] = $name[3];
							if (count($name) > 5) {
								$name[7] = base64_decode($name[7]);
								if ($name[5] != 'utf-8')
									$name[0] .= iconv($name[5], 'utf-8', $name[7]);
								else
									$name[0] .= $name[7];
							}
						}
						if ($rename)
							$name[0] = substr(md5(uniqid()), 0, 8).'_'.$name[0];*/
						//var_dump($name);
						//echo('<br>');
						if (!$getonlyname) {
							$message = imap_fetchbody($this->marubox,$mid,$key+1);
							if ($enc == 0)
								$message = imap_8bit($message);
							if ($enc == 1)
								$message = imap_8bit ($message);
							if ($enc == 2)
								$message = imap_binary ($message);
							if ($enc == 3)
								$message = imap_base64 ($message); 
							if ($enc == 4)
								$message = quoted_printable_decode($message);
							if ($enc == 5)
								$message = $message;
							//$fp=fopen($path.$name[0],"w");
							$fp=fopen($path.$newName,"w");
							fwrite($fp,$message);
							fclose($fp);
						}
						//$ar=$ar.$name[0].",";
						//$ar[] = $name[0];
						$ar[$i]['name'] = $newName;
						$ar[$i]['size'] = filesize($path.$newName);
						$i++;
					}
					// Support for embedded attachments starts here
					if($struckture->parts[$key]->parts)
					{
						foreach($struckture->parts[$key]->parts as $keyb => $valueb)
						{
							$enc=$struckture->parts[$key]->parts[$keyb]->encoding;
							if($struckture->parts[$key]->parts[$keyb]->ifdparameters)
							{
								$name=$struckture->parts[$key]->parts[$keyb]->dparameters[0]->value;
								//echo('<br>Закодированное имя: ');
								//var_dump($name);
								$newName = '';
								if (substr($name, 0, 2) == '=?' and substr($name, -2) == '?=') {
									$arrName = substr($name, 1, strlen($name)-2); // Удаляем "=" в начале и конце
									//echo('<br>Удаляем в начале и конце "=": ');
									//var_dump($arrName);
									
									$arrName = preg_replace('/[\s]{1,}/', ' ', $arrName);
									//echo('<br>Удаляем двойные пробелы: ');
									//var_dump($arrName);
									
									$arrName = explode("= =", $arrName); // Разбиваем в массив по разделителю "= ="
									//echo('<br>Разбиваем по "= =": ');
									//var_dump($arrName);
									
									// Разбиваем строки на подмассивы по разделителю "?"
									for ($j=0; $j < count($arrName); $j++)
										$arrName[$j] = explode("?", $arrName[$j]);
									//echo('<br>Разбиваем на подмассивы по "?": ');
									//var_dump($arrName);
									
									// Декодируем массив и собираем его обратно
									//echo('<br>Собираем имя.');
									for ($j=0; $j < count($arrName); $j++) {
										$arrName[$j][3] = ($arrName[$j][2] == 'Q' ? quoted_printable_decode($arrName[$j][3]) : base64_decode($arrName[$j][3]));
										if 		(strtolower($arrName[$j][1]) == strtolower('windows-1251')) $arrName[$j][3] = iconv('cp1251', 	'utf-8', $arrName[$j][3]);
										elseif 	(strtolower($arrName[$j][1]) == strtolower('KOI8-R')) 		$arrName[$j][3] = iconv('KOI8-R', 	'utf-8', $arrName[$j][3]);
										elseif 	(strtolower($arrName[$j][1]) != strtolower('utf-8')) 		$arrName[$j][3] = iconv('', 			'utf-8', $arrName[$j][3]);
										$newName .= $arrName[$j][3];
										//echo("<br>Часть имени #$j: ");
										//var_dump($arrName[$j][3]);
									}
								} else {
									$newName = $name;
								}
								if ($rename)
									$newName = substr(md5(uniqid()), 0, 8).'_'.$newName;
								//echo('<br>Новое имя: ');
								//var_dump($newName);
								/*$name = explode("?", $name);
								//var_dump($name);
								//echo('<br>');
								if (count($name) > 1) {
									$name[3] = base64_decode($name[3]);
									if ($name[1] != 'utf-8')
										$name[0] = iconv($name[1], 'utf-8', $name[3]);
									else
										$name[0] = $name[3];
									if (count($name) > 5) {
										$name[7] = base64_decode($name[7]);
										if ($name[5] != 'utf-8')
											$name[0] .= iconv($name[5], 'utf-8', $name[7]);
										else
											$name[0] .= $name[7];
									}
								}
								if ($rename)
									$name[0] = substr(md5(uniqid()), 0, 8).'_'.$name[0];*/
								//var_dump($name);
								//echo('<br>');
								$partnro = ($key+1).".".($keyb+1);
								if (!$getonlyname) {
									$message = imap_fetchbody($this->marubox,$mid,$partnro);
									if ($enc == 0)
										   $message = imap_8bit($message);
									if ($enc == 1)
										   $message = imap_8bit ($message);
									if ($enc == 2)
										   $message = imap_binary ($message);
									if ($enc == 3)
										   $message = imap_base64 ($message);
									if ($enc == 4)
										   $message = quoted_printable_decode($message);
									if ($enc == 5)
										   $message = $message;
									//$fp=fopen($path.$name[0],"w");
									$fp=fopen($path.$newName,"w");
									fwrite($fp,$message);
									fclose($fp);
								}
								//$ar=$ar.$name[0].",";
								//$ar[] = $name[0];
								$ar[$i]['name'] = $newName;
								$ar[$i]['size'] = filesize($path.$newName);
								$i++;
							}
						}
					}                
				}
			}
			//$ar=substr($ar,0,(strlen($ar)-1));
			return $ar;
		}
		function getBody($mid) // Get Message Body
		{
			if(!$this->marubox)
				return false;

			$body = $this->get_part($this->marubox, $mid, "TEXT/HTML");
			if ($body == "")
				$body = $this->get_part($this->marubox, $mid, "TEXT/PLAIN");
			if ($body == "") { 
				return "";
			}
			return $body;
		}
		function deleteMails($mid) // Delete That Mail
		{
			if(!$this->marubox)
				return false;
		
			imap_delete($this->marubox,$mid);
		}
		function close_mailbox() //Close Mail Box
		{
			if(!$this->marubox)
				return false;

			imap_close($this->marubox,CL_EXPUNGE);
		}
	} 
?>
