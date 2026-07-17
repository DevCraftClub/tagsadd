<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Ajax;

use DevCraft\Core\Application;
use DevCraft\Core\Config\Paths;
use DevCraft\Core\Http\AjaxRequest;
use DevCraft\Core\Http\JsonResponse;
use DevCraft\Core\Support\DataManager;
use DevCraft\Core\Config\DevCraftConfig;
use DevCraft\Core\Admin\SettingsFormService;
use DevCraft\Core\Interfaces\ResponseInterface;
use DevCraft\Core\Interfaces\AjaxHandlerInterface;
use DevCraft\Modules\TagsAdd\Services\ConfigNormalizer;

/**
 * Сохранение настроек TagsAdd.
 */
final class SettingsHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		$plugin = Application::instance()->registry()->forMod($request->mod);
		$schema = $plugin?->settingsSchema();

		if($schema === NULL) {
			return JsonResponse::fail(__('Ошибка'), __('Схема настроек недоступна'), 'validation');
		}

		$configDir  = Paths::config();
		$configFile = $configDir . '/' . $schema->codename . '.json';

		if(is_file($configFile) && !is_writable($configFile)) {
			return JsonResponse::fail(__('Ошибка'), __('Файл конфигурации недоступен для записи'), 'validation', 500);
		}

		if(!is_dir($configDir) && !DataManager::createDir($configDir)) {
			return JsonResponse::fail(__('Ошибка'), __('Каталог конфигурации недоступен для записи'), 'validation', 500);
		}

		$service    = new SettingsFormService();
		$result     = $service->validatePartial($request->data, $schema);
		$normalizer = new ConfigNormalizer();

		if($result['valid'] === [] && $result['errors'] !== []) {
			return JsonResponse::fail(__('Ошибка'), __('Все поля недействительны'), 'validation', 422, ['fields' => $result['errors']]);
		}

		if($result['valid'] !== []) {
			$existing = $normalizer->normalize(DataManager::getConfig($schema->codename, NULL, 'tagsadd'));
			$merged   = $normalizer->normalize(array_merge($existing, $result['valid']));
			DataManager::saveConfig($schema->codename, $merged);
			DevCraftConfig::resetCache();
		}

		if(function_exists('clear_cache')) {
			clear_cache();
		}

		if($result['errors'] !== []) {
			return JsonResponse::notify(__('Внимание'),
				__('Частичное сохранение завершено с ошибками в полях'),
				JsonResponse::TYPE_WARNING,
				[],
				422,
				false,
				[
					'code'    => 'validation',
					'message' => __('Частичное сохранение завершено с ошибками в полях'),
					'title'   => __('Внимание'),
					'fields'  => $result['errors'],
				]);
		}

		return JsonResponse::toast(__('Сохранено'), ['saved' => true]);
	}

}
