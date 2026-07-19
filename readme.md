# TagsAdd

Модуль предложения тегов к новостям для DevCraft Admin и DataLife Engine 20.0.

| | |
|---|---|
| Версия | **200.3.1** |
| Совместимость | DevCraft Admin ≥ **200.4.1**, DLE **20.0** |
| Сайт | https://devcraft.club/downloads/polzovatelskie-tegi.12/ |
| Документация | https://readme.devcraft.club/dev/usertags/ |

## Установка

1. Установите `DevCraft Admin` версии `200.4.1` или новее (нужны `dc_public.js` и `controller=public`).
2. Упакуйте содержимое каталога `upload/` в ZIP (`./install_archive.sh`).
3. Загрузите архив через менеджер плагинов DLE.
4. Выполните `composer dump-autoload` в каталоге `devcraft/`.

## Подключение в теме

В `fullstory.tpl`:

```
{include file="engine/modules/devcraft/tags_add.php?newsid={news-id}&focus=css"}
{include file="engine/modules/devcraft/tags_add.php?newsid={news-id}&focus=button"}
{include file="engine/modules/devcraft/tags_add.php?newsid={news-id}&focus=modal"}
{include file="engine/modules/devcraft/tags_add.php?newsid={news-id}&focus=js"}
```

## Ограничения

- AJAX сайта: `devcraft/ajax.php?mod=tags_add&controller=public&method=suggest`.
- Админ: `devcraft/ajax.php?mod=tags_add&controller=admin&method=…`.
- Legacy-пути `engine/inc/maharder/...` в пакет не входят.
