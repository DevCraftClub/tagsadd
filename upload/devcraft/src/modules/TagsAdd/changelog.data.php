<?php

declare(strict_types=1);

use DevCraft\Builders\ChangelogBuilder;

/**
 * Журнал изменений TagsAdd.
 *
 * @return list<\DevCraft\Types\Changelog>
 */
return [
	ChangelogBuilder::create('200.3.1')
		->date('2026-09-20')
		->fixed([
			__('Фатал undefined __() на фронте при include tags_add.php без DevCraft bootstrap — добавлен fallback.'),
			__('XLIFF TagsAdd перенесён из locales/ru/ в locales/ru_RU/.'),
		])
		->changed([
			__('Публичный include: Controller/show_tags_add.php от корня сайта.'),
			__('CSS и JS сайта — siteAssets (Public/tags_add.css, tags_add_site.js); include focus=css/js пустые.'),
			__('У публичного JS — зависимость от dc_public.js и разделы main / showfull / lastnews / tags / allnews (только если записи ещё нет в БД).'),
			__('install.xml: иконка — путь к Public/icon.png, allow_groups 1,2, notice — сайт автора и документация.'),
			__('Фильтр очереди через FilterSchemaBuilder; заголовки новостей через QueryBuilder.'),
			__('Манифест через ModuleManifestBuilder, TagsAddIdentity, публичный suggest через publicMethod.'),
			__('Фронт подгружает конфиг через DataManager после DevCraft bootstrap.'),
			__('Рендер button/modal через нативный $tpl и шаблоны темы.'),
			__('Блок author в manifest убран — используется дефолт ModuleManifest.'),
		])
		->removed([
			__('Файл engine/modules/devcraft/tags_add.php — в теме только Controller/show_tags_add.php.'),
			__('CSS и JS модуля в templates/*/devcraft/tags_add/.'),
			__('Автопосев tags_add.json при открытии настроек.'),
		])
		->build(),
	ChangelogBuilder::create('200.3.0')
		->date('2026-07-17')
		->added([
			__('Каркас TagsAdd для DevCraft Admin и DLE 20.0.'),
			__('Очередь предложений тегов, модерация approve/reject, инкрементальная вставка в post.tags/_tags или xfield.'),
			__('Публичный suggest через dc_public.js и controller=public.'),
			__('Шаблоны темы Default/devcraft/tags_add и настройки с PM-шаблонами.'),
			__('Стандартная причина отклонения (decline_reason_default) и Metro-диалоги отклонения/удаления.'),
			__('В списке предложений: имена пользователей и заголовки новостей со ссылками/иконками.'),
		])
		->changed([
			__('Плейсхолдеры PM: {suggested_tags}, {moderate_suggested_tags}, {decline_reason}; mail_from — select.'),
		])
		->fixed([
			__('Отмена отклонения больше не удаляет запись; HTML-ссылки в шаблонах PM после двойного экранирования.'),
		])
		->removed([
			__('Legacy Semantic UI, arcticModal, tokenfield, maharder AJAX и доверие к userid из POST.'),
		])
		->build(),
];
