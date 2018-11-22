<?php
	require_once 'config.php';
	function connect_to_db ($db_host, $db_user, $db_pass, $db_name, $db_charset)
    {
        $mysql_connect = mysql_connect($db_host, $db_user, $db_pass);
        if ($mysql_connect) {
            $select_db = mysql_select_db($db_name);
            if ($select_db) {
                $q = "SET NAMES $db_charset";
                $set_names = mysql_query($q);
                return true;
            } else {
                $mysql_error = "На сервере не найдено базы данных с именем ".$db_name;
                exit ($mysql_error());
            }
        } else {
            $mysql_error = "Ошибка связи с сервером базы данных";
            exit ($mysql_error());
        }
        return false;
    }
	$db = connect_to_db ($db_host, $db_user, $db_pass, $db_name, $db_charset);
	header ('Content-Type: text/html; charset=UTF-8');
	function post_data_is_valid ()
	{
		if (isset($_POST['from'])) {
			$from = trim($_POST['from']);
			$form = strtolower($from);
			if (!preg_match('/^[a-z0-9_\.-]+@[a-z0-9_\.-]+\.[a-z\.]{2,6}$/', $from)) {
				return '<script>alert(\'Неправильно заполнено поле "E-mail отправителя"\')</script>';
			}
		} else
			return false;

		if (isset($_POST['name'])) {
			$name = trim($_POST['name']);
			if (!preg_match('/^.{3,30}$/', $name)) {
				return '<script>alert(\'Неправильно заполнено поле "Имя отправителя"\')</script>';
			}
		} else
			return false;

		if (isset($_POST['subject'])) {
			$subject = trim($_POST['subject']);
			if (!preg_match('/^.{3,128}$/', $subject)) {
				return '<script>alert(\'Неправильно заполнено поле "Тема письма"\')</script>';
			}
		} else
			return false;


		if (isset($_POST['to'])) {
			$to = trim($_POST['to']);
			$to = strtolower($to);
			if (!preg_match_all('/ *[a-z0-9_\.-]{2,30}@[a-z0-9_\.-]{2,20}\.[a-z\.]{2,6} */', $to, $mail_list)) {
				return '<script>alert(\'В списке не найдено ни одного корректного адреса E-mail"\')</script>';
			} else {
				$to = array();
				foreach ($mail_list[0] as $key => $value) {
					$to[$key] = $value;
					$to[$key] = trim($to[$key]);
				}
			}
		} else
			return false;

		if (isset($_POST['message_type'])) {
			$message_type = trim($_POST['message_type']);
			if ($message_type == 'text')
				$content_type = 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
			else
				if ($message_type == 'html')
					$content_type = 'Content-Type: text/html; charset=UTF-8' . "\r\n";

		} else
			return false;

		if (isset($_POST['message'])) {
			$message = trim($_POST['message']);
			$message = stripslashes($message);
			if (strlen($message) < 3 || strlen($message) > 4096) {
				return '<script>alert(\'Неправильно заполнено поле "Текст письма"\')</script>';
			}
		} else
			return false;
	
		return  array (

			'from' => $from,
			'name' => $name,
			'subject' => $subject,
			'message' => $message,
			'to' => $to,
			'content_type' => $content_type
		);
	}
	function add_mail_to_db ($email)
	{
		$email = trim($email);
		$email = htmlspecialchars($email);
		$email = mysql_real_escape_string($email);
		$last_used = date("Y-m-d H:i:s");
		$q = mysql_query ("SELECT `id` FROM `e-mails` WHERE `e-mail`='$email' LIMIT 1");
		if (mysql_num_rows($q) > 0)
			$q = "UPDATE `e-mails` SET `last_used`='$last_used' WHERE `e-mail`='$email' LIMIT 1";
		else
			$q = "INSERT INTO `e-mails` (`e-mail`, `last_used`) VALUES ('$email', '$last_used')";
		$result = mysql_query($q);
		if (!$result)
			return false;
		return true;
	}
	function sent_post_from_fake_mail ($from, $name, $subject, $message, $to, $content_type)
	{
		$sent_status = mail ($to, $subject, $message, $content_type . "From: $name <$from>\r\n");
		if ($sent_status !== false)
			return true;
		else
			return false;
	}

	function input_value ($field_name)
	{
		if (isset($_POST[$field_name]))
			return ' value="'.$_POST[$field_name].'"';
	}

	function textarea_value ()
	{
		if (isset($_POST['message']))
			return stripslashes($_POST['message']);
	}
	
	function textarea_mail_values ()
	{
		if (isset($_POST['to']))
			return trim($_POST['to']);
	}	

	function radio_checked ($value)
	{
		if (isset($_POST['message_type']) && $_POST['message_type'] == $value)
			return ' checked';
		else
			if ($value == 'text')
				return ' checked';
	}
	$alert = '';
	$textarea_mail_list = '';
	if (isset($_GET['do']) && $_GET['do'] == 'sent')
		if (post_data_is_valid ()) {
			if (isset($_COOKIE['sent']) && $_COOKIE['sent'] == 'true')
				$alert = '<script>alert(\'Рассылать письма можно только 1 раз в 5 минут\')</script>';
			else {
				$sent_data = post_data_is_valid ();
				if (!is_array($sent_data) && $sent_data !== false)
					$alert = $sent_data;
				else {
					$success = 0;
					$failed = 0;
					$sent_data['to'] = array_unique($sent_data['to']);
					foreach ($sent_data['to'] as $to) {
							add_mail_to_db ($to);
						$sent_status = sent_post_from_fake_mail ($sent_data['from'], $sent_data['name'], $sent_data['subject'], $sent_data['message'], $to, $sent_data['content_type']);
									
						if (!$sent_status)
							$failed++;
						else {
							$success++;
						}
					}
					if ($success === 0)
						$alert = '<script>alert(\'Рассылка завершилась неудачей. Попробуйте повторить попытку через 30 минут\')</script>';	
					else {
						setcookie('sent', 'true', time() + 300);
						$alert = '<script>alert(\'Рассылка законечена. Успешно отправлено: '.$success.' писем. Завершились неудачей: '.$failed.' рассылок\')</script>';
					}
				}
			}
		}
?>
