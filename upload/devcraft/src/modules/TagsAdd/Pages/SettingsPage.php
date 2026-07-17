<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Pages;

use DLEPlugins;
use DevCraft\Core\Application;
use DevCraft\Core\Config\Paths;
use DevCraft\Core\Support\DataManager;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Interfaces\SettingsPageInterface;
use DevCraft\Modules\TagsAdd\Services\ConfigNormalizer;

/**
 * Настройки TagsAdd.
 */
final class SettingsPage extends AbstractPage implements SettingsPageInterface {

	public function handle(): array {
		global $config, $dle_login_hash;

		$this->addBreadcrumb(__('Настройки'));

		$normalizer = new ConfigNormalizer();
		$configFile = Paths::config() . '/tags_add.json';

		if(!is_file($configFile)) {
			DataManager::saveConfig('tags_add', $normalizer->normalize(DataManager::getConfig('tags_add', NULL, 'tagsadd')));
		}

		$dleHome = rtrim((string) ($config['http_home_url'] ?? '/'), '/') . '/';

		return [
			'view' => 'tagsadd/settings.twig',
			'data' => [
				'page_title'       => __('Настройки'),
				'dle_home'         => $dleHome,
				'dle_skin'         => (string) ($config['skin'] ?? 'Default'),
				'pm_wysiwyg'       => !empty($config['allow_pm_wysiwyg']),
				'pm_editor_script' => $this->buildPmEditorScript(),
				'dle_login_hash'   => (string) ($dle_login_hash ?? ''),
			],
		];
	}

	public function supplementFormData(): array {
		$dleData = Application::instance()->dleData();
		$empty   = ['' => __('— не выбрано —')];

		$userOptions = $empty;

		foreach($dleData->users() as $row) {
			$name = trim((string) ($row['name'] ?? ''));

			if($name === '') {
				continue;
			}

			$email              = trim((string) ($row['email'] ?? ''));
			$userOptions[$name] = $email !== ''? $name . ' <' . $email . '>' : $name;
		}

		$userXfieldOptions = $empty;

		foreach($dleData->userXfields() as $name => $meta) {
			$key = is_string($name)? $name : (string) (is_array($meta)? ($meta['name'] ?? '') : '');

			if($key === '') {
				continue;
			}

			$label                   = is_array($meta)? (string) ($meta['description'] ?? $key) : $key;
			$userXfieldOptions[$key] = $label !== ''? $label : $key;
		}

		$postXfieldOptions = $empty;

		foreach($dleData->postXfields() as $name => $meta) {
			$key = is_string($name)? $name : (string) (is_array($meta)? ($meta['name'] ?? '') : '');

			if($key === '') {
				continue;
			}

			$label                   = is_array($meta)? (string) ($meta['description'] ?? $key) : $key;
			$postXfieldOptions[$key] = $label !== ''? $label : $key;
		}

		return [
			'admin_name'        => $userOptions,
			'mail_from'         => $userOptions,
			'user_inform_field' => $userXfieldOptions,
			'xfield_name'       => $postXfieldOptions,
		];
	}

	private function buildPmEditorScript(): string {
		global $config, $member_id, $user_group, $lang, $tpl;

		if(empty($config['allow_pm_wysiwyg'])) {
			return '';
		}

		if(!is_array($member_id ?? NULL)) {
			$member_id = ['user_group' => 1, 'user_id' => 1, 'name' => ''];
		}

		if(!is_array($user_group ?? NULL) || $user_group === []) {
			$user_group = [
				1 => ['allow_url' => 1, 'allow_image' => 1, 'group_name' => 'Admin'],
			];
		}

		if(!is_array($lang ?? NULL)) {
			$lang = ['language_code' => 'ru', 'direction' => 'ltr'];
		} else {
			$lang['language_code'] = $lang['language_code'] ?? 'ru';
			$lang['direction']     = $lang['direction'] ?? 'ltr';
		}

		if(!isset($tpl) || !is_object($tpl)) {
			if(!class_exists('dle_template', false)) {
				require_once DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php');
			}
			$tpl             = new \dle_template();
			$tpl->smartphone = false;
			$tpl->tablet     = false;
		}

		$is_pm_ajax_mode        = true;
		$comments_mobile_editor = false;

		/** @noinspection PhpIncludeInspection */
		include DLEPlugins::Check(ENGINE_DIR . '/editor/pm.php');

		return isset($editor_scrips)? (string) $editor_scrips : '';
	}

}
