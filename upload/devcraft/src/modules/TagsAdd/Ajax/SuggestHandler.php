<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Ajax;

use DevCraft\Core\Http\AjaxRequest;
use DevCraft\Core\Http\JsonResponse;
use DevCraft\Core\Interfaces\AjaxHandlerInterface;
use DevCraft\Core\Interfaces\ResponseInterface;
use DevCraft\Core\Support\DataManager;
use DevCraft\Modules\TagsAdd\Services\ConfigNormalizer;
use DevCraft\Modules\TagsAdd\Services\SuggestService;

/**
 * Публичный suggest с сайта.
 */
final class SuggestHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		global $is_logged;

		$cfg = (new ConfigNormalizer())->normalize(DataManager::getConfig('tags_add', null, 'tagsadd'));

		if(empty($is_logged) && empty($cfg['allow_guests'])) {
			return JsonResponse::fail(__('Ошибка'), __('Гостям запрещено предлагать теги'), 'auth_failed', 403);
		}

		$newsId = (int) ($request->data['news_id'] ?? $request->data['newsid'] ?? 0);
		$tags   = (string) ($request->data['tags'] ?? '');

		try {
			$entity = (new SuggestService())->suggest($newsId, $tags);
		} catch(\Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'suggest');
		}

		return JsonResponse::ok([
			'id'      => $entity->id,
			'news_id' => $entity->news_id,
		]);
	}

}
