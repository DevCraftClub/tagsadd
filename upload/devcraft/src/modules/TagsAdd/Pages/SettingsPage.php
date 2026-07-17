<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Pages;

use DLEPlugins;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Application;
use DevCraft\Core\Config\Paths;
use DevCraft\Core\Interfaces\SettingsPageInterface;
use DevCraft\Core\Support\DataManager;
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
			DataManager::saveConfig('tags_add', $normalizer->normalize(DataManager::getConfig('tags_add', null, 'tagsadd')));
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

			$email = trim((string) ($row['email'] ?? ''));
			$userOptions[$name] = $email !== '' ? $name . ' <' . $email . '>' : $name;
		}

		$userXfieldOptions = $empty;

		foreach($dleData->userXfields() as $name => $meta) {
			$key = is_string($name) ? $name : (string) (is_array($meta) ? ($meta['name'] ?? '') : '');

			if($key === '') {
				continue;
			}

			$label = is_array($meta) ? (string) ($meta['description'] ?? $key) : $key;
			$userXfieldOptions[$key] = $label !== '' ? $label : $key;
		}

		$postXfieldOptions = $empty;

		foreach($dleData->postXfields() as $name => $meta) {
			$key = is_string($name) ? $name : (string) (is_array($meta) ? ($meta['name'] ?? '') : '');

			if($key === '') {
				continue;
			}

			$label = is_array($meta) ? (string) ($meta['description'] ?? $key) : $key;
			$postXfieldOptions[$key] = $label !== '' ? $label : $key;
		}

		return [
			'admin_name'        => $userOptions,
			'user_inform_field' => $userXfieldOptions,
			'xfield_name'       => $postXfieldOptions,
		];
	}

	private function buildPmEditorScript(): string {
		global $config;

		if(empty($config['allow_pm_wysiwyg'])) {
			return '';
		}

		$is_pm_ajax_mode        = true;
		$comments_mobile_editor = false;

		/** @noinspection PhpIncludeInspection */
		include DLEPlugins::Check(ENGINE_DIR . '/editor/pm.php');

		return isset($editor_scrips) ? (string) $editor_scrips : '';
	}

}
