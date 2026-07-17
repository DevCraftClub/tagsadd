<?php

declare(strict_types=1);

use DevCraft\Types\AdminLink;
use DevCraft\Modules\TagsAdd\Pages\DashboardPage;
use DevCraft\Modules\TagsAdd\Pages\SuggestionsPage;
use DevCraft\Modules\TagsAdd\Pages\EditSuggestionPage;
use DevCraft\Modules\TagsAdd\Pages\SettingsPage;
use DevCraft\Modules\TagsAdd\Pages\TemplatesPage;
use DevCraft\Modules\TagsAdd\Pages\ChangelogPage;
use DevCraft\Modules\TagsAdd\Ajax\SettingsHandler;
use DevCraft\Modules\TagsAdd\Ajax\ApproveHandler;
use DevCraft\Modules\TagsAdd\Ajax\RejectHandler;
use DevCraft\Modules\TagsAdd\Ajax\DeleteHandler;
use DevCraft\Modules\TagsAdd\Ajax\SaveSuggestionHandler;
use DevCraft\Modules\TagsAdd\Ajax\BulkModerationHandler;
use DevCraft\Modules\TagsAdd\Ajax\SuggestHandler;

/**
 * Манифест модуля TagsAdd.
 */
return [
	'mod'  => 'tags_add',
	'code' => 'tags_add',
	'meta' => [
		'name'        => 'TagsAdd',
		'version'     => '200.3.0',
		'description' => __('Предложение тегов к новостям и модерация очереди'),
		'icon'        => 'mif-price-tags',
		'docsLink'    => 'https://readme.devcraft.club/',
		'siteLink'    => 'https://devcraft.club/',
		'siteId'      => 0,
		'author'      => [
			'name'     => 'Maxim Harder',
			'contacts' => [
				['name' => __('E-Mail'), 'link' => 'mailto:dev@devcraft.club'],
				['name' => __('Telegram'), 'link' => 'https://t.me/MaHarder'],
			],
		],
	],
	'menu' => [
		AdminLink::page(__('Главная'), 'dashboard', DashboardPage::class, 'mif-home', 'tags_add'),
		AdminLink::page(__('Предложения'), 'suggestions', SuggestionsPage::class, 'mif-list', 'tags_add'),
		AdminLink::hidden('edit', EditSuggestionPage::class),
		AdminLink::page(__('Подключение в шаблоны'), 'templates', TemplatesPage::class, 'mif-files-empty', 'tags_add'),
		AdminLink::page(__('Настройки'), 'settings', SettingsPage::class, 'mif-cog', 'tags_add'),
		AdminLink::page(__('Журнал изменений'), 'changelog', ChangelogPage::class, 'mif-library', 'tags_add'),
	],
	'ajax' => [
		'controller' => 'admin',
		'methods'    => [
			'settings'         => SettingsHandler::class,
			'approve'          => ApproveHandler::class,
			'reject'           => RejectHandler::class,
			'delete'           => DeleteHandler::class,
			'save_suggestion'  => SaveSuggestionHandler::class,
			'bulk_moderation'  => BulkModerationHandler::class,
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
