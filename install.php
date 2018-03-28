<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Origin: https://ui.sakuranight.net");

$codename = "tagsadd";

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname (__FILE__));
define('ENGINE_DIR', ROOT_DIR.'/engine');
define('INC_DIR', ENGINE_DIR.'/inc');

require_once ENGINE_DIR.'/classes/mysql.php';
require_once INC_DIR.'/include/functions.inc.php';
include ENGINE_DIR.'/data/dbconfig.php';
include ENGINE_DIR.'/data/config.php';
require_once (ENGINE_DIR . '/inc/maharder/assets/functions.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/version.php');

$check_db = new db;
$check_db->connect(DBUSER, DBPASS, DBNAME, DBHOST, false);
if( version_compare($check_db->mysql_version, '5.6.4', '<') ) {
    $storage_engine = "MyISAM";
} else $storage_engine = "InnoDB";
unset($check_db);

switch ($_GET['action']) {
    case 'install':
        try {
            $tableSchema = array();
            $tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_tagsadd";
            $tableSchema[] = "CREATE TABLE " . PREFIX . "_tagsadd (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
	        `news_id` INT(10) NOT NULL DEFAULT '0',
	        `user_id` INT(10) NULL DEFAULT '0',
	        `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	        `tags` VARCHAR(255) NULL DEFAULT NULL,
	        PRIMARY KEY (`id`),
	        INDEX `News` (`news_id`),
	        INDEX `Users` (`user_id`)
        ) COMMENT='{$name}' ENGINE={$storage_engine} DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci AUTO_INCREMENT=1";
            $tableSchema[] = "INSERT INTO " . PREFIX . "_admin_sections (name, title, descr, icon, allow_groups) VALUES ('{$codename}', '{$name} v{$version}', '{$descr}', '{$codename}.png', '1')";
            foreach ($tableSchema as $table) {
                $db->query($table);
            }
            $html = "Успешно установлено";
        } catch (Exception $e) {
            $fail = $e->getMessage();
            $html = "Произошла ошибка: {$fail}";
        }
        break;

    case 'update':
        try {
            $tableSchema = array();
            $tableSchema[] = "DELETE FROM " . PREFIX . "_admin_sections WHERE name = '{$codename}'";
            $tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_tagsadd";
            $tableSchema[] = "CREATE TABLE " . PREFIX . "_tagsadd (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
	        `news_id` INT(10) NOT NULL DEFAULT '0',
	        `user_id` INT(10) NULL DEFAULT '0',
	        `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	        `tags` VARCHAR(255) NULL DEFAULT NULL,
	        PRIMARY KEY (`id`),
	        INDEX `News` (`news_id`),
	        INDEX `Users` (`user_id`)
        ) COMMENT='{$name}' ENGINE={$storage_engine} DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci AUTO_INCREMENT=1";
            $tableSchema[] = "INSERT INTO " . PREFIX . "_admin_sections (name, title, descr, icon, allow_groups) VALUES ('{$codename}', '{$name} v{$version}', '{$descr}', '{$codename}.png', '1')";
            $oldEntries = $db->query("SELECT * FROM " . PREFIX . "_post_tags");

            while ($entry = $db->get_array($oldEntries)) {
                $username = $entry['username'];
                $user = $db->query("SELECT * FROM " . PREFIX . "_users WHERE name = '{$username}'");
                $userid = intval($user['user_id']);
                if (!$userid) $userid = 0;
                unset($user);
                $news = intval($entry['news_id']);
                $tags = $entry['tags'];
                $tags = explode(',', $tags);
                $btags = array();
                foreach ($tags as $tag) {
                    if ($tag != '' || !empty($tag)) $btags[] = $tag;
                }
                $tags = implode(',', $btags);
                unset($btags);
                $tableSchema[] = "INSERT INTO " . PREFIX . "_tagsadd (news_id, user_id, tags) VALUES ($newsid, $userid, '{$tags}')";
            }
            unset($oldEntries);
            $tableSchema[] = "DROP TABLE " . PREFIX . "_post_tags";
            $tableSchema[] = "INSERT INTO " . PREFIX . "_admin_sections (name, title, descr, icon, allow_groups) VALUES ('{$codename}', '{$name} v{$version}', '{$descr}', '{$codename}.png', '1')";
            foreach ($tableSchema as $table) {
                $db->query($table);
            }

            $html = "Успешно обновлено";
        } catch (Exception $e) {
            $fail = $e->getMessage();
            $html = "Произошла ошибка: {$fail}";
        }
        break;

    default:

        $html = <<<HTML
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta http-equiv="Access-Control-Allow-Credentials" content="True">
    <link href="https://ui.sakuranight.net/css/frame.css" rel="stylesheet">
    <link href="https://ui.sakuranight.net/css/prettify.css" rel="stylesheet">
    <link href="https://ui.sakuranight.net/css/installpage.css" rel="stylesheet">
    <title>TagsAdd, версия 2.0</title>
</head>

<body>
    <div class="ui container">
        <div class="ui equal width divided grid">
            <div class="stretched row">
                <div class="three wide column sticky">
                    <div class="ui vertical fluid tabular menu">
                        <a class="active item" data-tab="descr">Описание</a>
                        <a class="item" data-tab="install">Установка</a>
                        <a class="item" data-tab="update2">Обновление до 2.0</a>
                        <a class="item" data-tab="help">Поддержка</a>
                    </div>
                </div>
                <div class="column content">
                    <div class="ui segment active tab" data-tab="descr">
                        <h2 class="ui header">
                            <i class="fab fa-cloudversify"></i>
                            <div class="content">
                                TagsAdd, версия 2.0
                                <div class="sub header">Пользовательские теги</div>
                            </div>
                        </h2>
                        <p>
                            Модуль предназначен для добавления тегов пользователями с новости.<br>
                            <b>Ситуация:</b> Хорошая новость/статья, где полно нужной информации, однако автор указал всего-лишь пару тегов. Совестливый пользователь, который прочитав данный текст, решил предложить пару тегов по теме, чтобы другие пользователи
                            смогли проще ориентироваться о чём статья, и написал автору статьи. Но автор молчит и дела не делаются. Что делать?<br>
                            <b>Решение:</b> Установить сей модуль и дать системе регулировать предложениями.
                        </p>
                        <p>
                            В новой версии модуля был переписан весь код и функционал. Теперь не надо лезьт в файлы и дописывать строчки, чтобы всё работало. Не важно - версия DLE 10.x, 11.x или 12.x - всё будет работать как часы.<br> Всё подключается
                            одной строкой в файле полной новости.
                        </p>
                    </div>
                    <div class="ui segment tab" data-tab="install">
                        <h2 class="ui header">
                            <i class="fas fa-list-ol"></i>
                            <div class="content">
                                Установка
                                <div class="sub header">Обновляемая документация всегда <a href="http://help.maxim-harder.de/forum/26-polzovatelskie-tegi/" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                        <ol>
                            <li>Для установки достаточно закинуть в корень сайта все файлы и запустить <a href="{$_SERVER['PHP_SELF']}?action=install" target="_blank">этот скрипт  <i class="fas fa-external-link-alt"></i></a> (раз вы это читаете, значит вы молодец).</li>
                            <li>В админпанеле устанавливаем для пользователей доп. поле:
                                <ul>
                                    <li><strong>Название:</strong> любое</li>
                                    <li><strong>Описание:</strong> любое</li>
                                    <li><strong>Тип:</strong> список</li>
                                    <li><strong>Значение по умолчанию:</strong><br /> onsend|При отправке на проверку<br /> onadd|При добавлении в новость<br /> ondel|При отказе добавлять<br /> all|Уведомлять обо всём<br /> none|Не присылать уведомления</li>
                                    <li><strong>Добавить на страницу регистрации?:</strong> Да (на ваше усмотрение)</li>
                                    <li><strong>Поле может быть изменено пользователем?:</strong> Да</li>
                                    <li><strong>Сделать это поле личным?:</strong> Да</li>
                                </ul>
                            </li>
                            <li>В админпанеле настройте скрипт под себя.</li>
                            <li>Открываем файл шаблона полной новости (<strong>fullstory.tpl</strong>) и в любое место добавляем следующую строку:
                                <pre class="prettyprint">{include file="/engine/modules/maharder/tagsadd.php?newsid={news-id}&amp;focus=XXX"}</pre></li>
                            <li>Вместо <strong>XXX</strong> вписываем:
                                <ul>
                                    <li><strong>button</strong> - для вывода кнопки</li>
                                    <li><strong>modal</strong> - для вывода модального окна</li>
                                    <li><strong>functions</strong> - для вывода функций</li>
                                </ul>
                            </li>
                            <li>Ещё можно дописать параметр <strong>nameN</strong>. Так будут называться ключевые функции для окон и кнопок.</li>
                            <li>Внешний вид вего настраивается в 3ёх шаблонах, что находятся в папке <strong>ШАБЛОН/modules/tagsadd</strong>. Настраивайте под себя.<br /> <span style="text-decoration: underline;">Важный аспект в файле с модальным окном</span>:
                                если изменяете его, то помните, что форме нужны те-же названия полей и адрес исполнения.</li>
                            <li><strong>Поддерживаемые теги в шаблонах:</strong> button.tpl
                                <ul>
                                    <li><strong>{name}</strong> - по умолчанию tagsadd. Глобальное название кнопок и функций</li>
                                    <li><strong>{button}</strong> - текст кнопки. Указывается в настройках</li>
                                </ul>
                            </li>
                            <li><strong>Поддерживаемые теги в шаблонах:</strong> modal.tpl
                                <ul>
                                    <li><strong>{name}</strong> - по умолчанию tagsadd. Глобальное название кнопок и функций</li>
                                    <li><strong>{AJAX}</strong> - ссылка на папку site.ru/engine/ajax</li>
                                    <li><strong>{news-id}</strong> - ID Новости</li>
                                    <li><strong>{user-id}</strong> - ID текущего пользователя</li>
                                </ul>
                            </li>
                            <li><strong>Поддерживаемые теги в шаблонах:</strong> js.tpl
                                <ul>
                                    <li><strong>{name}</strong> - по умолчанию tagsadd. Глобальное название кнопок и функций</li>
                                    <li><strong>{AJAX}</strong> - ссылка на папку site.ru/engine/ajax</li>
                                    <li><strong>{THEME}</strong> - актуальная папка шаблона сайта</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                    <div class="ui segment tab" data-tab="update2">
                        <h2 class="ui header">
                            <i class="fas fa-edit"></i>
                            <div class="content">
                                Обновление с версий ниже 2.0
                                <div class="sub header">Обновляемая документация всегда <a href="http://help.maxim-harder.de/forum/26-polzovatelskie-tegi/" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                        <ol>
                            <li>Удаляем из папки с шаблоном <strong>всё</strong>, что имеет общее с <strong>tagsadd</strong></li>
                            <li>Удаляем из папки engine/inc и engine/modules файл tags.php</li>
                            <li>Открываем&nbsp;.htaccess и удаляем
                                <pre class="prettyprint">#Пользовательские теги
RewriteRule ^tags.php index.php?do=tag [L,QSA]</pre>
                            </li>
                            <li>Открываем engine/engine.php и удаляем:
                                <pre class="prettyprint">case "tag" :
	include ENGINE_DIR . '/modules/tags.php';
	break;</pre>
                            </li>
                            <li>Открываем engine/modules/main.php и удаляем:
                                <pre class="prettyprint linenums">
/*Добавление тегов*/
include ENGINE_DIR . '/data/tagsadd.php';
if(\$tagsconf['onof'] == 1) {
	\$tagsbutton = "&lt;a href=\"#\" role=\"button\" id=\"TagsAdd\"&gt;{\$tagsconf['button']}&lt;/a&gt;&lt;div style=\"display: none;\"&gt;&lt;div class=\"box-modal\" id=\"AddTags\"&gt;&lt;div class=\"box-modal_close arcticmodal-close\"&gt;закрыть&lt;/div&gt;&lt;form action=\"/tags.php\" method=\"post\"&gt;&lt;input class=\"form-control\" type=\"text\" placeholder=\"теги\" name=\"utags\" id=\"utags\"&gt;&lt;input type=\"hidden\" name=\"news\" value=\"".\$id."\"&gt;&lt;input type=\"hidden\" name=\"username\" value=\"".\$user."\"&gt;&lt;input type=\"hidden\" name=\"userid\" value=\"".\$userid."\"&gt;&lt;input type=\"hidden\" name=\"link\" value=\"".\$link."\"&gt;&lt;input type=\"hidden\" name=\"title\" value=\"".\$name."\"&gt;&lt;button class=\"btn btn-block btn-success\" onclick=\"submit();\" id=\"add_tags\"&gt;Отправить&lt;/button&gt;&lt;/form&gt;&lt;/div&gt;&lt;/div&gt;";

	if(\$tagsconf['guest'] == 1)  {
		\$tpl-&gt;set( '{tagsbutton}', \$tagsbutton );
	} else {
		if(\$is_logged) {
			\$tpl-&gt;set( '{tagsbutton}', \$tagsbutton);
		} else {
			\$tpl-&gt;set( '{tagsbutton}', "");
		}
	}
} else {
	\$tpl-&gt;set( '{tagsbutton}', "");
}
/*Добавление тегов*/</pre>
                            </li>
                            <li>Открываем engine/modules/show.full.php и удаляем:
                                <pre class="prettyprint linenums">include ENGINE_DIR . '/data/tagsadd.php';
if(\$tagsconf['onof'] == 1) {

    \$id = \$row['id'];
	\$name = \$row['title'];
	\$link = \$full_link;

	if(\$tagsconf['guest'] == 1 &amp;&amp; empty(\$member_id['name'])) {
		\$user = "Гость";
		\$userid = 0;
	} else {
		\$user = \$member_id['name'];
		\$userid = \$member_id['user_id'];
	}

	\$tags = \$_POST['utags'];

	\$tagsfull = "&lt;a href=\"#\" role=\"button\" id=\"TagsAdd\"&gt;{\$tagsconf['button']}&lt;/a&gt;&lt;div style=\"display: none;\"&gt;&lt;div class=\"box-modal\" id=\"AddTags\"&gt;&lt;div class=\"box-modal_close arcticmodal-close\"&gt;закрыть&lt;/div&gt;&lt;form action=\"/tags.php\" method=\"post\"&gt;&lt;input class=\"form-control\" type=\"text\" placeholder=\"теги\" name=\"utags\" id=\"utags\"&gt;&lt;input type=\"hidden\" name=\"news\" value=\"{\$id}\"&gt;&lt;input type=\"hidden\" name=\"username\" value=\"{\$user}\"&gt;&lt;input type=\"hidden\" name=\"userid\" value=\"{\$userid}\"&gt;&lt;input type=\"hidden\" name=\"link\" value=\"{\$link}\"&gt;&lt;input type=\"hidden\" name=\"title\" value=\"{\$name}\"&gt;&lt;br&gt;&lt;br&gt;&lt;button class=\"btn btn-block btn-success\" onclick=\"submit();\" id=\"add_tags\"&gt;Отправить&lt;/button&gt;&lt;/form&gt;&lt;/div&gt;&lt;/div&gt;";
	\$tagsbutton = "&lt;a href=\"#\" role=\"button\" id=\"TagsAdd\"&gt;{\$tagsconf['button']}&lt;/a&gt;";
	\$tagsbody = "&lt;div style=\"display: none;\"&gt;&lt;div class=\"box-modal\" id=\"AddTags\"&gt;&lt;div class=\"box-modal_close arcticmodal-close\"&gt;закрыть&lt;/div&gt;&lt;form action=\"/tags.php\" method=\"post\"&gt;&lt;input class=\"form-control\" type=\"text\" placeholder=\"теги\" name=\"utags\" id=\"utags\"&gt;&lt;input type=\"hidden\" name=\"news\" value=\"{\$id}\"&gt;&lt;input type=\"hidden\" name=\"username\" value=\"{\$user}\"&gt;&lt;input type=\"hidden\" name=\"userid\" value=\"{\$userid}\"&gt;&lt;input type=\"hidden\" name=\"link\" value=\"{\$link}\"&gt;&lt;input type=\"hidden\" name=\"title\" value=\"{\$name}\"&gt;&lt;br&gt;&lt;br&gt;&lt;button class=\"btn btn-block btn-success\" onclick=\"submit();\" id=\"add_tags\"&gt;Отправить&lt;/button&gt;&lt;/form&gt;&lt;/div&gt;&lt;/div&gt;";

	if(\$tagsconf['guest'] == 1)  {
		\$tpl-&gt;set( '{tagsadd}', \$tagsfull );
		\$tpl-&gt;set( '{tagsbutton}', \$tagsbutton);
		\$tpl-&gt;set( '{tagsbody}', \$tagsbody);
		\$tpl-&gt;set_block( "'\\[usertags\\](.*?)\\[/usertags\\]'si", "\\1" );
		\$tpl-&gt;set_block( "'\\[not-usertags\\](.*?)\\[/not-usertags\\]'si", "" );
	} else {
		if(\$is_logged) {
			\$tpl-&gt;set( '{tagsadd}', \$tagsfull );
	        \$tpl-&gt;set( '{tagsbutton}', \$tagsbutton);
			\$tpl-&gt;set( '{tagsbody}', \$tagsbody);
			\$tpl-&gt;set_block( "'\\[usertags\\](.*?)\\[/usertags\\]'si", "\\1" );
		    \$tpl-&gt;set_block( "'\\[not-usertags\\](.*?)\\[/not-usertags\\]'si", "" );
		} else {
			\$tpl-&gt;set( '{tagsadd}', "");
			\$tpl-&gt;set( '{tagsbutton}', "");
			\$tpl-&gt;set( '{tagsbody}', "");
			\$tpl-&gt;set_block( "'\\[usertags\\](.*?)\\[/usertags\\]'si", "" );
			\$tpl-&gt;set_block( "'\\[not-usertags\\](.*?)\\[/not-usertags\\]'si", "\\1" );
		}
	}

} else {
	\$tpl-&gt;set( '{tagsadd}', "");
	\$tpl-&gt;set( '{tagsbutton}', "");
	\$tpl-&gt;set( '{tagsbody}', "");
	\$tpl-&gt;set_block( "'\\[usertags\\](.*?)\\[/usertags\\]'si", "" );
	\$tpl-&gt;set_block( "'\\[not-usertags\\](.*?)\\[/not-usertags\\]'si", "\\1" );
}

/*Добавление тегов*/
							   </pre>
                            </li>
                            <li>Открываем templates/THEME/fullstory.tpl
                                <pre class="prettyprint linenums">&lt;script src="{THEME}/tagsadd/jquery.arcticmodal-0.3.min.js"&gt;&lt;/script&gt;
&lt;script src="{THEME}/tagsadd/jquery.arcticmodal-0.3.min.js"&gt;&lt;/script&gt;
&lt;script src="{THEME}/tagsadd/bootstrap-tokenfield.js"&gt;&lt;/script&gt;
&lt;link rel="stylesheet" href="{THEME}/tagsadd/css/jquery.arcticmodal-0.3.css"&gt;
&lt;link rel="stylesheet" href="{THEME}/tagsadd/css/bootstrap-tokenfield.css"&gt;
&lt;link rel="stylesheet" href="{THEME}/tagsadd/css/themes/dark.css"&gt;

&lt;script type="text/javascript"&gt;
	$(document).ready(function() {
		$(document).on('click', '#TagsAdd', function(){
			$('#AddTags').arcticmodal({
				beforeClose: function(data, el) {
					alert('Ваши предложенные теги были отпавлены на рассмотрение! Администрация проверит и добавит.');
				}
			});
		});
		$(document).on('click', '#add_tags', function(){
			$('#AddTags').arcticmodal('close');
		});
		$('#utags').tokenfield();
	});
&lt;/script&gt;
							</pre>
                            </li>
                            <li>Для обновления достаточно закинуть в корень сайта все файлы и запустить <a href="{$_SERVER['PHP_SELF']}?action=update" target="_blank">этот скрипт <i class="fas fa-external-link-alt"></i></a> (раз вы это читаете, значит вы молодец).</li>
                            <li>В админпанеле устанавливаем для пользователей доп. поле:
                                <ul>
                                    <li><strong>Название:</strong> любое</li>
                                    <li><strong>Описание:</strong> любое</li>
                                    <li><strong>Тип:</strong> список</li>
                                    <li><strong>Значение по умолчанию:</strong><br /> onsend|При отправке на проверку<br /> onadd|При добавлении в новость<br /> ondel|При отказе добавлять<br /> all|Уведомлять обо всём<br /> none|Не присылать уведомления</li>
                                    <li><strong>Добавить на страницу регистрации?:</strong> Да (на ваше усмотрение)</li>
                                    <li><strong>Поле может быть изменено пользователем?:</strong> Да</li>
                                    <li><strong>Сделать это поле личным?:</strong> Да</li>
                                </ul>
                            </li>
                            <li>В админпанеле настройте скрипт под себя.</li>
                            <li>Открываем файл шаблона полной новости (<strong>fullstory.tpl</strong>) и в любое место добавляем следующую строку:
                                <pre class="prettyprint">{include file="/engine/modules/maharder/tagsadd.php?newsid={news-id}&amp;focus=XXX"}</pre> Вместо <strong>XXX</strong> вписываем:
                                <ul>
                                    <li><strong>button</strong> - для вывода кнопки</li>
                                    <li><strong>modal</strong> - для вывода модального окна</li>
                                    <li><strong>functions</strong> - для вывода функций</li>
                                </ul>
                            </li>
                            <li>Ещё можно дописать параметр <strong>nameN</strong>. Так будут называться ключевые функции для окон и кнопок.</li>
                            <li>Читаем о возможностях шаблонов в разделе установки.</li>
                        </ol>
                    </div>
                    <div class="ui segment tab" data-tab="help">
                        <h2 class="ui header">
                            <i class="fas fa-user-circle"></i>
                            <div class="content">
                                Установка
                                <div class="sub header">Обновляемая документация всегда <a href="http://help.maxim-harder.de/forum/26-polzovatelskie-tegi/" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                         <p>Поддержка скрипта проводится в <strong>свободное</strong> от работы <strong>время</strong> и делается на <strong>бесплатной основе</strong> в кодировке <strong>UTF-8</strong>. Автор не несёт ответственности за ваши модификации и с ними связанные ошибки
                            при установке.</p>
                        <p><strong>Вы имеете право</strong>:</p>
                        <ul>
                            <li>Запросить о помощи в ветке на <a href="http://help.maxim-harder.de/forum/26-polzovatelskie-tegi/" target="_blank" rel="noopener">форуме</a>, на <a href="https://maxim-harder.de/dle/31-polzovatelskie-tegi.html" target="_blank" rel="noopener">сайте</a>        или в <a href="https://t.me/MaHarder" target="_blank" rel="noopener">телеграме</a> автора</li>
                            <li>Адаптировать функционал под себя</li>
                            <li>Адаптировать дизайн под себя</li>
                            <li>Предлагать новый функционал через те-же ветки, что описаны выше</li>
                            <li>Публиковать модуль в публичном доступе</li>
                        </ul>
                        <p><strong>Вы не имеете право:</strong></p>
                        <ul>
                            <li>Присваивать авторство себе</li>
                            <li>Требовать невозможного</li>
                            <li>Публиковать адаптации без согласия автора в сети</li>
                            <li>Распространять модуль без указания автора</li>
                            <li>Удалять копирайты</li>
                        </ul>
                        <p><strong>Авторство</strong>:</p>
                        <ul>
                            <li><strong>Автор</strong>: Maxim Harder</li>
                            <li><strong>Телеграм</strong>: <a href="https://t.me/MaHarder" target="_blank" rel="noopener">MaHarder</a></li>
                        </ul>
                        <p><strong>Финансовая поддержка (доброволная)</strong>:</p>
                        <ul>
                            <li><strong>Webmoney (RU)</strong>:&nbsp;R127552376453</li>
                            <li><strong>Webmoney (USD)</strong>:&nbsp;Z139685140004</li>
                            <li><strong>Webmoney (EU)</strong>:&nbsp;E275336355586</li>
                            <li><strong>PayPal</strong>:&nbsp;<a href="https://paypal.me/MaximH" target="_blank" rel="noopener">paypal.me/MaximH</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ui.sakuranight.net/js/jquery.js"></script>
    <script src="https://ui.sakuranight.net/js/frame.js"></script>
    <script src="https://ui.sakuranight.net/js/icons.js"></script>
    <script src="https://ui.sakuranight.net/js/prettify.js"></script>
    <script src="https://ui.sakuranight.net/js/run_prettify.js"></script>
    <script src="https://ui.sakuranight.net/js/installpage.js"></script>
</body>

</html>
HTML;

        break;

}

echo $html;