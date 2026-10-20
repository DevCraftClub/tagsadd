<?php

declare(strict_types=1);

/**
 * Публичный include TagsAdd (канон Controller/).
 *
 * {include file="devcraft/src/modules/TagsAdd/Controller/show_tags_add.php?news_id={news-id}&focus=button"}
 *
 * focus: button|modal. css/js — пустые (файлы через siteAssets / теги {devcraft*}).
 * Параметр newsid — синоним news_id (старые темы).
 */

if(!defined('DATALIFEENGINE')) {
	header('HTTP/1.1 403 Forbidden');

	exit('Hacking attempt!');
}

$newsId = 0;

if(isset($news_id)) {
	$newsId = (int) $news_id;
} elseif(isset($newsid)) {
	$newsId = (int) $newsid;
}

$focus = isset($focus) ? (string) $focus : 'button';

echo (new DevCraft\Modules\TagsAdd\Controller\FullstoryController())->render($newsId, $focus);
