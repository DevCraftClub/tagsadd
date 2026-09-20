<?php

declare(strict_types=1);

use DevCraft\Types\AdminLink;
use DevCraft\Types\ModuleManifest;
use DevCraft\Builders\ModuleManifestBuilder;
use DevCraft\Builders\ModuleAjaxConfigBuilder;
use DevCraft\Builders\ModuleAssetsBuilder;
use DevCraft\Builders\ModuleSiteAssetsBuilder;
use DevCraft\Modules\TagsAdd\TagsAddIdentity;
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
 * @return ModuleManifest
 */
return ModuleManifestBuilder::create()
	->mod(TagsAddIdentity::mod())
	->code(TagsAddIdentity::code())
	->name('TagsAdd')
	->version('200.3.1')
	->description(__('Предложение тегов к новостям и модерация очереди'))
	->icon('mif-price-tags')
	->docsLink('https://readme.devcraft.club/dev/dle/usertags/200.3.1/getting_started')
	->siteLink('https://devcraft.club/downloads/polzovatelskie-tegi.12/')
	->siteId(12)
	->menu([
		AdminLink::page(__('Главная'), 'dashboard', DashboardPage::class, 'mif-home', TagsAddIdentity::mod()),
		AdminLink::page(__('Предложения'), 'suggestions', SuggestionsPage::class, 'mif-list', TagsAddIdentity::mod()),
		AdminLink::hidden('edit', EditSuggestionPage::class),
		AdminLink::page(__('Подключение в шаблоны'), 'templates', TemplatesPage::class, 'mif-files-empty', TagsAddIdentity::mod()),
		AdminLink::page(__('Настройки'), 'settings', SettingsPage::class, 'mif-cog', TagsAddIdentity::mod()),
		AdminLink::page(__('Журнал изменений'), 'changelog', ChangelogPage::class, 'mif-library', TagsAddIdentity::mod()),
	])
	->ajax(
		ModuleAjaxConfigBuilder::create('admin')
			->methods([
				'settings'        => SettingsHandler::class,
				'approve'         => ApproveHandler::class,
				'reject'          => RejectHandler::class,
				'delete'          => DeleteHandler::class,
				'save_suggestion' => SaveSuggestionHandler::class,
				'bulk_moderation' => BulkModerationHandler::class,
			])
			->publicMethod('suggest', SuggestHandler::class, true)
	)
	->changelog(require DLEPlugins::Check(__DIR__ . '/changelog.data.php'))
	->assets(
		ModuleAssetsBuilder::create()
			->js('tags_add.js')
	)
	->siteAssets(
		ModuleSiteAssetsBuilder::create()
			->css('tags_add.css')
			->js(
				'tags_add_site.js',
				dependsOn: ['devcraft/src/templates/core/assets/js/dc_public.js'],
				available: ['main', 'showfull', 'lastnews', 'tags', 'allnews'],
			)
	)
	->build(__DIR__);
