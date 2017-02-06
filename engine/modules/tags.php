<?php
if (! defined ( 'DATALIFEENGINE' )) {
	die ( "Hacking attempt!" );
}
require_once (ENGINE_DIR.'/api/api.class.php');
include ENGINE_DIR . '/data/tagsadd.php';

extract($_POST);

if($tagsconf['onof'] && $tagsconf['send']) {
	$date = time();
	$tagsadd = explode( ',', $_POST['utags'] );
	$tag = array();
	foreach ($tagsadd as $key) {
		$tag[] = "<span class=\"label label-danger\">{$key}</span>";
	}
	$tags = implode(' ', $tag);
	$subject = "Пользовательские теги к {$title}";
	$subjecto = "Ваши теги к {$title}";
	$mess = "{$username} предложил к новости <a href=\"{$link}\" target=\"_blank\" >{$title}</a> новые теги: {$tags}.";
	$messo = "Вами предложенные теги к <a href=\"{$link}\" target=\"_blank\" >{$title}</a>: {$tags}.";

	if (empty($_POST['utags'])) {
		$link = "/";
	} else {
		$link = $link;
		$master = $tagsconf['master'];
		$admin = $tagsconf['admin'];
		$users = $db->super_query("SELECT * FROM " . PREFIX . "_users WHERE name='{$admin}'");
		$adminid = $users['user_id'];
		$db->super_query("INSERT INTO " . PREFIX . "_post_tags (news_id, username, tags) VALUES ('{$news}','{$username}','{$tags}')");
		$db->query( "INSERT INTO " . USERPREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder, sendid) values ('{$subject}','{$mess}','{$adminid}','{$username}','{$date}','0','inbox','0')");
		if($tagsconf['user']) $db->query( "INSERT INTO " . USERPREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder, sendid) values ('{$subjecto}','{$messo}','{$userid}','{$master}','{$date}','0','inbox','0')");
		$db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1  WHERE user_id='{$adminid}'" );
		if($tagsconf['user']) $db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1  WHERE user_id='{$userid}'" );
	}
}

print "<script type=\"text/javascript\">
    window.location.replace(\"$link\");
</script>";
?>
