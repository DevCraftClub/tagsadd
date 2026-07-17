<?php

declare(strict_types=1);

/**
 * Рендер кнопки / модалки / assets TagsAdd для fullstory.
 *
 * {include file="engine/modules/devcraft/tags_add.php?newsid={news-id}&focus=button|modal|js|css"}
 */

if(!defined('DATALIFEENGINE')) {
	die('Hacking attempt!');
}

$focus  = isset($focus) ? (string) $focus : 'button';
$newsid = isset($newsid) ? (int) $newsid : (isset($news_id) ? (int) $news_id : 0);

if($newsid <= 0) {
	return;
}

$configPath  = ROOT_DIR . '/devcraft/config/tags_add.json';
$buttonLabel = 'Предложить теги';
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

$skin   = (string) ($config['skin'] ?? 'Default');
$tplDir = ROOT_DIR . '/templates/' . $skin . '/devcraft/tags_add';

if(!is_dir($tplDir)) {
	$skin   = 'Default';
	$tplDir = ROOT_DIR . '/templates/Default/devcraft/tags_add';
}

$themeUrl = rtrim((string) ($config['http_home_url'] ?? '/'), '/') . '/templates/' . $skin;
$homeUrl  = rtrim((string) ($config['http_home_url'] ?? '/'), '/') . '/';
$modUrl   = $themeUrl . '/devcraft/tags_add';

$replacements = [
	'{news-id}'      => (string) $newsid,
	'{button-label}' => htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8'),
	'{THEME}'        => $themeUrl,
	'{MOD_URL}'      => $modUrl,
	'{HOME}'         => $homeUrl,
	'{AJAX}'         => $homeUrl . 'devcraft/ajax.php',
	'{DC_PUBLIC}'    => $homeUrl . 'devcraft/src/templates/core/assets/js/dc_public.js',
	'{user-hash}'    => htmlspecialchars((string) ($dle_login_hash ?? ''), ENT_QUOTES, 'UTF-8'),
];

$renderTpl = static function (string $file) use ($tplDir, $replacements): string {
	$path = $tplDir . '/' . $file;

	if(!is_file($path)) {
		return '';
	}

	return strtr((string) file_get_contents($path), $replacements);
};

switch($focus) {
	case 'modal':
		echo $renderTpl('modal.tpl');
		break;
	case 'css':
		echo '<link rel="stylesheet" href="' . htmlspecialchars($modUrl . '/tags_add.css', ENT_QUOTES, 'UTF-8') . '">' . "\n";
		break;
	case 'js':
		echo '<meta name="dc-ajax-base" content="' . htmlspecialchars($replacements['{AJAX}'], ENT_QUOTES, 'UTF-8') . '">' . "\n";
		echo '<script src="' . htmlspecialchars($replacements['{DC_PUBLIC}'], ENT_QUOTES, 'UTF-8') . '"></script>' . "\n";
		// JS лежит в templates/.../devcraft/tags_add/, но прямой HTTP к .js там даёт 403 (правила темы/openresty).
		$jsPath = $tplDir . '/tags_add.js';
		if(is_file($jsPath)) {
			echo "<script>\n" . (string) file_get_contents($jsPath) . "\n</script>\n";
		}
		break;
	case 'button':
	default:
		echo $renderTpl('button.tpl');
		break;
}
