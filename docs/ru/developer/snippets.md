Простые и быстрые решения
===
[<< документация](readme.md)


##### Сформировать путь к странице на основе правил формирования URL 

```php
\Dvelum\Request::factory()->url(['controller' , 'action' , 'subaction']);
```
Стандартная конфигурация использует «/» как разделитель и «.html» как расширение. Будет сформирован путь: /controller/action/subaction.html

##### Определить, авторизован ли пользователь
```php
$user = \Dvelum\App\Session\User::factory(); 
$authorized = $user->isAuthorized();
```
##### Авторизовать пользователя по логину и паролю
```php
$user = \Dvelum\App\Session\User::factory()->login($login , $password);
```
Если пара login-password верная, то $user будет содержать ссылку на объект User, иначе false.
Извлечь колонку из многомерного массива (актуально для результатов выборок из базы данных)
```php
$data = array(
   array('id' => 10 , 'name' => 'Name1' , 'group' => 'admin'),
   array('id' => 22 , 'name' => 'Name2' , 'group' => 'admin'),
   array('id' => 34 , 'name' => 'Name3' , 'group' => 'dev'),
   array('id' => 45 , 'name' => 'Name4' , 'group' => 'admin'),
   array('id' => 52 , 'name' => 'Name5' , 'group' => 'dev'),
   array('id' => 61 , 'name' => 'Name6' , 'group' => 'user'),
);
$ids = \Dvelum\Utils::fetchCol('id' , $data);
print_r($ids);
//Будет получено:
Array
(
   [0] => 10
   [1] => 22
   [2] => 34
   [3] => 45
   [4] => 52
   [5] => 61
)
```
##### Переиндексировать многомерный массив (актуально для результатов выборок из базы данных)
```php
$data = array(
    array('id'=>10,'name'=>'Name1','group'=>'admin'),
    array('id'=>22,'name'=>'Name2','group'=>'admin'),
    array('id'=>34,'name'=>'Name3','group'=>'dev'),
    array('id'=>45,'name'=>'Name4','group'=>'admin'),
    array('id'=>52,'name'=>'Name5','group'=>'dev'),
    array('id'=>61,'name'=>'Name6','group'=>'user'),
);
$ids = \Dvelum\Utils::rekey('id' , $data);
print_r($ids);
Array
(
    [10] => Array([id] => 10 [name] => Name1 [group] => admin)
    [22] => Array([id] => 22 [name] => Name2 [group] => admin)
    [34] => Array([id] => 34 [name] => Name3 [group] => dev)
    [45] => Array([id] => 45 [name] => Name4 [group] => admin)
    [52] => Array([id] => 52 [name] => Name5 [group] => dev)
    [61] => Array([id] => 61 [name] => Name6 [group] => user)
)
```
##### Перегруппировать данные многомерного массива (актуально для результатов выборок из базы данных)
```php
$data = array(
 	array('id'=>10,'name'=>'Name1','group'=>'admin'),
 	array('id'=>22,'name'=>'Name2','group'=>'admin'),
 	array('id'=>34,'name'=>'Name3','group'=>'dev'),
 	array('id'=>45,'name'=>'Name4','group'=>'admin'),
 	array('id'=>52,'name'=>'Name5','group'=>'dev'),
 	array('id'=>61,'name'=>'Name6','group'=>'user'),
);
$ids = \Dvelum\Utils::groupByKey('group' , $data);
print_r($ids);
Array
(
     [admin] => Array (
         [0] => Array([id] => 10 [name] => Name1 [group] => admin)
         [1] => Array([id] => 22 [name] => Name2 [group] => admin)
         [2] => Array([id] => 45 [name] => Name4 [group] => admin)
     )
     [dev] => Array (
         [0] => Array([id] => 34 [name] => Name3 [group] => dev)
         [1] => Array([id] => 52 [name] => Name5 [group] => dev)
     )
     [user] => Array (
         [0] => Array([id] => 61 [name] => Name6 [group] => user)
     )
)
```
##### Получить объект конфигурации. Подключить конфигурационный файл php (содержащий массив)

Получим объект конфигурации новостей, расположенный по адресу: ./system/app/config/news.php
```php
/**
 * @var \Dvelum\Config\ConfigInterface $someCfg
 */
$someCfg = Config::storage()->get('news.php');
```
##### Получить объект, содержащий основную конфигурацию. Получить Main Config

```php
/**
 * @var \Dvelum\Config\ConfigInterface $cfg
 */
$cfg = Config::storage()->get('main.php');
```
##### Узнать количество объектов определенного типа (количество строк в таблице БД)

Получим количество активных учетных записей пользователей (объект User):
```php
$count = Model::factory('user')->query()->filters(['enabled'=>1])->getCount(); 
```
##### Выборка данных объекта при помощи модели
Пример: Выбрать 10 последних новостей
```php
/*
 * Параметры выборки. Получить 10 строк, отсортированных по news_date в порядке убывания
 */
$params = array(
 	'sort'=>'news_date',
 	'dir'=>'DESC',
 	'start'=>0,
 	'limit'=>10
);
/*  
 * Фильтры. Только опубликованные записи  
 */ 
$filters = ['published'=>true]; 
/*  
 * Список полей, которые необходимо выбрать  
 */ 
$fields = ['id','title','news_date']; 
/*  
 * Инициализируем модель  
 */ 
$model = Model::factory('News'); 
$data = $model->query()
              ->params($params)
              ->filters($filters)
              ->fields($fields)
              ->fetchAll(); 
```
##### Получить ссылку на инстанцированый адаптер подключения к базе данных
```php
use Dvelum\Orm\Model;
// Адаптер подключения к базе данных, используемый моделью User
/**
 * @var \Dvelum\Db\Adapter
 */
$db = Model::factory('user')->getDbConnection();
```
##### Получить ссылку на инстанцированый адаптер кэша
```php
use Dvelum\Orm\Model;
// Получить адаптер кэширования данных
$cache = Model::factory('user')->getCacheAdapter();
/**
 * @var \Dvelum\Cache\CacheInterface | false $cache
 */
```

##### Вывести в таблицу заголовок вместо ссылки на объект</a>

Самое простое решение - доопределить listField контроллера, ели он унаследован от \Dvelum\App\Backend\Api\Controller:
```php
protected $listFields = ['link_field'];
```
или если нужно положить заголовок в отдельное поле результата
```php
protected $listFields = ['new_result_field'=>'link_field'];
```

