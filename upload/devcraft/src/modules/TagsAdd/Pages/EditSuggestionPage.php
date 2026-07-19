<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Pages;

use DevCraft\Core\Application;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Modules\TagsAdd\Models\TagSuggestion;

/**
 * Редактирование предложения.
 */
final class EditSuggestionPage extends AbstractPage {

	public function handle(): array {
		$this->addBreadcrumb(__('Предложения'), '?mod=tags_add&action=suggestions');
		$id = (int) ($_GET['id'] ?? 0);
		/** @var TagSuggestion|null $entity */
		$entity = $id > 0
			? Application::instance()->database()->repository(TagSuggestion::class)->findOneById($id)
			: NULL;

		$title = __('Редактирование');
		$this->addBreadcrumb($title);

		$item = NULL;

		if($entity !== NULL) {
			$userName = __('Гость');

			if($entity->user_id > 0) {
				$user     = Application::instance()->dleData()->user(id: $entity->user_id);
				$userName = !empty($user['name'])
					? (string) $user['name']
					: '#' . $entity->user_id;
			}

			$item = [
				'id'        => $entity->id(),
				'news_id'   => $entity->news_id,
				'user_id'   => $entity->user_id,
				'user_name' => $userName,
				'tags'      => $entity->tags,
				'date'      => $entity->date instanceof \DateTimeInterface
					? $entity->date->format('Y-m-d H:i:s')
					: (string) $entity->date,
			];
		}

		return [
			'view' => 'tagsadd/edit.twig',
			'data' => [
				'page_title' => $title,
				'item'       => $item,
			],
		];
	}

}
