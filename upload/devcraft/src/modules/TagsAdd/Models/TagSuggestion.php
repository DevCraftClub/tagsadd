<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Models;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use DevCraft\Modules\TagsAdd\Repositories\TagSuggestionRepository;

/**
 * Предложение тегов в очереди модерации (`{prefix}_tags_add`).
 *
 * Схема создаётся Cycle ORM по этой модели (миграции DevCraft).
 */
#[Entity(role: 'tag_suggestion', repository: TagSuggestionRepository::class, table: 'tags_add')]
#[Index(columns: ['news_id'], name: 'idx_tags_add_news_id')]
#[Index(columns: ['date'], name: 'idx_tags_add_date')]
class TagSuggestion {

	#[Column(type: 'primary')]
	public int $id;

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $news_id = 0;

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $user_id = 0;

	#[Column(type: 'text')]
	public string $tags = '';

	#[Column(type: 'datetime')]
	public \DateTimeImmutable $date;

	public function __construct() {
		$this->date = new \DateTimeImmutable();
	}

	public function getColumnVal(string $name): mixed {
		return match ($name) {
			'id'      => $this->id,
			'news_id' => $this->news_id,
			'user_id' => $this->user_id,
			'tags'    => $this->tags,
			'date'    => $this->date,
			default   => NULL,
		};
	}

}
