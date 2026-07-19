<?php

declare(strict_types=1);

/**
 * Схема фильтрации и сортировки страницы предложений тегов TagsAdd.
 *
 * Гидрируется в `FilterSchema` через `FilterSchema::fromArray()` — сам файл
 * возвращает массив в форме, ожидаемой этим методом.
 *
 * @return array{
 *     sort: array{default: string, columns: array<string, string>},
 *     sections: list<array{title: string, fields: list<array{id: string, type: string, label: string, metro?: array<string, mixed>}>}>,
 * }
 */
return [
	'sort'     => [
		'default' => 'date',
		'columns' => [
			'id'      => '#',
			'date'    => __('Дата'),
			'news_id' => __('Новость'),
			'user_id' => __('Пользователь'),
		],
	],
	'sections' => [
		[
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
		],
	],
];
