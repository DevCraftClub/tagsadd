<?php

declare(strict_types=1);

/**
 * Рендер кнопки / модалки / assets TagsAdd для fullstory.
 *
 * {include file="engine/modules/devcraft/tags_add.php?newsid={news-id}&focus=button|modal|js|css"}
 *
 * DLE подключает этот файл через dle_template::load_file(), который перед
 * include клонирует вызывающий $tpl (см. templates.class.php) — внутри
 * доступен настоящий dle_template, а не самодельный strtr()-рендер.
 *
 * @var \dle_template $tpl
 */

if(!defined('DATALIFEENGINE')) {
	die('Hacking attempt!');
}

// {include} на фронте идёт без admin bootstrap — __() из DevCraft может отсутствовать.
if(!function_exists('__')) {
	/**
	 * Fallback без DevCraft: возвращает исходную фразу (ru = source).
	 *
	 * @param   array<string, mixed>  $params
	 */
	function __(string $phrase, array $params = [], int $count = 0): string {
		return $phrase;
	}
}

$focus  = isset($focus) ? (string) $focus : 'button';
$newsid = isset($newsid) ? (int) $newsid : (isset($news_id) ? (int) $news_id : 0);

if($newsid <= 0) {
	return;
}

$configPath  = ROOT_DIR . '/devcraft/config/tags_add.json';
$buttonLabel = __('Предложить теги');
$allowGuests = false;

if(is_file($configPath)) {
	$raw = json_decode((string) file_get_contents($configPath), true);

	if(is_array($raw)) {
		$buttonLabel = trim((string) ($raw['button_label'] ?? $buttonLabel));
		$allowGuests = !empty($raw['allow_guests']);
	}
}

global $is_logged, $member_id, $config, $dle_login_hash;

$logged = !empty($is_logged) && !empty($member_id['user_id']);

if(!$logged && !$allowGuests) {
	return;
}

$skin = totranslit((string) ($config['skin'] ?? 'Default'), false, false);

if(!is_dir(ROOT_DIR . '/templates/' . $skin . '/devcraft/tags_add')) {
	$skin = 'Default';
}

$tpl->dir = ROOT_DIR . '/templates/' . $skin;

$homeUrl = rtrim((string) ($config['http_home_url'] ?? '/'), '/') . '/';
$modUrl  = rtrim((string) ($config['http_home_url'] ?? '/'), '/') . '/templates/' . $skin . '/devcraft/tags_add';
$ajaxUrl = $homeUrl . 'devcraft/ajax.php';
$dcPublicUrl = $homeUrl . 'devcraft/src/templates/core/assets/js/dc_public.js';

$tpl->set('{news-id}', (string) $newsid);
$tpl->set('{button-label}', htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8'));
$tpl->set('{tags-label}', __('Теги (через запятую)'));
$tpl->set('{user-hash}', htmlspecialchars((string) ($dle_login_hash ?? ''), ENT_QUOTES, 'UTF-8'));

switch($focus) {
	case 'modal':
		$tpl->load_template('devcraft/tags_add/modal.tpl');
		$tpl->compile('content');
		echo $tpl->result['content'];
		break;
	case 'css':
		echo '<link rel="stylesheet" href="' . htmlspecialchars($modUrl . '/tags_add.css', ENT_QUOTES, 'UTF-8') . '">' . "\n";
		break;
	case 'js':
		echo '<meta name="dc-ajax-base" content="' . htmlspecialchars($ajaxUrl, ENT_QUOTES, 'UTF-8') . '">' . "\n";
		echo '<script src="' . htmlspecialchars($dcPublicUrl, ENT_QUOTES, 'UTF-8') . '"></script>' . "\n";
		$jsPath = $tpl->dir . '/devcraft/tags_add/tags_add.js';
		if(is_file($jsPath)) {
			echo "<script>\n" . file_get_contents($jsPath) . "\n</script>\n";
		}
		break;
	case 'button':
	default:
		$tpl->load_template('devcraft/tags_add/button.tpl');
		$tpl->compile('content');
		echo $tpl->result['content'];
		break;
}
