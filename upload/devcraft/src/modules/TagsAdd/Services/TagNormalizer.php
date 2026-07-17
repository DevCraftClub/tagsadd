<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Services;

/**
 * Нормализация и сравнение тегов.
 */
final class TagNormalizer {

	/**
	 * @return list<string>
	 */
	public function parse(string $raw): array {
		$parts  = preg_split('/[,;]+/u', $raw)? : [];
		$result = [];
		$seen   = [];

		foreach($parts as $part) {
			$tag = trim($part);
			$tag = preg_replace('/\s+/u', ' ', $tag) ?? $tag;

			if($tag === '') {
				continue;
			}

			$key = mb_strtolower($tag);

			if(isset($seen[$key])) {
				continue;
			}

			$seen[$key] = true;
			$result[]   = $tag;
		}

		return $result;
	}

	/**
	 * @param   list<string>  $tags
	 */
	public function toCsv(array $tags): string {
		return implode(',', $tags);
	}

	/**
	 * @param   list<string>  $existing
	 * @param   list<string>  $proposed
	 *
	 * @return list<string>
	 */
	public function missing(array $existing, array $proposed): array {
		$map = [];

		foreach($existing as $tag) {
			$map[mb_strtolower(trim($tag))] = true;
		}

		$out = [];

		foreach($proposed as $tag) {
			$key = mb_strtolower(trim($tag));

			if($key === '' || isset($map[$key])) {
				continue;
			}

			$map[$key] = true;
			$out[]     = $tag;
		}

		return $out;
	}

}
