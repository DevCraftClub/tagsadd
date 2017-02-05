<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004,2015 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: complaint.php
-----------------------------------------------------
 Назначение: управление жалобами
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) ) die( "You are a fucking faggot!" );

# Функции для работы с панелью == START.
function showRow($title = "", $description = "", $field = "") {
	echo "<tr><td class=\"col-xs-10 col-sm-6 col-md-7\"><h6>{$title}</h6><span class=\"note large\">{$description}</span></td><td class=\"col-xs-2 col-md-5 settingstd\">{$field}</td></tr>";
}
function showRow2($title = "", $description = "", $field = "") {
	echo "<tr><td colspan=\"2\" class=\"col-xs-10 col-sm-6 col-md-7\"><h6>{$title}</h6><span class=\"note large\">{$description}</span></td></tr>";
}
function showInput($name, $value) {
	return "<input type=text style=\"width: 400px;text-align: center;\" name=\"save_con[{$name}]\" value=\"{$value}\" size=20>";
}
function makeCheckBox($name, $selected, $flag = true) {
	$selected = $selected ? "checked" : "";
	if($flag)
		echo "<input class=\"iButton-icons-tab\" type=\"checkbox\" name=\"$name\" value=\"1\" {$selected}>";
	else
		return "<input class=\"iButton-icons-tab\" type=\"checkbox\" name=\"$name\" value=\"1\" {$selected}>";
}
function showForm($title = "", $field = "") {
	echo "<div class=\"form-group\"><label class=\"control-label col-xs-2\">{$title}</label><div class=\"col-xs-10\">{$field}</div></div>";
}
function makeDropDown($value, $name, $selected) {
	$output = "<select class=\"uniform\" name=\"save_con[$name]\">\r\n";
	foreach ( $value as $values => $description ) {
		$output .= "<option value=\"{$values}\"";
		if( $selected == $values ) {
			$output .= " selected ";
		}
		$output .= ">$description</option>\n";
	}
	$output .= "</select>";
	return $output;
}
# Функции для работы с панелью == END.
include ENGINE_DIR . "/data/tagsadd.php";

if( !$user_group[$member_id['user_group']]['admin_complaint'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}
switch($action):

	case "config":
		echoheader( "<i class=\"icon-wrench\"></i> ".$tagsconf['name'], "Настройки модуля" );
echo <<<HTML
		<form action="$PHP_SELF?mod=tags&action=save&for=config" method="post">
			<div id="setting" class="box">
				<div class="box-header"><div class="title">Настройки</div></div>
				<div class="box-content">
					<table class="table table-normal">
						<thead>
HTML;
							showRow2("Основные настройки", "Включаем и выключаем модуль");
echo <<<HTML
						</thead>
						<tbody>
HTML;
							showRow("Включить модуль?", "Включаем-выключаем", makeCheckBox( "save_con[onof]", ($tagsconf['onof'] == 1) ? true : false, false ));
							showRow("Добавление тегов гостями", "Если отключено, то теги могут добавлять только авторизованные пользователи.", makeCheckBox( "save_con[guest]", ($tagsconf['guest'] == 1) ? true : false, false ));
							showRow("Отсылать уведомление при добавлении тегов?", "Если нет, то письма с уведомлением не будут отсылаться, но будут показываться в админке", makeCheckBox( "save_con[send]", ($tagsconf['send'] == 1) ? true : false, false ));
							showRow("Отсылать уведомление отправителю тегов?", "Если нет, то письмо получит лишь админ", makeCheckBox( "save_con[user]", ($tagsconf['user'] == 1) ? true : false, false ));
							showRow("Имя администратора", "Укажите имя, которому будут приходить уведомления", showInput("admin", $tagsconf['admin']));
							showRow("Имя отправителя", "Укажите имя, откоторого будет исходить сообщения", showInput("master", $tagsconf['master']));
							showRow("Название кнопки", "Как будет выводится кнопка в новости? К примеру: добавить", showInput("button", $tagsconf['button']));
echo <<<HTML
						</tbody>
					</table>
				</div>
				<div class="box-footer padded">
					<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
					<input type="submit" class="btn btn-lg btn-green" value="{$lang['user_save']}">
					<a href="$PHP_SELF?mod=tags" class="btn btn-lg btn-red" style="color:white">Назад</a>
				</div>
			</div>
			<in
		</form>
		<div class="text-center">Copyright 2016 &copy; <a href="http://maxim-harder.de/" target="_blank"><b>Maxim Harder</b></a>. All rights reserved.</div>
HTML;
		echofooter();
	break;
	case "save":

		if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
			die( "Hacking attempt! User not found" );
		}

		$_for = $_REQUEST['for'];
		if( $_for == "config" ) {
			$save_con = $_REQUEST['save_con'];
			$handler = fopen(ENGINE_DIR . '/data/tagsadd.php', "w");


			fwrite($handler, "<?PHP\n/*\n=============================================================================\nПользовательские теги, файл конфигурации\n=============================================================================\nАвтор хака: Максим Гардер\n-----------------------------------------------------\nURL: http://maxim-harder.de/\n-----------------------------------------------------\nemail: info@maxim-harder.de\n-----------------------------------------------------\nskype: maxim_harder\n=============================================================================\nФайл:  engine/data/tagsadd.php\n=============================================================================\n*/\n\n\$tagsconf = array (\n\n'version' => \"1.2.1\",\n\n'name' => \"Пользовательские теги\",\n\n");
			foreach ($save_con as $name => $value) {
				fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
			}
			fwrite($handler, ");\n\n?>");
			fclose($handler);

			clear_cache();
			msg("info", $lang['opt_sysok'], "<b>{$lang['opt_sysok_1']}</b>", "$PHP_SELF?mod=tags");
		}
	break;
	default :
if ($_GET['action'] == "delete") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}

	$id = intval($_GET['id']);

	$db->query( "DELETE FROM " . PREFIX . "_post_tags WHERE id = '{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '22', '')" );

	header( "Location: ?mod=tags" ); die();
}

if ($_POST['action'] == "mass_delete") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}

	$selected_complaint = $_POST['selected_complaint'];

	if( ! $selected_complaint ) {
		msg( "error", $lang['mass_error'], "Вы не выбрали предложения для операций с ними.", "?mod=tags" );
	}

	foreach ( $selected_complaint as $complaint ) {

		$complaint = intval($complaint);

		$db->query( "DELETE FROM " . PREFIX . "_post_tags WHERE id = '{$complaint}'" );
	}
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '22', '')" );

	header( "Location: ?mod=tags" ); die();
}

$found = false;

echoheader("<i class=\"icon-bullhorn\"></i>Список предложений", "<a href=\"{$config['admin_path']}?mod=tags&action=config\" class=\"btn btn-default\">Настройки</a>");

	echo <<<HTML
<script type="text/javascript">
<!-- begin
function popupedit( name ){

		var rndval = new Date().getTime();

		$('body').append('<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #666666; opacity: .40;filter:Alpha(Opacity=40); z-index: 999; display:none;"></div>');
		$('#modal-overlay').css({'filter' : 'alpha(opacity=40)'}).fadeIn('slow');

		$("#dleuserpopup").remove();
		$("body").append("<div id='dleuserpopup' title='{$lang['user_edhead']}' style='display:none'></div>");

		$('#dleuserpopup').dialog({
			autoOpen: true,
			width: 570,
			height: 510,
			resizable: false,
			dialogClass: "modalfixed",
			buttons: {
				"{$lang['user_can']}": function() {
					$(this).dialog("close");
					$("#dleuserpopup").remove();
				},
				"{$lang['user_save']}": function() {
					document.getElementById('edituserframe').contentWindow.document.getElementById('saveuserform').submit();
				}
			},
			open: function(event, ui) {
				$("#dleuserpopup").html("<iframe name='edituserframe' id='edituserframe' width='100%' height='389' src='?mod=editusers&action=edituser&user=" + name + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' allowtransparency='true'></iframe>");
			},
			beforeClose: function(event, ui) {
				$("#dleuserpopup").html("");
			},
			close: function(event, ui) {
					$('#modal-overlay').fadeOut('slow', function() {
			        $('#modal-overlay').remove();
			    });
			 }
		});

		if ($(window).width() > 830 && $(window).height() > 530 ) {
			$('.modalfixed.ui-dialog').css({position:"fixed"});
			$('#dleuserpopup').dialog( "option", "position", ['0','0'] );
		}

		return false;

}
// end -->
</script>
HTML;

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post_tags WHERE news_id > '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=tags" method="post" name="optionsbar3" id="optionsbar3">
<input type="hidden" name="mod" value="tags">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="box">
  <div class="box-header">
    <div class="title">Список предложенных тегов</div>
  </div>
  <div class="box-content">

    <table class="table table-normal">
      <thead>
      <tr>
        <td style="width: 180px">Предложение отправил:</td>
        <td>Предложенные теги:</td>
		<td style="width: 300px">{$lang['user_action']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all3()"></td>
      </tr>
      </thead>
	  <tbody>
HTML;



$db->query("SELECT " . PREFIX . "_post_tags.id, `news_id`, " . PREFIX . "_post_tags.tags, `username`,  " . PREFIX . "_post.id as post_id, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category  FROM " . PREFIX . "_post_tags LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_post_tags.news_id=" . PREFIX . "_post.id WHERE news_id > '0' ORDER BY id DESC");


$entries = "";

while($row = $db->get_row()) {
	$found = true;
	$row['tags'] = stripslashes($row['tags']);
	if ( $row['date'] ) $date = date( "d.m.Y H:i", $row['date'] )."<br /><br />"; else $date = "";

	if ($row['post_id']) {

		$edit_link = "<br /><br /><a class=\"btn btn-default\" href=\"?mod=editnews&amp;action=editnews&amp;id=" . $row['news_id'] ."\" target=\"_blank\"><i class=\"icon-pencil\"></i> {$lang['opt_complaint_18']}</a>";

	} else {

		$edit_link = "";
	}

	$from = "<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['username'])."'); return(false)\" href=\"#\">{$row['username']}</a><br /><br /><a class=\"btn btn-gold\" href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['username'])."\" target=\"_blank\">{$lang['send_pm']}</a>";


	$row['category'] = intval( $row['category'] );

	if( $config['allow_alt_url'] ) {

		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {

			if( $row['category'] and $config['seo_type'] == 2 ) {

				$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['post_id'] . "-" . $row['alt_name'] . ".html";

			} else {

				$full_link = $config['http_home_url'] . $row['post_id'] . "-" . $row['alt_name'] . ".html";

			}

		} else {

			$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime ($row['newsdate']) ) . $row['alt_name'] . ".html";
		}

	} else {

		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['post_id'];

	}

	$full_link = "<a class=\"status-info\" href=\"" . $full_link . "\" target=\"_blank\">" . stripslashes( $row['title'] ) . "</a>";

	$entries .= "<tr>
	<td>{$date}<strong>{$from}</strong></td>
    <td>Предложение тегов к новости: {$full_link}<br /><br /><b>Предложенные теги</b><br />{$row['tags']}<br /><br /></td>
    <td align=\"center\" class=\"settingstd\"><a uid=\"{$row['id']}\" class=\"btn btn-red dellink3\" href=\"?mod=tags\"><i class=\"icon-trash\"></i>Удалить</a>{$edit_link}</td>
    <td align=\"center\" class=\"settingstd\"><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>";

}


echo <<<HTML
		{$entries}
	  </tbody>
	</table>

   </div>
	<div class="box-footer padded text-right">
		<select class="uniform" name="action"><option value="">{$lang['edit_selact']}</option><option value="mass_delete">{$lang['edit_seldel']}</option></select>&nbsp;<input class="btn btn-gray" type="submit" value="{$lang['b_start']}">&nbsp;<a href="{$config['admin_path']}?mod=tags&action=config" class="btn btn-default">Настройки</a>
	</div>
</div>
</form>
<script language="javascript" type="text/javascript">
<!--

	function ckeck_uncheck_all3() {
	    var frm = document.optionsbar3;
	    for (var i=0;i<frm.elements.length;i++) {
	        var elmnt = frm.elements[i];
	        if (elmnt.type=='checkbox') {
	            if(frm.master_box.checked == true){ elmnt.checked=false; }
	            else{ elmnt.checked=true; }
	        }
	    }
	    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
	    else{ frm.master_box.checked = true; }
	}

	$(function(){

			$("#list3").delegate("tr", "hover", function(){
			  $(this).toggleClass("hoverRow");
			});

			var tag_name = '';

			$('.dellink3').click(function(){

				id_comp = $(this).attr('uid');

				DLEconfirm( 'Вы действительно хотите удалить данное предложение?', '{$lang['p_confirm']}', function () {

					document.location='?mod=tags&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

				} );

				return false;
			});
	});
//-->
</script>
HTML;

}



if (!$found) {


echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">Список предложений</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center settingstd">На текущий момент предложений нет.</td>
		    </tr>
		</table>
	</div>
	<div class="row box-section"><div class="col-md-12 text-center"><a class="btn btn btn-red" href="javascript:history.go(-1)">{$lang['func_msg']}</a></div></div>
  </div>
</div>
HTML;


}

echofooter();

	break;
endswitch;
?>
