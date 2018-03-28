<?php

//	===============================
//	Настройки модуля | файл настроек
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, '' => 'Настройки') );
include_once ENGINE_DIR . '/classes/parse.class.php';
$parse = new ParseFilter();

$boxList = [
	'start' => [
		'name' => "Основные функции",
		'icon' => "home icon",
	],
	'list' => [
		'name' => "Спидок с тегами",
		'icon' => "list alternate icon",
	],
	'templates' => [
		'name' => "Шаблоны",
		'icon' => "pencil alternate icon",
	], 
	'author' => [
		'name' => "Автор",
		'icon' => "user circle icon",
	],

];

boxes($boxList);
echo "<form class=\"ui form\" method=\"POST\" action=\"".$adminlink."&do=save\">";

$blockstart = [
	segRow("Включить модуль?", "Включает и выключает модуль", addCheckbox('onof', ($tagsconf['onof'] == 1) ? true : false), 'onof'),
	segRow("Отсылать уведомление при добавлении тегов?", "Если нет, то письма с уведомлением не будут отсылаться, но будут показываться в админке", addCheckbox('send', ($tagsconf['send'] == 1) ? true : false), 'send'),
	segRow("Укажите поле с настройками уведомлений", "Пользователь сам выбирает о чём его будет уведомлять система. Если включена опция выше, то при изменении тегов (добавлении в новость и добавлении о предложении в базу данных) отправитель получит письмо.", addInput('userinform', $tagsconf['userinform'], "Укажите поле с настройками уведомлений"), 'userinform'),
	segRow("Укажите имя админа", "Укажите имя, которому будут приходить уведомления", addInput('admin', $tagsconf['admin'], "Укажите имя админа"), 'admin'),
	segRow("Имя отправителя", "Укажите имя, откоторого будет исходить сообщения", addInput('master', $tagsconf['master'], "Имя отправителя"), 'master'),
	segRow("Название кнопки", "Как будет выводится кнопка в новости? К примеру: добавить", addInput('button', $tagsconf['button'], "Название кнопки"), 'button'),
];
$blocklist = [
	segRow("Кол-во новостей в списке?", "Чтобы отобразить все новости. По умолчанию берётся с глобальных настроек.", addInput('listcount', $tagsconf['listcount'], "Кол-во новостей в списке?"), 'listcount'),
	segRow("Кол-во страниц в пагинации?", "Это поможет в настройке пагинации. По умолчанию выдаёт по 5 страниц на выбор.", addInput('limit', $tagsconf['limit'], "Кол-во страниц в пагинации?"), 'limit'),
	segRow("Сортировка списка", "Порядок", addSelect('listsort', array('ASC' => "По возрастанию", 'DESC' => "По убыванию"), "Сортировка списка", $tagsconf['listsort']), 'listsort'),
	segRow("Сортировка списка", "Сортировка по полю<br><u>Пример по возрастанию:</u><br>Если по статусу: Добавлено, Не одобрено, Отредактированое, Новое", addSelect('listsort2', array('id' => "По ID", 'date' => "По дате", 'status' => "По статусу"), "Сортировка списка", $tagsconf['listsort2']), 'listsort2'),
	segRow("Куда добавлять теги?", "При нажатии на \"Добавить в новость\" скрипт должен знать куда добавлять теги в обход редактуры", addSelect('fast', array('tags' => "Добавляет сразу в теги", 'xfield' => "Добавляет в доп. поле"), "Куда добавлять теги?", $tagsconf['fast']), 'fast'),
	segRow("Название поля", "Если было выбрано в выборе выше \"Доп. поле\" или \"Поле в базе данных\" в <b>dle_post</b>, то вписываем его сюда", addInput('field', $tagsconf['field'], "Название поля"), 'field'),
    segRow("Доп. поле - гиперссылка?", "Если доп. поле является гиперссылкой, то скрипт будет добавлять значения в нужную таблицу", addCheckbox('xflink', ($tagsconf['xflink'] == 1) ? true : false), 'xflink'),
];
$blocktemplates = [
	segRow("Заголовок для письма админу", "Следующие теги будут заменены на данные:<ul><li><b>%title%</b> - название новости</li><li><b>%user%</b> - имя (ник) отправителя</li></ul>", addInput('adminmailtitle', $parse->decodeBBCodes( $tagsconf['adminmailtitle'], false ), "Заголовок для письма админу"), 'adminmailtitle'),
	segRow('Шаблон письма админу', '<b>Разрешены BB-Code и HTML-Code</b><br>Следующие теги будут заменены на данные:<ul><li><b>%link%</b> - Ссылка на новость</li><li><b>%tags%</b> - выводит предлагаемые теги к новости</li><li><b>%user%</b> - имя (ник) отправителя</li><li><b>%title%</b> - название новости</li><li><b>%adminlink%</b> - ссылка на редактирование тегов</li></ul>', addTextarea('adminmail', $parse->decodeBBCodes( $tagsconf['adminmail'], false ), 'Шаблон письма админу'), 'adminmail'),
	segRow("Заголовок для письма отправителю", "Следующие теги будут заменены на данные:<ul><li><b>%title%</b> - название новости</li></ul>", addInput('usermailtitle', $parse->decodeBBCodes( $tagsconf['usermailtitle'], false ), "Заголовок для письма отправителю"), 'usermailtitle'),
	segRow('Шаблон письма отправителю', '<b>Разрешены BB-Code и HTML-Code</b><br>Следующие теги будут заменены на данные:<ul><li><b>%link%</b> - Ссылка на новость</li><li><b>%tags%</b> - выводит предлагаемые теги к новости</li><li><b>%user%</b> - имя (ник) отправителя</li><li><b>%title%</b> - название новости</li></ul>', addTextarea('usermail', $parse->decodeBBCodes( $tagsconf['usermail'], false ), 'Шаблон письма отправителю'), 'usermail'),
	segRow("Заголовок для письма отправителю при добавлении тегов в новость", "Следующие теги будут заменены на данные:<ul><li><b>%title%</b> - название новости</li><li><b>%user%</b> - имя (ник) отправителя</li></ul>", addInput('usermailtitle2', $parse->decodeBBCodes( $tagsconf['usermailtitle2'], false ), "Заголовок для письма отправителю при добавлении тегов в новость"), 'usermailtitle2'),
	segRow('Шаблон для письма отправителю при добавлении тегов в новость', '<b>Разрешены BB-Code и HTML-Code</b><br>Следующие теги будут заменены на данные:<ul><li><b>%link%</b> - Ссылка на новость</li><li><b>%tags%</b> - выводит предлагаемые теги к новости</li><li><b>%user%</b> - имя (ник) отправителя</li><li><b>%title%</b> - название новости</li></ul>', addTextarea('usermail2', $parse->decodeBBCodes( $tagsconf['usermail2'], false ), 'Шаблон для письма отправителю при добавлении тегов в новость'), 'usermail2'),
	segRow("Заголовок для письма отправителю при отказе в добавлении", "Следующие теги будут заменены на данные:<ul><li><b>%title%</b> - название новости</li><li><b>%user%</b> - имя (ник) отправителя</li></ul>", addInput('usermailtitle3', $parse->decodeBBCodes( $tagsconf['usermailtitle3'], false ), "Заголовок для письма отправителю при отказе в добавлении"), 'usermailtitle3'),
	segRow('Шаблон для письма отправителю при отказе в добавлении', '<b>Разрешены BB-Code и HTML-Code</b><br>Следующие теги будут заменены на данные:<ul><li><b>%link%</b> - Ссылка на новость</li><li><b>%tags%</b> - выводит предлагаемые теги к новости</li><li><b>%user%</b> - имя (ник) отправителя</li><li><b>%title%</b> - название новости</li><li><b>%reason%</b> - название причины</li></ul>', addTextarea('usermail3', $parse->decodeBBCodes( $tagsconf['usermail3'], false ), 'Шаблон для письма отправителю при отказе в добавлении'), 'usermail3'),
];
$blockauthor = [
	segRow("Автор", "", author('name')),
	segRow("Связь", "", author('social')),
	segRow("Версия модуля", "", $version),
	segRow("Изменения", "", author('changes')),
];

segment('start', $blockstart, true);
segment('list', $blocklist);
segment('templates', $blocktemplates);
segment('author', $blockauthor);
saveButton();
echo "</form>";