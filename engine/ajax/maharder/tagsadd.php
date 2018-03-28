<?php

//	===============================
//	Настройки модуля
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR',  substr( dirname(  __FILE__ ), 0, -21 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
include ENGINE_DIR . '/data/tagsadd.php';
include ENGINE_DIR . '/data/config.php';

$id = intval($_POST['newsid']);
if(!$id) return;
$userid = intval($_POST['userid']);

if($userid > 0) $user = $db->super_query( "SELECT * FROM " . PREFIX . "_users WHERE user_id = '".$userid."'" );
else $user = ['id' => 0, 'name' => "Гость"];
$news = $db->super_query( "SELECT * FROM " . PREFIX . "_post WHERE id = '".$id."'" );

include_once ENGINE_DIR . '/classes/parse.class.php';
$parse = new ParseFilter();

$from = $tagsconf['master'];
$to = $user['user_id'];
$folder = 'inbox';
$subject = '';
$message = '';
$time = time();

$userfields = $user['xfields'];
$userfields = explode("||", $userfields);
$param = '';
foreach ($userfields as $fields){
    $tempField = explode('|', $fields);
    if($tempField[0] == $tagsconf['userinform']){
        $param = $tempField[1];
        break;
    }
}

$title = $news['title'];
$title = htmlspecialchars( stripslashes( $title ), ENT_QUOTES, $config['charset'] );
$title = str_replace("&amp;","&", $title );

if( $config['allow_alt_url'] ) {
    if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
        if( intval( $news['category'] ) and $config['seo_type'] == 2 ) {
            $full_link = $config['http_home_url'] . get_url( intval( $news['category'] ) ) . "/" . $news['id'] . "-" . $news['alt_name'] . ".html";
        } else {
            $full_link = $config['http_home_url'] . $news['id'] . "-" . $news['alt_name'] . ".html";
        }
    } else {
        $full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime( $news['date'] ) ) . $news['alt_name'] . ".html";
    }
} else {
    $full_link = $config['http_home_url'] . "index.php?newsid=" . $news['id'];
}

$newtags = htmlspecialchars( $_POST['newtags'], ENT_QUOTES, $config['charset'] );
$db->query("INSERT INTO " . PREFIX . "_tagsadd (news_id, user_id, tags) VALUES ({$id}, {$userid}, '{$newtags}')");

if(($param == 'all' || $param == 'onsend') && $_POST['userid'] > 0){
    $subject = str_replace('%title%', $title, $tagsconf['usermailtitle']);
    $subject = str_replace('%user%', $user['name'], $subject);

    $message = str_replace('%title%', $title, $tagsconf['usermail']);
    $message = str_replace('%user%', $user['name'], $message);
    $message = str_replace('%link%', $full_link, $message);
    $message = str_replace('%tags%', $newtags, $message);
    $message = $parse->BB_Parse( $message, false );

    $db->query("INSERT INTO " . PREFIX . "_pm (subj, text, user, user_from, date, folder) VALUES ('{$subject}', '{$message}', {$to}, '{$from}', {$time}, '{$folder}')");
    $db->query("UPDATE " . PREFIX . "_users SET pm_all = pm_all+1, pm_unread=pm_unread+1 WHERE user_id = '{$to}'");
}

if($tagsconf['send']) {
    $myTag = $db->super_query("SELECT * FROM " . PREFIX . "_tagsadd WHERE news_id = '{$id}' AND tags LIKE '%{$newtags}%'");
    $editlink = $config['http_home_url'].$config['admin_path']."?mod=tagsadd&do=edittag&id=".$myTag['id'];

    $subject = str_replace('%title%', $title, $tagsconf['adminmailtitle']);
    $subject = str_replace('%user%', $user['name'], $subject);

    $message = str_replace('%title%', $title, $tagsconf['adminmail']);
    $message = str_replace('%user%', $user['name'], $message);
    $message = str_replace('%link%', $full_link, $message);
    $message = str_replace('%adminlink%', $editlink, $message);
    $message = str_replace('%tags%', $newtags, $message);
    $message = $parse->BB_Parse( $message, false );

    $admin = $db->super_query( "SELECT * FROM " . PREFIX . "_users WHERE name = '".$tagsconf['admin']."'" );
    $db->query("INSERT INTO " . PREFIX . "_pm (subj, text, user, user_from, date, folder) VALUES ('{$subject}', '{$message}', {$admin['user_id']}, '{$from}', {$time}, '{$folder}')");
    $db->query("UPDATE " . PREFIX . "_users SET pm_all = pm_all+1, pm_unread=pm_unread+1 WHERE user_id = '{$admin['user_id']}'");
}

header("HTTP/1.0 301 Moved Permanently");
header("Location: {$full_link}" );

?>