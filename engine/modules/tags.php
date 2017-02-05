<?php
if (! defined ( 'DATALIFEENGINE' )) {
	die ( "Hacking attempt!" );
}
include ENGINE_DIR . '/data/tagsadd.php';

extract($_POST);

if($tagsconf['onof'] == 1 && $tagsconf['send'] == 1) {
	$subject = "Пользовательские теги к {$title}";
	$subjecto = "Ваши теги к {$title}";
	$mess = "{$username} предложил к новости <a href=\"{$link}\" target=\"_blank\" >{$title}</a> новые теги: {$tags}.";
	$messo = "Вами предложенные теги к <a href=\"{$link}\" target=\"_blank\" >{$title}</a>: {$tags}.";
	$date = time();
	$tagsadd = explode( ',', $_POST['utags'] );
	$tag = array();
	foreach ($tagsadd as $key) {
		$tag[] = "<span class=\"label label-danger\">{$key}</span>";
	}
	$tags = implode(',', $tag);
	if (empty($_POST['utags'])) {
		$link = "/";
	} else {
		$link = $link;
		$master = $tagsconf['master'];
		$admin = $tagsconf['admin'];
		$users = $db->query("SELECT * FROM " . PREFIX . "_users WHERE name='{$admin}'");
		$adminid = $users['user_id'];
		$db->super_query("INSERT INTO " . PREFIX . "_post_tags (news_id, username, tags) VALUES ('{$news}','{$username}','{$tags}')");
		$db->query( "INSERT INTO " . USERPREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder, sendid) values ('{$subject}','{$mess}','1','{$username}','{$date}','0','inbox','0')");
		if($tagsconf['user'] == 1) $db->query( "INSERT INTO " . USERPREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder, sendid) values ('{$subjecto}','{$messo}','{$userid}','{$master}','{$date}','0','inbox','0')");
		$db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1  WHERE user_id='{$adminid}'" );
		if($tagsconf['user'] == 1) $db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1  WHERE user_id='{$userid}'" );
	}
}

if($tagsconf['onof'] != 1) $link = "/";

print "<script language='Javascript'><!--
function reload() {location = \"$link\"}; setTimeout('reload()', 0);
//--></script>";
?>
