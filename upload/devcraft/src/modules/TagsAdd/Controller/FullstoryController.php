<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Controller;

use DevCraft\Core\Support\DataManager;
use DevCraft\Modules\TagsAdd\Services\ConfigNormalizer;

/**
 * Публичная кнопка / окно предложения тегов на полной новости.
 */
final class FullstoryController {

	/**
	 * Рендерит фрагмент темы по параметру focus.
	 *
	 * CSS и JS сайта — через siteAssets манифеста (теги `{devcraft}` / `{devcraft-header}` / `{devcraft-scripts}`).
	 * `focus=css` и `focus=js` оставлены пустыми, чтобы старые include не дублировали файлы.
	 *
	 * @param   string  $focus  button|modal|css|js
	 */
	public function render(int $newsId, string $focus = 'button'): string {
		global $tpl, $is_logged, $member_id, $config, $dle_login_hash;

		if($newsId <= 0) {
			return '';
		}

		$cfg         = (new ConfigNormalizer())->normalize(DataManager::getConfig('tags_add', NULL, 'tagsadd'));
		$buttonLabel = trim((string) ($cfg['button_label'] ?? ''));
		$allowGuests = !empty($cfg['allow_guests']);

		if($buttonLabel === '') {
			$buttonLabel = __('Предложить теги');
		}

		$logged = !empty($is_logged) && !empty($member_id['user_id']);

		if(!$logged && !$allowGuests) {
			return '';
		}

		if(!isset($tpl) || !is_object($tpl)) {
			if(!class_exists('dle_template', false)) {
				require_once \DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php');
			}

			$tpl       = new \dle_template();
			$tpl->dir  = ROOT_DIR . '/templates/' . (string) ($config['skin'] ?? 'Default');
		}

		$skin = totranslit((string) ($config['skin'] ?? 'Default'), false, false);

		if(!is_dir(ROOT_DIR . '/templates/' . $skin . '/devcraft/tags_add')) {
			$skin = 'Default';
		}

		$restoreDir = $tpl->dir;
		$tpl->dir   = ROOT_DIR . '/templates/' . $skin;

		$tpl->set('{news-id}', (string) $newsId);
		$tpl->set('{button-label}', htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8'));
		$tpl->set('{tags-label}', __('Теги (через запятую)'));
		$tpl->set('{user-hash}', htmlspecialchars((string) ($dle_login_hash ?? ''), ENT_QUOTES, 'UTF-8'));

		$html = '';

		try {
			switch($focus) {
				case 'modal':
					$tpl->result['content'] = '';
					$tpl->load_template('devcraft/tags_add/modal.tpl');
					$tpl->compile('content');
					$html = (string) ($tpl->result['content'] ?? '');
					break;
				case 'css':
				case 'js':
					break;
				case 'button':
				default:
					$tpl->result['content'] = '';
					$tpl->load_template('devcraft/tags_add/button.tpl');
					$tpl->compile('content');
					$html = (string) ($tpl->result['content'] ?? '');
					break;
			}
		} finally {
			$tpl->dir = $restoreDir;
		}

		return $html;
	}

}
