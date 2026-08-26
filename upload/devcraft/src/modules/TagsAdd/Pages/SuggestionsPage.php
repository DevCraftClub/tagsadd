<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Pages;

use DevCraft\Modules\TagsAdd\TagsAddIdentity;

use DLEPlugins;
use DevCraft\Core\Application;
use DevCraft\Types\FilterSchema;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Admin\FilterFormService;
use DevCraft\Core\Support\ParseTemplateTags;
use DevCraft\Modules\TagsAdd\Models\TagSuggestion;
use DevCraft\Modules\TagsAdd\Repositories\TagSuggestionRepository;
use DevCraft\Core\Support\DleDataService;

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
		$sort          = strtoupper((string) ($query['sort'] ?? 'DESC'));
		$perPage       = FilterFormService::resolveListCount();
		$page          = max(1, (int) ($query['page'] ?? 1));
		$rules         = $filterService->parseRules($query);
		$criteria      = $filterService->rulesToCriteria($rules, $schema);

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

		$newsIds = [];
		$userIds = [];

		foreach($result['items'] as $item) {
			/** @var TagSuggestion $item */
			if($item->news_id > 0) {
				$newsIds[$item->news_id] = $item->news_id;
			}

			if($item->user_id > 0) {
				$userIds[$item->user_id] = $item->user_id;
			}
		}

		$newsById = $this->loadNewsMap(array_values($newsIds));
		$userById = $this->loadUserNameMap(array_values($userIds));

		$rows = [];

		foreach($result['items'] as $item) {
			/** @var TagSuggestion $item */
			$news = $newsById[$item->news_id] ?? [];

			if($item->user_id <= 0) {
				$userName = __('Гость');
			} else {
				$userName = $userById[$item->user_id] ?? ('#' . $item->user_id);
			}

			$newsTitle = $news !== []
				? stripslashes((string) ($news['title'] ?? ''))
				: '';

			if($newsTitle === '') {
				$newsTitle = '#' . $item->news_id;
			}

			$rows[] = [
				'id'            => $item->id(),
				'news_id'       => $item->news_id,
				'news_title'    => $newsTitle,
				'news_view_url' => $news !== []? ParseTemplateTags::fullLink($news) : '#',
				'news_edit_url' => '?mod=editnews&action=editnews&id=' . $item->news_id,
				'user_id'       => $item->user_id,
				'user_name'     => $userName,
				'user_edit_url' => $item->user_id > 0
					? '?mod=editusers&action=edituser&id=' . $item->user_id
					: '',
				'tags'          => $item->tags,
				'date'          => $item->date instanceof \DateTimeInterface
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
	 * @param   list<int>  $ids
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function loadNewsMap(array $ids): array {
		global $db;

		if($ids === []) {
			return [];
		}

		$safe = implode(',', array_map('intval', $ids));
		$db->query(
			'SELECT id, title, alt_name, category, date FROM ' . PREFIX . "_post WHERE id IN ({$safe})",
		);

		$map = [];

		while($row = $db->get_row()) {
			$map[(int) $row['id']] = $row;
		}

		return $map;
	}

	/**
	 * @param   list<int>  $ids
	 *
	 * @return array<int, string>
	 */
	private function loadUserNameMap(array $ids): array {
		$map = [];

		foreach($ids as $id) {
			$user     = DleDataService::user(id: $id);
			$name     = trim((string) ($user['name'] ?? ''));
			$map[$id] = $name !== ''? $name : ('#' . $id);
		}

		return $map;
	}

	/**
	 * @param   array<string, mixed>  $query
	 *
	 * @return array<int, string>
	 */
	private function buildPageUrls(array $query, int $totalPages): array {
		$urls = [];

		for($page = 1; $page <= $totalPages; $page++) {
			$params      = array_merge($query, [
				'mod'    => TagsAddIdentity::mod(),
				'action' => 'suggestions',
				'page'   => $page,
			]);
			$urls[$page] = http_build_query($params);
		}

		return $urls;
	}

	private function loadFilterSchema(): FilterSchema {
		/** @var array<string, mixed> $raw */
		$raw = require DLEPlugins::Check(__DIR__ . '/../Filter/filter.schema.php');

		return FilterSchema::fromArray($raw);
	}

}
