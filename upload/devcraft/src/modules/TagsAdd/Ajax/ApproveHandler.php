<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Ajax;

use DevCraft\Core\Http\AjaxRequest;
use DevCraft\Core\Http\JsonResponse;
use DevCraft\Core\Interfaces\ResponseInterface;
use DevCraft\Core\Interfaces\AjaxHandlerInterface;
use DevCraft\Modules\TagsAdd\Services\ModerationService;

final class ApproveHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		$id   = (int) ($request->data['id'] ?? 0);
		$tags = isset($request->data['tags'])? (string) $request->data['tags'] : NULL;

		try {
			(new ModerationService())->approve($id, $tags);
		} catch(\Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'moderation');
		}

		return JsonResponse::toast(__('Одобрено'), ['id' => $id]);
	}

}
