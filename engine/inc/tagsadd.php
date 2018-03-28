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

if( !defined( 'DATALIFEENGINE' ) ) die( "Oh! You little bastard!" );

$codename = "tagsadd";

@include (ENGINE_DIR . '/data/'.$codename.'.php');
require_once (ENGINE_DIR . '/inc/maharder/assets/functions.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/version.php');
	
impFiles('css', $cssfiles);

$adminlink = "?mod=".$codename;

switch ($_GET['do']) {
	case 'settings':
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/settings.php');
		break;

	case 'list':
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/list.php');
		break;

	case 'save':
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/save.php');
		break;

	case 'edittag':
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/edit.php');
		break;

	case 'savetag':
        require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/savetags.php');
        break;

    case 'inserttag':
        require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/inserttag.php');
        break;

    case 'delete':
        require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/delete.php');
        break;
	
	default:
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/default.php');
		break;
}

impFiles('js', $jsfiles);
echofooter();