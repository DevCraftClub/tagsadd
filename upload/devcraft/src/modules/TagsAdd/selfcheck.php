<?php

declare(strict_types=1);

/**
 * Минимальная проверка TagNormalizer (без фреймворка).
 * Запуск: php devcraft/src/modules/TagsAdd/selfcheck.php
 */

require_once __DIR__ . '/Services/TagNormalizer.php';

use DevCraft\Modules\TagsAdd\Services\TagNormalizer;

$n = new TagNormalizer();

$parsed = $n->parse(' PHP, php ,DLE,  ,dev ');
assert($parsed === ['PHP', 'DLE', 'dev'], 'parse unique case-insensitive');

$missing = $n->missing(['PHP', 'DLE'], ['php', 'News', 'DLE']);
assert($missing === ['News'], 'missing only new tags');

$csv = $n->toCsv(['a', 'b']);
assert($csv === 'a,b', 'toCsv');

fwrite(STDOUT, "TagsAdd selfcheck OK\n");
