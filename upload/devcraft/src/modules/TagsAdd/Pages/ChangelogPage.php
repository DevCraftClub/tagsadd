<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Pages;

use DevCraft\Core\Abstracts\AbstractPage;

/**
 * Журнал изменений TagsAdd.
 */
final class ChangelogPage extends AbstractPage {

	public function handle(): array {
		$pageName = __('История изменений');
		$this->addBreadcrumb($pageName);

		return [
			'view' => 'pages/changelog.twig',
			'data' => [
				'page_title' => $pageName,
			],
		];
	}

}
