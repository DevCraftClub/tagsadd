<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd;

use DevCraft\Core\Abstracts\AbstractModuleIdentity;

/**
 * Identity модуля TagsAdd.
 *
 * MODULE/CODE = DLE mod (`engine/inc/tags_add.php` → `?mod=tags_add`).
 * Каталог модуля: `devcraft/src/modules/TagsAdd/`.
 */
final class TagsAddIdentity extends AbstractModuleIdentity {

	public const string MODULE = 'tags_add';

	public const string CODE = 'tags_add';

}
