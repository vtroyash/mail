<?php require_once 'functions.php'; ?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Анонимная отправка писем с любого почтового ящика</title>
		<meta name="description" content="Онлайн-сервис для анонимной отправки писем с любого почтового ящика">
		<meta name="keywords" content="отправка писем, фальшивый ящик, анонимная отправка писем, фальшивая почта, подделка e-mail">
		<link rel="stylesheet" href="style.css">
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
		<script type="text/javascript">
			function diplay_hide (blockId) 
			{
				if ($(blockId).css('display') == 'none')
					$(blockId).animate({height: 'show'}, 500);
				else
					$(blockId).animate({height: 'hide'}, 500);
			}
		</script>	
	</head>
	<body>
		<a class="share" href="javascript:void(0)" onclick="diplay_hide('#share');">Код для сайта</a>
		<textarea id="share" style="display: none;" readonly><a href="http://<?=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']?>">Отправить письмо с поддельного E-mail адреса</a></textarea>
		<section id="wrap">
			<h1>Отправить письмо с любого адреса</h1>
			<form action="?do=sent" method="post">
				<input class="textbox" type="text" name="from"<?=input_value('from');?> placeholder="E-mail отправителя">
				<input class="textbox" type="text" name="name"<?=input_value('name');?> placeholder="Имя отправителя">
				<input class="textbox" type="text" name="subject"<?=input_value('subject');?> placeholder="Тема письма">
				<textarea name="to" placeholder="Список E-mail адресов для рассылки. Каждый адрес должен начинаться с новой строки."><?=textarea_mail_values();?></textarea>
				<span>Формат письма: </span>
				<input id="text_t" class="radiobox" type="radio" name="message_type" value="text"<?=radio_checked ('text');?>>
				<label for="text_t">Текст</label>
				<input id="html_t" class="radiobox" type="radio" name="message_type" value="html"<?=radio_checked ('html');?>>
				<label for="html_t">HTML</label>
				<textarea name="message" placeholder="Текст письма"><?=textarea_value();?></textarea>
				<button type="submit">Послать письмо</button>
			</form>
			<footer>
				<p>Сервис разрабатывался как программа-шутка исключительно для ознакомительных целей. Администрация сайта не несет никакой ответственности за дейстия пользователей.</p>		
			</footer>
			<?=$alert?>
		</section>
	</body>
</html>
