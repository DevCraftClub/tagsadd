<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Repositories;

use DevCraft\Core\Abstracts\AbstractRepository;
use DevCraft\Modules\TagsAdd\Models\TagSuggestion;

/**
 * Репозиторий предложений тегов.
 */
final class TagSuggestionRepository extends AbstractRepository {

	public function findOneById(int $id): ?TagSuggestion {
		/** @var TagSuggestion|null $entity */
		$entity = $this->select()->where('id', $id)->fetchOne();

		return $entity;
	}

}
