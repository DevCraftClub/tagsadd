<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Ajax;

use DevCraft\Core\Http\AjaxRequest;
use DevCraft\Core\Http\JsonResponse;
use DevCraft\Core\Interfaces\ResponseInterface;
use DevCraft\Core\Interfaces\AjaxHandlerInterface;
use DevCraft\Modules\TagsAdd\Services\ModerationService;

final class BulkModerationHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		$action = (string) ($request->data['action'] ?? '');
		$ids    = $request->data['ids'] ?? [];
		$reason = trim((string) ($request->data['reason'] ?? ''));

		if(!is_array($ids) || $ids === []) {
			return JsonResponse::fail(__('Ошибка'), __('Не выбраны записи'), 'validation');
		}

		$count = (new ModerationService())->bulk($action, array_map('intval', $ids), $reason);

		return JsonResponse::toast(__('Обработано: {n}', ['{n}' => (string) $count]), ['count' => $count]);
	}

}
