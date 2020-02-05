Запуск тестов DVelum
===
[<< документация](readme.md)

Тесты запускаются из корня проекта, для запуска тестов необходимо установить dev зависимости, подробнее: устанока и настройка Dvelum 2.x
## PHPUnit

    ./vendor/bin/phpunit -c ./phpunit.xml.dist
## Интеграционные тесты на PHPUnit

Создать 1 БД (например  dvelum_test) для интеграционных тестов, внести настройки подключения в файлы конфигурации application/configs/test/db/    default.php, error.php, sharding_index.php (по аналогии с  настройками application/configs/dev/db/)

Создать 2 БД данных для тестирования функций шардинга и внести настройки подключения в файл application/configs/test/sharding_shards.php (для простоты создайте базы dvelum_test_sh1, dvelum_test_sh2  для пользователя бд тестов)

    ./vendor/bin/phpunit -c ./integration.xml.dist
    
## Статический анализ

    ./vendor/bin/phpstan analyse application dvelum

или

    php -d memory_limit=256M ./vendor/bin/phpstan analyse dvelum
    