Конструктор Select запросов Dvelum\Db\Select
===
[<< документация](readme.md)

Пример прямого запроса к БД
```php
<?php
// получаем модель для ORM объекта "Пользователь"
$model = \Dvelum\Orm\Model::factory('User');
// узнаем имя таблицы бд, в которой хранятся данные
$userTable = $model->table();
// получаем адаптер соединения с базой данных
$db = $model->getDbConnection();
// формируем запрос вручную
$sql = new \Dvelum\Db\Select();

$sql->from($userTable)
    ->where('`enabled` = ?', true)
    ->limit(10);

try{
    // получаем результаты в виде ассоциативного массива
    $db->fetchAll($sql);
}catch(\Exception $e){
    // обработать исключение
}

```

## DISTINCT
```sql
SELECT DISTINCT `table`.* FROM `table`;
```
```php
    $sql = new \Dvelum\Db\Select();
    $sql->from('table')->distinct();
```

## FROM
```php
$sql = new \Dvelum\Db\Select();
```

1 
```sql 
SELECT `table`.`id`, `table`.`title`, `table`.`name` FROM `table`;
```
```php
$sql->from('table', ['id','title','name']);
```
2
```sql 
SELECT COUNT(*) AS `count`, `table`.`name` AS `field_name`, `table`.`order` FROM `table`;
```
```php
$sql->from('table', array(
           'count'=>'COUNT(*)',
           'field_name'=>'name',
           'order'
          )
);
```   
3
```sql 
SELECT COUNT(*) AS `count`, `t`.`name` AS `field_name`, `t`.`order` FROM `some_table` AS `t`;
```
```php
$sql->from(
    ['t'=>'some_table'],                     
    [
        'count'=>'COUNT(*)',
        'field_name'=>'name',
        'order'
    ]
);
```

## GROUP BY
```php
$sql = new \Dvelum\Db\Select();
```
1
```sql   
SELECT `table`.* FROM `table` GROUP BY `type`,`cat`;
```
```php
$sql->from('table')->group(array('type','cat'));
```
2
```sql
SELECT `table`.* FROM `table` GROUP BY `type`;   
```
```php
$sql->from('table')->group('type');
```
3
```sql
SELECT `table`.* FROM `table` GROUP BY `type`,`cat`;
```
```php
$sql->from('table')->group('type,cat');
```

## HAVING

```sql
SELECT CONCAT(code,"i") AS `c_code` FROM `sb_content` HAVING (`c_code` ='index');
```
```php
$sql = new \Dvelum\Db\Select();
$sql->from('sb_content' , ['c_code'=>'CONCAT(code,"i")'])
    ->having('`c_code` =?',"index");
```

## JOIN

```sql
SELECT `a`.*, `b`.`title`, `b`.`time` FROM `table` AS `a` INNER JOIN `table2` AS `b` ON a.code = b.id;
```
```php
$sql = new \Dvelum\Db\Select();
$sql->from(['a'=>'table'])
    ->join(
        ['b'=>'table2'],
        'a.code = b.id',
        ['title','time']
    );
```

## LIMIT

```php
$sql = new \Dvelum\Db\Select();
```
1)
```sql
SELECT `table`.* FROM `table` LIMIT 20,10;
```
```php
$sql->from('table')->limit(10 ,20);
``` 
2)    
```sql           
SELECT `table`.* FROM `table` LIMIT 10;
```
```php
$sql->from('table')->limit(10);
```


## Устанавливаем limit и count исходя из номера страницы.

```sql
    SELECT `table`.* FROM `table` LIMIT 30,10;
```
```php
    $sql = new \Dvelum\Db\Select();
    $sql->from('table')->limitPage(4, 10);
```

## ORDER BY

```php 
$sql = new \Dvelum\Db\Select();
```
1
```sql
SELECT `table`.* FROM `table` ORDER BY `name` DESC,`group` ASC;
```
```php
$sql->from('table')->order(array('name'=>'DESC','group'=>'ASC'));
```
2
```sql
SELECT `table`.* FROM `table` ORDER BY `name`,`group`;
```
```php
$sql->from('table')->order(array('name','group'));
```
3
```sql
SELECT `table`.* FROM `table` ORDER BY name ASC,group DESC;
```
```php
$sql->from('table')->order(array('name ASC','group DESC'));
```
4
```sql
SELECT `table`.* FROM `table` ORDER BY `name` DESC,`group` ASC;
```
```php
$sql->from('table')->order('name DESC, group ASC');
```
5
```sql
SELECT `table`.* FROM `table` ORDER BY `name` DESC;
```
```php
$sql->from('table')->order('name DESC');
```


## OR HAVING

```sql
SELECT CONCAT(code,"i") AS `c_code` FROM `sb_content` HAVING (`c_code` ='index') OR (`c_code` ='articles');
```
```php
$sql = new \Dvelum\Db\Select();
$sql->from('sb_content' , ['c_code'=>'CONCAT(code,"i")'])
    ->having('`c_code` =?',"index")
    ->orHaving('`c_code` =?',"articles");
```

## WHERE
```php
$sql = new \Dvelum\Db\Select();
```
1
```sql
    SELECT `table`.* FROM `table` WHERE (`id` =7) ORDER BY `name` DESC;
```
```php
$sql->from('table')->where('`id` =?',7)->order('name DESC');
```
2
```sql
SELECT `table`.* FROM `table` WHERE (`id` =0.600000) ORDER BY `name` DESC;
```
```php
$sql->from('table')->where('`id` =?',0.6)->order('name DESC');
``` 
3
```sql
SELECT `table`.* FROM `table` WHERE (`code` ='code') ORDER BY `name` DESC;
```
```php
$sql->from('table')->where('`code` =?','code')->order('name DESC');
```
4
```sql
SELECT 
    `table`.* 
FROM 
    `table` 
WHERE
    (`code` IN('first','second'))
ORDER BY `name` DESC;
```
```php
$sql->from('table')
->where('`code` IN(?)', ['first','second'])
->order('name DESC');
```
5
```sql
SELECT `table`.* FROM `table` WHERE (`id` IN(7,8,9)) ORDER BY `name` DESC;
```
```php
$sql->from('table')->where('`id` IN(?)',array(7,8,9))->order('name DESC');
```

## OR WHERE
```sql
SELECT `table`.*
FROM `table`
WHERE
    (`id` =7 AND `code` ='code')
OR
    (`id` =8 )
OR
    (`id` =9);
```
```php
$sql = new \Dvelum\Db\Select();

$sql->from('table')
->where('`id` =?',7)
->where('`code` =?',"code")
->orWhere('`id` =?',8)
->orWhere('`id` =?',9);
```