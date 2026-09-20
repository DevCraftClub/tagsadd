<?php

declare(strict_types=1);

use DevCraft\Builders\FilterSchemaBuilder;
use DevCraft\Types\FormSection;

/**
 * Схема фильтрации и сортировки страницы предложений тегов TagsAdd.
 *
 * @return \DevCraft\Types\FilterSchema
 */
return FilterSchemaBuilder::create()
	->defaultOrder('date')
	->sortColumns([
		'id'      => '#',
		'date'    => __('Дата'),
		'news_id' => __('Новость'),
		'user_id' => __('Пользователь'),
	])
	->addSection(FormSection::fromArray([
		'title'  => __('Фильтр'),
		'fields' => [
			[
				'id'    => 'news_id',
				'type'  => 'text',
				'label' => __('ID новости'),
				'metro' => ['db_column' => 'news_id'],
			],
			[
				'id'    => 'user_id',
				'type'  => 'text',
				'label' => __('ID пользователя'),
				'metro' => ['db_column' => 'user_id'],
			],
			[
				'id'    => 'tags',
				'type'  => 'text',
				'label' => __('Теги'),
				'metro' => ['db_column' => 'tags'],
			],
		],
	]))
	->build();
