# tagsadd
Пользовательские теги

#  Нововведения начиная с версии 1.2.1
- добавлены теги [usertags] и [not-usertags] для скрытия данных для включённого и выключенного модуля
- исправлены баги

# Нововведения начиная с версии 1.2
- добавлена админ панель
- улучшен код

# Обновление до 1.2.1
Замените все папки и файлы в директории engine

# Установка
- Выполняем запросы<br>
<pre><code>INSERT INTO `dle_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`) VALUES ('tags', 'Добавление тегов', 'Пользовательские предложения тегов', 'tags.png', '1');
CREATE TABLE IF NOT EXISTS `dle_post_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` text NOT NULL,
  `username` text NOT NULL,
  `tags` text NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `news_id` (`news_id`),
  FULLTEXT KEY `username` (`username`),
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;</code></pre><br><br>

dle - меняем на свой префикс
- Открываем .htaccess<br>
После <br>
RewriteEngine On<br>
<br>
Ставим<br>
#Пользовательские теги<br>
RewriteRule ^tags.php index.php?do=tag [L,QSA]<br>
- Открываем engine/engine.php<br>
после<br>
switch ( $do ) {<br>
<br>
ставим<br><code>
	case "tag" :
		include ENGINE_DIR . '/modules/tags.php';
		break;</code>
- Открываем engine/modules/main.php<br>
Ищем<br>
<code>$tpl->set ( '{speedbar}', $tpl->result['speedbar'] );</code><br>
<br>
ниже<br><code>
/*Добавление тегов*/
include ENGINE_DIR . '/data/tagsadd.php';
if($tagsconf['onof'] == 1) {
	$tagsbutton = "<a href=\"#\" role=\"button\" id=\"TagsAdd\">{$tagsconf['button']}</a><div style=\"display: none;\"><div class=\"box-modal\" id=\"AddTags\"><div class=\"box-modal_close arcticmodal-close\">закрыть</div><form action=\"/tags.php\" method=\"post\"><input class=\"form-control\" type=\"text\" placeholder=\"теги\" name=\"utags\" id=\"utags\"><input type=\"hidden\" name=\"news\" value=\"".$id."\"><input type=\"hidden\" name=\"username\" value=\"".$user."\"><input type=\"hidden\" name=\"userid\" value=\"".$userid."\"><input type=\"hidden\" name=\"link\" value=\"".$link."\"><input type=\"hidden\" name=\"title\" value=\"".$name."\"><br><br><button class=\"btn btn-block btn-success\" onclick=\"submit();\" id=\"add_tags\">Отправить</button></form></div></div>";

	if($tagsconf['guest'] == 1)  {
		$tpl->set( '{tagsbutton}', $tagsbutton );
	} else {
		if($is_logged) {
			$tpl->set( '{tagsbutton}', $tagsbutton);
		} else {
			$tpl->set( '{tagsbutton}', "");
		}
	}
} else {
	$tpl->set( '{tagsbutton}', "");
}
/*Добавление тегов*/</code>
- Открываем engine/modules/show.full.php<br>
перед<br><code>
$tpl->compile( 'content' );

if( $user_group[$member_id['user_group']]['allow_hide'] ) $tpl->result['content'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $tpl->result['content']) );
</code><br><br>
ставим<br><code>
/*Добавление тегов*/
		include ENGINE_DIR . '/data/tagsadd.php';
		if($tagsconf['onof'] == 1) {
			$tagsfull = "<a href=\"#\" role=\"button\" id=\"TagsAdd\">{$tagsconf['button']}</a><div style=\"display: none;\"><div class=\"box-modal\" id=\"AddTags\"><div class=\"box-modal_close arcticmodal-close\">закрыть</div><form action=\"/tags.php\" method=\"post\"><input class=\"form-control\" type=\"text\" placeholder=\"теги\" name=\"utags\" id=\"utags\"><input type=\"hidden\" name=\"news\" value=\"".$id."\"><input type=\"hidden\" name=\"username\" value=\"".$user."\"><input type=\"hidden\" name=\"userid\" value=\"".$userid."\"><input type=\"hidden\" name=\"link\" value=\"".$link."\"><input type=\"hidden\" name=\"title\" value=\"".$name."\"><br><br><button class=\"btn btn-block btn-success\" onclick=\"submit();\" id=\"add_tags\">Отправить</button></form></div></div>";
			$tagsbutton = "<a href=\"#\" role=\"button\" id=\"TagsAdd\">{$tagsconf['button']}</a>";
			$tagsbody = "<div style=\"display: none;\"><div class=\"box-modal\" id=\"AddTags\"><div class=\"box-modal_close arcticmodal-close\">закрыть</div><form action=\"/tags.php\" method=\"post\"><input class=\"form-control\" type=\"text\" placeholder=\"теги\" name=\"utags\" id=\"utags\"><input type=\"hidden\" name=\"news\" value=\"".$id."\"><input type=\"hidden\" name=\"username\" value=\"".$user."\"><input type=\"hidden\" name=\"userid\" value=\"".$userid."\"><input type=\"hidden\" name=\"link\" value=\"".$link."\"><input type=\"hidden\" name=\"title\" value=\"".$name."\"><br><br><button class=\"btn btn-block btn-success\" onclick=\"submit();\" id=\"add_tags\">Отправить</button></form></div></div>";

			$id = $row['id'];
			$name = $row['title'];
			$link = $full_link;

			if($tagsconf['guest'] == 1 && empty($member_id['name'])) {
				$user = "Гость";
				$userid = 0;
			} else {
				$user = $member_id['name'];
				$userid = $member_id['user_id'];
			}

			$tags = $_POST['utags'];

			if($tagsconf['guest'] == 1)  {
				$tpl->set( '{tagsadd}', $tagsfull );
				$tpl->set( '{tagsbutton}', $tagsbutton);
				$tpl->set( '{tagsbody}', $tagsbody);
				$tpl->set_block( "'\\[usertags\\](.*?)\\[/usertags\\]'si", "\\1" );
				$tpl->set_block( "'\\[not-usertags\\](.*?)\\[/not-usertags\\]'si", "" );
			} else {
				if($is_logged) {
					$tpl->set( '{tagsadd}', $tagsfull );
					$tpl->set( '{tagsbutton}', $tagsbutton);
					$tpl->set( '{tagsbody}', $tagsbody);
					$tpl->set_block( "'\\[usertags\\](.*?)\\[/usertags\\]'si", "\\1" );
					$tpl->set_block( "'\\[not-usertags\\](.*?)\\[/not-usertags\\]'si", "" );
				} else {
					$tpl->set( '{tagsadd}', "");
					$tpl->set( '{tagsbutton}', "");
					$tpl->set( '{tagsbody}', "");
					$tpl->set_block( "'\\[usertags\\](.*?)\\[/usertags\\]'si", "" );
					$tpl->set_block( "'\\[not-usertags\\](.*?)\\[/not-usertags\\]'si", "\\1" );
				}
			}

		} else {
			$tpl->set( '{tagsadd}', "");
			$tpl->set( '{tagsbutton}', "");
			$tpl->set( '{tagsbody}', "");
			$tpl->set_block( "'\\[usertags\\](.*?)\\[/usertags\\]'si", "" );
			$tpl->set_block( "'\\[not-usertags\\](.*?)\\[/not-usertags\\]'si", "\\1" );
		}

		/*Добавление тегов*/</code>
- Открываем templates/THEME/fullstory.tpl<br>
В самое начало<br><code>
<script src="{THEME}/tagsadd/jquery.arcticmodal-0.3.min.js"></script>
<script src="{THEME}/tagsadd/bootstrap-tokenfield.js"></script>
<link rel="stylesheet" href="{THEME}/tagsadd/css/jquery.arcticmodal-0.3.css">
<link rel="stylesheet" href="{THEME}/tagsadd/css/bootstrap-tokenfield.css">
<link rel="stylesheet" href="{THEME}/tagsadd/css/themes/dark.css">

<script type="text/javascript">
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
</script>
</code><br>
dark.css - меняем на свой или альтернативный из папки<br>

# Инструкция по применению
- Если вы хотите подключить тег не в fullstory.tpl, а в main.tpl, то в нужное место (main.tpl) ставим тег {tagsbutton}, а в fullstory.tpl тогда тег {tagsbody}.
- Если вы решили всётаки подключить в шаблон полной новости, то в любое место добавляем тег {tagsadd}.
Для полной новости действуют следующие теги:
- {tagsadd} - полное подключение модуля
- {tagsbutton} - добавляет только кнопку "Добавить"
- {tagsbody} - добавляет в шаблон только модальное окно
- [usertags][/usertags] - если модуь включён, то заключённый в эти тег текст будет отображаться
- [not-usertags][/not-usertags] - аналогия с верхним, только наоборот, если модуль выключен
