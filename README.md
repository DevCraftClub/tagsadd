![version](https://img.shields.io/badge/version-1.2.1-red.svg?style=flat-square "Version")
![DLE](https://img.shields.io/badge/DLE-9.X--11.x-green.svg?style=flat-square "DLE Version")
[![MIT License](https://img.shields.io/badge/license-AGPL_3.0-blue.svg?style=flat-square)](https://github.com/Gokujo/tagsadd/blob/master/LICENSE)
# TagsAdd+ 1.2.1
Пользовательские теги
![TagsAdd+](/1462111645_tagsadd%5B1%5D.png)

#  Нововведения начиная с версии 1.2.1
- добавлены теги [usertags] и [not-usertags] для скрытия данных для включённого и выключенного модуля
- исправлены баги

# Нововведения начиная с версии 1.2
- добавлена админ панель
- улучшен код

# Обновление до 1.2.1
Замените все папки и файлы в директории engine

# Установка
- Смотрим файл [установка.txt](https://raw.githubusercontent.com/Gokujo/tagsadd/master/установка.txt)

# Инструкция по применению
- Если вы хотите подключить тег не в fullstory.tpl, а в main.tpl, то в нужное место (main.tpl) ставим тег {tagsbutton}, а в fullstory.tpl тогда тег {tagsbody}.
- Если вы решили всётаки подключить в шаблон полной новости, то в любое место добавляем тег {tagsadd}.
Для полной новости действуют следующие теги:
- {tagsadd} - полное подключение модуля
- {tagsbutton} - добавляет только кнопку "Добавить"
- {tagsbody} - добавляет в шаблон только модальное окно
- [usertags][/usertags] - если модуь включён, то заключённый в эти тег текст будет отображаться
- [not-usertags][/not-usertags] - аналогия с верхним, только наоборот, если модуль выключен
