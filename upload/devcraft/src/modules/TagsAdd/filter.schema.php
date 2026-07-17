<?php

declare(strict_types=1);

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
