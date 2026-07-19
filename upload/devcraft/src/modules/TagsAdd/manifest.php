<?php

declare(strict_types=1);

use DevCraft\Types\AdminLink;
use DevCraft\Modules\TagsAdd\Pages\SettingsPage;
use DevCraft\Modules\TagsAdd\Ajax\RejectHandler;
use DevCraft\Modules\TagsAdd\Ajax\DeleteHandler;
use DevCraft\Modules\TagsAdd\Pages\DashboardPage;
use DevCraft\Modules\TagsAdd\Pages\TemplatesPage;
use DevCraft\Modules\TagsAdd\Pages\ChangelogPage;
use DevCraft\Modules\TagsAdd\Ajax\ApproveHandler;
use DevCraft\Modules\TagsAdd\Ajax\SuggestHandler;
use DevCraft\Modules\TagsAdd\Ajax\SettingsHandler;
use DevCraft\Modules\TagsAdd\Pages\SuggestionsPage;
use DevCraft\Modules\TagsAdd\Pages\EditSuggestionPage;
use DevCraft\Modules\TagsAdd\Ajax\SaveSuggestionHandler;
use DevCraft\Modules\TagsAdd\Ajax\BulkModerationHandler;

/**
 * Манифест модуля TagsAdd.
 *
 * Гидрируется в `ModuleManifest` через `ModuleManifest::fromManifest()` — сам
 * файл возвращает массив в форме, ожидаемой этим методом.
 *
 * @return array{
 *     mod: string,
 *     code?: string,
 *     meta?: array<string, mixed>,
 *     menu?: list<AdminLink>,
 *     ajax?: array{controller?: string, methods?: array<string, class-string>},
 *     changelog?: array<int, array<string, mixed>>,
 *     assets?: array<string, list<string>>,
 * }
 */
return [
	'mod'       => 'tags_add',
	'code'      => 'tags_add',
	'meta'      => [
		'name'        => 'TagsAdd',
		'version'     => '200.3.0',
		'description' => __('Предложение тегов к новостям и модерация очереди'),
		'icon'        => 'mif-price-tags',
		'docsLink'    => 'https://readme.devcraft.club/dev/usertags/',
		'siteLink'    => 'https://devcraft.club/downloads/polzovatelskie-tegi.12/',
		'siteId'      => 12,
	],
	'menu'      => [
		AdminLink::page(__('Главная'), 'dashboard', DashboardPage::class, 'mif-home', 'tags_add'),
		AdminLink::page(__('Предложения'), 'suggestions', SuggestionsPage::class, 'mif-list', 'tags_add'),
		AdminLink::hidden('edit', EditSuggestionPage::class),
		AdminLink::page(__('Подключение в шаблоны'), 'templates', TemplatesPage::class, 'mif-files-empty', 'tags_add'),
		AdminLink::page(__('Настройки'), 'settings', SettingsPage::class, 'mif-cog', 'tags_add'),
		AdminLink::page(__('Журнал изменений'), 'changelog', ChangelogPage::class, 'mif-library', 'tags_add'),
	],
	'ajax'      => [
		'controller' => 'admin',
		'methods'    => [
			'settings'        => SettingsHandler::class,
			'approve'         => ApproveHandler::class,
			'reject'          => RejectHandler::class,
			'delete'          => DeleteHandler::class,
			'save_suggestion' => SaveSuggestionHandler::class,
			'bulk_moderation' => BulkModerationHandler::class,
		],
		'public'     => [
			'suggest' => [
				'handler'     => SuggestHandler::class,
				'allow_guest' => true,
			],
		],
	],
	'changelog' => require DLEPlugins::Check(__DIR__ . '/changelog.data.php'),
	'assets'    => [
		'js' => ['tags_add.js'],
	],
];
