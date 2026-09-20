<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Services;

use DevCraft\Builders\QueryBuilder;

/**
 * Инкрементальная синхронизация тегов/xfields при одобрении.
 */
final class TagSyncService {

	private TagNormalizer $normalizer;

	public function __construct(?TagNormalizer $normalizer = NULL) {
		$this->normalizer = $normalizer ?? new TagNormalizer();
	}

	/**
	 * @param   list<string>          $proposed
	 * @param   array<string, mixed>  $config
	 *
	 * @return list<string> Фактически добавленные значения
	 */
	public function apply(int $newsId, array $proposed, array $config): array {
		global $db;

		$newsId = max(0, $newsId);

		if($newsId === 0 || $proposed === []) {
			return [];
		}

		if((string) ($config['insert_target'] ?? 'tags') === 'xfield') {
			return $this->applyXfield($newsId, $proposed, $config);
		}

		$row = QueryBuilder::create('post')
			->withColumns(['tags'])
			->withConditionsItem('id', $newsId)
			->withLimit(1)
			->first();

		if($row === []) {
			return [];
		}

		$existing = $this->normalizer->parse((string) ($row['tags'] ?? ''));
		$missing  = $this->normalizer->missing($existing, $proposed);

		if($missing === []) {
			return [];
		}

		$csv = $db->safesql($this->normalizer->toCsv(array_merge($existing, $missing)));
		$db->query('UPDATE ' . PREFIX . "_post SET tags='{$csv}' WHERE id='{$newsId}'");

		foreach($missing as $tag) {
			$safe  = $db->safesql($tag);
			$found = QueryBuilder::create('tags')
				->withColumns(['news_id'])
				->withConditions(['news_id' => $newsId, 'tag' => $tag])
				->withLimit(1)
				->first();

			if($found === []) {
				$db->query(
					'INSERT INTO ' . PREFIX . "_tags (news_id, tag) VALUES ('{$newsId}', '{$safe}')",
				);
			}
		}

		$this->clearNewsCache($newsId);

		return $missing;
	}

	/**
	 * @param   list<string>          $proposed
	 * @param   array<string, mixed>  $config
	 *
	 * @return list<string>
	 */
	private function applyXfield(int $newsId, array $proposed, array $config): array {
		global $db;

		$field = trim((string) ($config['xfield_name'] ?? ''));

		if($field === '') {
			return [];
		}

		$row = QueryBuilder::create('post')
			->withColumns(['xfields'])
			->withConditionsItem('id', $newsId)
			->withLimit(1)
			->first();

		if($row === []) {
			return [];
		}

		$parts    = ((string) ($row['xfields'] ?? '')) !== ''
			? explode('||', (string) $row['xfields'])
			: [];
		$existing = [];
		$rebuilt  = [];

		foreach($parts as $part) {
			$xf    = explode('|', $part, 2);
			$name  = (string) ($xf[0] ?? '');
			$value = (string) ($xf[1] ?? '');

			if($name === $field) {
				$existing = $this->normalizer->parse(str_replace('|', ',', $value));
				continue;
			}

			$rebuilt[] = $part;
		}

		$missing = $this->normalizer->missing($existing, $proposed);

		if($missing === []) {
			return [];
		}

		$merged    = array_merge($existing, $missing);
		$rebuilt[] = $field . '|' . implode(',', $merged);
		$xfields   = $db->safesql(implode('||', $rebuilt));
		$db->query('UPDATE ' . PREFIX . "_post SET xfields='{$xfields}' WHERE id='{$newsId}'");

		if(!empty($config['xfield_link'])) {
			$safeField = $db->safesql($field);

			foreach($missing as $tag) {
				$safe  = $db->safesql($tag);
				$found = QueryBuilder::create('xfsearch')
					->withColumns(['news_id'])
					->withConditions([
						'news_id'  => $newsId,
						'tagname'  => $field,
						'tagvalue' => $tag,
					])
					->withLimit(1)
					->first();

				if($found === []) {
					$db->query(
						'INSERT INTO ' . PREFIX
						. "_xfsearch (news_id, tagname, tagvalue) VALUES ('{$newsId}', '{$safeField}', '{$safe}')",
					);
				}
			}
		}

		$this->clearNewsCache($newsId);

		return $missing;
	}

	private function clearNewsCache(int $newsId): void {
		if(function_exists('clear_cache')) {
			clear_cache(['full_' . $newsId, 'news_' . $newsId, 'related_' . $newsId, 'stats']);
		}
	}

}
