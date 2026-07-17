<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Pages;

use DLEPlugins;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Application;
use DevCraft\Core\Admin\FilterFormService;
use DevCraft\Types\FilterSchema;
use DevCraft\Modules\TagsAdd\Models\TagSuggestion;
use DevCraft\Modules\TagsAdd\Repositories\TagSuggestionRepository;

/**
 * Список предложений тегов.
 */
final class SuggestionsPage extends AbstractPage {

	public function handle(): array {
		$this->addBreadcrumb(__('Предложения'));

		$filterService = new FilterFormService();
		$query         = $filterService->parseRequestQuery();
		$schema        = $this->loadFilterSchema();
		$order         = FilterFormService::normalizeOrder(
			(string) ($query['order'] ?? $schema->defaultOrder),
			$schema,
		);
		$sort    = strtoupper((string) ($query['sort'] ?? 'DESC'));
		$perPage = FilterFormService::resolveListCount();
		$page    = max(1, (int) ($query['page'] ?? 1));
		$rules   = $filterService->parseRules($query);
		$criteria = $filterService->rulesToCriteria($rules, $schema);

		/** @var TagSuggestionRepository $repository */
		$repository = Application::instance()->database()->repository(TagSuggestion::class);
		$result     = $repository->findFiltered(
			$criteria,
			$page,
			$perPage,
			$order,
			$sort,
			$schema->sortColumnKeys(),
			$schema->defaultOrder,
		);

		$rows = [];

		foreach($result['items'] as $item) {
			/** @var TagSuggestion $item */
			$rows[] = [
				'id'      => $item->id,
				'news_id' => $item->news_id,
				'user_id' => $item->user_id,
				'tags'    => $item->tags,
				'date'    => $item->date instanceof \DateTimeInterface
					? $item->date->format('Y-m-d H:i:s')
					: (string) $item->date,
			];
		}

		$total      = (int) $result['total'];
		$totalPages = max(1, (int) ceil($total / $perPage));

		return [
			'view' => 'tagsadd/suggestions.twig',
			'data' => [
				'page_title'     => __('Предложения'),
				'items'          => $rows,
				'total'          => $total,
				'per_page'       => $perPage,
				'current_page'   => min($page, $totalPages),
				'page_urls'      => $this->buildPageUrls($query, $totalPages),
				'order'          => $order,
				'sort'           => $sort,
				'filter_rules'   => $rules,
				'filter_chips'   => $filterService->buildChipViewModel($rules, $schema),
				'filter_catalog' => $filterService->buildCatalogViewModel($schema, $repository),
				'query'          => $query,
			],
		];
	}

	/**
	 * @param array<string, mixed> $query
	 *
	 * @return array<int, string>
	 */
	private function buildPageUrls(array $query, int $totalPages): array {
		$urls = [];

		for($page = 1; $page <= $totalPages; $page++) {
			$params = array_merge($query, [
				'mod'    => 'tags_add',
				'action' => 'suggestions',
				'page'   => $page,
			]);
			$urls[$page] = http_build_query($params);
		}

		return $urls;
	}

	private function loadFilterSchema(): FilterSchema {
		/** @var array<string, mixed> $raw */
		$raw = require DLEPlugins::Check(__DIR__ . '/../filter.schema.php');

		return FilterSchema::fromArray($raw);
	}

}
