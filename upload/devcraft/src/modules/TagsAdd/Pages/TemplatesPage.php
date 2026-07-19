<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Pages;

use DevCraft\Core\Abstracts\AbstractPage;

/**
 * Инструкция по подключению TagsAdd в шаблоны сайта.
 */
final class TemplatesPage extends AbstractPage {

	public function handle(): array {
		$pageName = __('Подключение в шаблоны');
		$this->addBreadcrumb($pageName);

		return [
			'view' => 'tagsadd/templates.twig',
			'data' => [
				'page_title' => $pageName,
			],
		];
	}

}
