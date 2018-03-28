![version](https://img.shields.io/badge/version-2.0-red.svg?style=flat-square "Version")
![DLE](https://img.shields.io/badge/DLE-9.X--12.x-green.svg?style=flat-square "DLE Version")
[![MIT License](https://img.shields.io/badge/license-AGPL_3.0-blue.svg?style=flat-square)](https://github.com/Gokujo/tagsadd/blob/master/LICENSE)
# TagsAdd+ 2.0
Пользовательские теги
![TagsAdd+](/1462111645_tagsadd%5B1%5D.png)


#  Нововведения начиная с версии 2.0
- переписан код, новый функционал
- подключается одной строкой
- исправлены баги

#  Нововведения начиная с версии 1.2.1
- добавлены теги [usertags] и [not-usertags] для скрытия данных для включённого и выключенного модуля
- исправлены баги

# Нововведения начиная с версии 1.2
- добавлена админ панель
- улучшен код

# Обновление до 1.2.1
Замените все папки и файлы в директории engine

# Установка
- Смотрим докуменацию [документация](http://help.maxim-harder.de/topic/31-versiya-20/)

# Инструкция по применению
- Открываем файл шаблона полной новости (fullstory.tpl) и в любое место добавляем следующую строку: {include file="/engine/modules/maharder/tagsadd.php?newsid={news-id}&focus=XXX"}
- Вместо XXX вписываем:
• button - для вывода кнопки
• modal - для вывода модального окна
• functions - для вывода функций
- Ещё можно дописать параметр nameN. Так будут называться ключевые функции для окон и кнопок.