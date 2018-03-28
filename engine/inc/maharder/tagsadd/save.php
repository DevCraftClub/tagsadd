<?php

//	===============================
//	Настройки модуля | сохраняем
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

include_once ENGINE_DIR . '/classes/parse.class.php';
$parse = new ParseFilter();

echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, $adminlink.'&do=settings' => 'Настройки', '' => "Сохранение настроек") );

$save_con = $_REQUEST['save'];
$handler = fopen(ENGINE_DIR . '/data/'.$codename.'.php', "w");
	
fwrite($handler, "<?PHP\n//	===============================\n//	Конфигурация модуля\n//	===============================\n//	Автор: Maxim Harder\n//	Сайт: https://maxim-harder.de\n//	Телеграм: http://t.me/MaHarder\n//	===============================\n//	Меняем, если скрипт неверно сохраняет\n//	===============================\n\n\$tagsconf = array (");
foreach ($save_con as $name => $value) {
	if($name == "adminmailtitle" || $name == "adminmail" || $name == "usermailtitle" || $name == "usermail" || $name == "usermailtitle2" || $name == "usermail2" || $name == "usermailtitle3" || $name == "usermail3")
		$value = $parse->BB_Parse( $value, false );
	fwrite($handler, "\t'{$name}' => \"{$value}\",\n");
}
fwrite($handler, ");\n?>");
fclose($handler);
	
clear_cache();

messageOut("Настройки сохранены", "Чтобы проверить - сохранились ли они - перейдите на страницу с настройками.", array($adminlink.'&do=settings' => "Настройки", $adminlink.'&do=list' => "Список новостей", $adminlink => "Главная",));
