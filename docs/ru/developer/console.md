Работа через консоль, запуск в cron / supervisor
===

[<< документация](readme.md)

## Запуск фоновых заданий

Список действий, доступных из консоли описан в application/configs/common/dist/console_actions.php,
Запуск осуществляется в корневой папке платформы командой:
```
php ./console.php /[action]
```

## Системные Action

* **buildDb** - перестроение базы данных с объектыми ORM
* **buildShards** - перестроение базы данных шардов с объектами ORM
* **generateClassMap** - перестроить карту классов
* **buildJs** - пересобрать JS файлы словарей и локализаций
* **clearStatic** - очистить кэш JS и CSS
* **external-add** - метод используемый при установке сторонних модулей

## Добавление своих Action
Свои Action нужно описывать в локальном файле конфигурации application/configs/common/local/console_actions.php

Пример:
```php
<?php
return  [
    'testAction'=>[
       'adapter' => '\\App\\Console\\TestAction'
        // другие опции
    ]
];
```
положим в application/classes/App/Console/TestAction.php
```php
<?php
declare(strict_types=1);

namespace App\Console;

class TestAction extends Dvelum\App\Console\Action
{
    public function action(): bool
    {
       echo 'Test Action started';
       return true;
    }
}
```
Теперь это действие можно запустить командой:
```
php ./console.php /testAction
```


## Запуск действий аналогичных браузеру
Запуск  http://mysite.com/page/param/  аналогичен cd /path/to/project && php ./console_client.php  /page/param

Вы запускаете файл console.php, расположенный в корне платформы, передавая ему в качестве параметров часть пути  url.
