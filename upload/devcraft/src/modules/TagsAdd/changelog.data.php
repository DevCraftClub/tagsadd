<?php

declare(strict_types=1);

return [
	[
		'version' => '200.3.0',
		'date'    => '2026-07-17',
		'changes' => [
			'added'   => [
				__('Каркас TagsAdd для DevCraft Admin и DLE 20.0.'),
				__('Очередь предложений тегов, модерация approve/reject, инкрементальная вставка в post.tags/_tags или xfield.'),
				__('Публичный suggest через dc_public.js и controller=public.'),
				__('Шаблоны темы Default/devcraft/tags_add и настройки с PM-шаблонами.'),
				__('Стандартная причина отклонения (decline_reason_default) и Metro-диалоги отклонения/удаления.'),
				__('В списке предложений: имена пользователей и заголовки новостей со ссылками/иконками.'),
			],
			'changed' => [
				__('Плейсхолдеры PM: {suggested_tags}, {moderate_suggested_tags}, {decline_reason}; mail_from — select.'),
			],
			'fixed'   => [
				__('Отмена отклонения больше не удаляет запись; HTML-ссылки в шаблонах PM после двойного экранирования.'),
			],
			'removed' => [
				__('Legacy Semantic UI, arcticModal, tokenfield, maharder AJAX и доверие к userid из POST.'),
			],
		],
	],
];
