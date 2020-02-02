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
* Вставка большого числа строк в обход ORM (без валидации и триггеров)
```php
<?php
$model = \Dvelum\Orm\Model::factory('MyObject');
$insert = new \Dvelum\Orm\Model\Insert($model);
$data = [
    ['field1'=>1,'field2'=>2],
    ['field1'=>1,'field2'=>2]
];
// данные для вставки будут разбиты на порции
$insert->bulkInsert($data);

// можно обернуть в транзакцию
$db = $model->getDbConnection();
$db->begin();
if(!$insert->bulkInsert($data)){
    $db->rollback();
}else{
    $db->commit();
}
```

* [Конструктор Select запросов Dvelum\Db\Select](db_select.md)