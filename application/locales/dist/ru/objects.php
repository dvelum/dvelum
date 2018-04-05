<?php return array (
    'bgtask_signal' =>
        array (
            'title' => 'Сигнал для фоновой задачи',
            'fields' =>
                array (
                    'pid' => 'PID Задачи',
                    'signal' => 'Сигрнал',
                ),
        ),
    'bgtask' =>
        array (
            'title' => 'Фоновая задача',
            'fields' =>
                array (
                    'status' => 'Статус',
                    'title' => 'Заголовок',
                    'parent' => 'Родитель',
                    'op_total' => 'Счетчик операци',
                    'op_finished' => 'Завершено операций',
                    'memory' => 'Памяти выделено',
                    'time_started' => 'Время запуска',
                    'time_finished' => 'Время окончания',
                    'memory_peak' => 'Пик потребления памяти',
                ),
        ),
    'blockmapping' =>
        array (
            'title' => 'Карта блоков',
            'fields' =>
                array (
                    'page_id' => 'Страница',
                    'place' => 'Код контейнера',
                    'block_id' => 'Блок',
                    'order_no' => 'Сортировка',
                ),
        ),
    'blocks' =>
        array (
            'title' => 'Блоки',
            'fields' =>
                array (
                    'title' => 'Заголовок',
                    'text' => 'Текст',
                    'show_title' => 'Показывать заголовок ?',
                    'is_system' => 'Системный ?',
                    'sys_name' => 'Системное имя',
                    'params' => 'Параметры',
                    'is_menu' => 'Блок меню',
                    'menu_id' => 'Меню',
                ),
        ),
    'group' =>
        array (
            'title' => 'Группы пользователей',
            'fields' =>
                array (
                    'title' => 'Заголовок',
                    'system' => 'Системный?',
                ),
        ),
    'historylog' =>
        array (
            'title' => 'История изменений',
            'fields' =>
                array (
                    'user_id' => 'Пользователь',
                    'date' => 'Дата',
                    'record_id' => 'ID записи',
                    'type' => 'ID операции',
                ),
        ),
    'links' =>
        array (
            'title' => 'Ассоциации',
            'fields' =>
                array (
                    'src' => 'Объект источник',
                    'src_id' => 'ID источника',
                    'src_field' => 'поле источника',
                    'target' => 'Объект назначение',
                    'target_id' => 'ID назначения',
                    'order' => 'Сортировка',
                ),
        ),
    'medialib' =>
        array (
            'title' => 'Медиатека',
            'fields' =>
                array (
                    'title' => 'Заголовок',
                    'date' => 'Дата загрузки',
                    'alttext' => 'Альтернативный текст',
                    'caption' => 'Подпись',
                    'description' => 'Описание',
                    'size' => 'Размер файла',
                    'user_id' => 'Пользователь',
                    'path' => 'Путь к файлу',
                    'type' => 'Тип ресурса',
                    'ext' => 'Расширение файла',
                    'modified' => 'Дата модификации',
                    'croped' => 'Обрезан вручную',
                    'category' => 'Каталог',
                    'hash' => 'Хэш файла'
                ),
        ),
    'menu_item' =>
        array (
            'title' => 'Элемент меню',
            'fields' =>
                array (
                    'page_id' => 'Страница',
                    'title' => 'Заголовок',
                    'published' => 'Опубликован?',
                    'menu_id' => 'ID Меню',
                    'order' => 'Сортировка',
                    'parent_id' => 'Родительский элемент',
                    'tree_id' => 'ID в дереве',
                    'link_type' => 'Тип ссылки',
                    'url' => 'URL',
                    'resource_id' => 'Ссылка на ресурс',
                ),
        ),
    'menu' =>
        array (
            'title' => 'Меню',
            'fields' =>
                array (
                    'code' => 'Код',
                    'title' => 'Заголовок',
                ),
        ),
    'page' =>
        array (
            'title' => 'Страницы',
            'fields' =>
                array (
                    'is_fixed' => 'Зафиксирована?',
                    'parent_id' => 'Родительская страница',
                    'code' => 'Код',
                    'page_title' => 'Заголовок в HEAD',
                    'menu_title' => 'Заголовок меню',
                    'html_title' => 'Заголовок сраницы',
                    'meta_keywords' => 'Meta Keyword',
                    'meta_description' => 'Meta Description',
                    'text' => 'Текст',
                    'func_code' => 'Прикрепленный модуль',
                    'show_blocks' => 'Показывать блоки?',
                    'in_site_map' => 'Показывать в карте сайта?',
                    'order_no' => 'Сортировка',
                    'blocks' => 'Данные блоков',
                    'theme' => 'Тема оформления',
                    'default_blocks' => 'Использовать карту блоков по умолчанию',
                ),
        ),
    'permissions' =>
        array (
            'title' => 'Права доступа',
            'fields' =>
                array (
                    'user_id' => 'Пользователь',
                    'group_id' => 'Группа',
                    'view' => 'Просмотр',
                    'edit' => 'Редактирование',
                    'delete' => 'Удаление',
                    'publish' => 'Публикация',
                    'only_own'=> 'Только свои записи',
                    'module' => 'Модуль',
                ),
        ),
    'user' =>
        array (
            'title' => 'Пользователи',
            'fields' =>
                array (
                    'name' => 'Имя',
                    'email' => 'Email',
                    'login' => 'Логин',
                    'pass' => 'Пароль',
                    'enabled' => 'Активен?',
                    'admin' => 'Доступ в Бэк-офис?',
                    'registration_date' => 'Дата регистрации',
                    'confirmation_code' => 'Код подтверждения',
                    'group_id' => 'ID Группы',
                    'confirmed' => 'Подтвержден?',
                    'avatar' => 'Аватар',
                    'registration_ip' => 'IP регистрации',
                    'last_ip' => 'Последний IP',
                    'confirmation_date' => 'Дата подтверждения',
                ),
        ),
    'vc' =>
        array (
            'title' => 'Хранилище версий',
            'fields' =>
                array (
                    'date' => 'Дата',
                    'record_id' => 'ID Записи',
                    'object_name' => 'Имя объекта',
                    'data' => 'Дата',
                    'user_id' => 'Автор',
                    'version' => 'Версия',
                ),
        ),
    'apikeys' =>
        array (
            'title' => 'Ключи API',
            'fields' =>
                array (
                    'name' => 'Имя',
                    'hash' => 'Хеш',
                    'active' => 'Активен',
                ),
        ),
    'mediacategory' =>
        array (
            'title' => 'Каталог медиатеки',
            'fields' =>
                array (
                    'title' => 'Имя',
                    'parent_id' => 'Родительский каталог',
                    'order_no' => 'Порядок сортировки',
                ),
        ),

    'acl_simple' =>
        array (
            'title' => 'Права доступа к ORM',
            'fields' =>
                array (
                    'user_id' => 'Пользователь',
                    'group_id' => 'Группа',
                    'view' => 'Просмотр',
                    'edit' => 'Редактирование',
                    'delete' => 'Удаление',
                    'publish' => 'Публикация',
                    'module' => 'Модуль',
                ),
        ),
    'error_log' =>
        array (
            'title' => 'Лог ошибок',
            'fields' =>
                array (
                    'name' => 'Источник',
                    'message' => 'Сообщение',
                    'date' => 'Дата',
                    'level' => 'Тип',
                    'context' => 'Контекст'
                ),
        ),
    'user_auth' =>
        array (
            'title' => 'Настройки аутентификации',
            'fields' =>
                array (
                    'user' => 'Пользователь',
                    'type' => 'Тип аутентификации',
                    'config' => 'Конфигурация',
                ),
        ),
    'distributed_shard' =>
        array (
            'title' => 'Распределенные Объекты. Шард',
            'fields' =>
                array (
                    'name' => 'Имя шарда',
                    'dbHost' => 'Хост БД',
                    'group' => 'Группа',
                ),
        ),
); 