Работа с ORM и базами данных
===
[<< документация](readme.md)


### Ручная работа с запросами

* Простые запросы с фильтрами Dvelum\Db\Orm\Model\Query
```php
<?php
$model = \Dvelum\Orm\Model::factory('User');
$data = $model->query()
              // добавление фильтров ключ => значение или Dvelum\Db\Select\Filter
              ->filters(['enabled'=>1])
              // дополнительные параметры сортировки и лимитов
              ->params(['sort'=>'name','dir'=>'DESC','start'=>0,'limit'=>100])
              // список полей для выборки, можно создавать алиас  ['id_field_alias'=>'id']
              ->fields(['id','name']) 
              // извлечение результатов fetchAll, fetchCol, fetchOne, fetchRow
              ->fetchAll();  
```
* [Конструктор Select запросов Dvelum\Db\Select](db_select.md)