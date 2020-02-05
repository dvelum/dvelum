Структура файлов конфигураций
===
[<< документация](readme.md)

Пользовательские и системные конфигурации разделены по папкам:

    application/configs/common/dist - общие настройки по умолчанию из дистрибутива
    application/configs/common/local - общие локальные настройки
    application/configs/prod/ -  переопределение настроек специфичных для  production  режима
    application/configs/dev/ -  переопределение настроек специфичных для  development  режима
    application/configs/test/-  переопределение настроек специфичных для  test  режима

Пользователь может доопределять или полностью переопределять системные настройки, для этого достаточно создать одноименный файл в папке application/configs/local более того доопределение может быть каскадным (несколько файлов в цепочке). Папка с переопределениями настроек для конеретного режима подключается при загрузке ядра.

За работу с настройками теперь отвечает Dvelum\Config::storage() (класс Dvelum\Config\Storage)
Получение настроек

```php
<?php
$storageConfig = \Dvelum\Config::storage()->get('filestorage.php');
//может принимать 3 параметра, 2 последних необязательны:
\Dvelum\Config::storage()->get($filepath, $useCache, $merge);
```

**$filepath** - относительный путь к файлу конфигурации, отсчитывается от директории application/configs/common/[dist или local]/

**$useCache** - использовать кэш рантайма, если конфигурация уже открывлась в рамках текущего запроса

**$merge** - флаг, по умолчанию  true  - объеденять конфигурации, т.е взять  dist/myconfig.php  наложить на него значение  local/myconfig.php. Если установлен флаг  false, то берется конфигурация из последнего существующего в цепочке подключения файла, например только local/myconfig.php Данный параметр необходим для полного переопределения кофигурации (удаление ненужных свойств)

##Переопределение стандартной конфигурации

Допустим разработчик хочет включить кэширование (основнй файл конфигурации application/configs/common/dist/main.php)

Для этого можно создать файл application/configs/common/local/main.php  следующего содержания:

```php
<?php
   return [
      'use_cache' => true
   ];
```
 

В процессе разработки DVelum  сохраняет конфигурации в каталог application/configs/common/local/ таким образом ваши проекты, объекты  ORM  отделены от дистрибутива.
Цепочки конфигураций

Конфигурации подключаются по цепочке, которая описана в настройках хранилища application/configs/common/dist/config_storage.php

```php
<?php
return [
   'file_array'=>[
       'paths' => [
           './application/configs/common/dist/',
           './application/configs/common/local/'
       ],
       'write' =>  './application/configs/common/local/',
       'apply_to' => './application/configs/common/dist/'
   ],
   'debug'=>false
];
```
где: 

**paths**  -  список директорий по очередности загрузки, можно добавлять свои (например для стороннего компонента)

**write** -  директория в которую будет производиться сохранение новых/обновленных конфигураций

**apply_to**  - папка из которой будут браться конфигурации для доопределения при сохранении. Если мы сохраняем обновленную конфигурацию, которая существует в указанной папке, то в папку write  будет записан файл содержащий только измененные ключи

Пример. Имеем цепочку:
```php
'paths' => [
     './application/configs/common/dist/',
     './application/configs/common/vendor/',
     './application/configs/common/local/'
]
```

В этом случае, порядок получения конфигурации следующий:  система откроет ./application/configs/common/dist/[someconfig.php], далее наложит на него ./application/configs/common/vendor/[someconfig.php],

далее наложит на него ./application/configs/common/local/[someconfig.php]

При этом файл ./application/configs/common/vendor/[someconfig.php]   может не существовать, в этом случае система пропустит этот шаг.

Подобным образом осуществляется работа с локализациями, только через отдельное хранилище Lang::storage();