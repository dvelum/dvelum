[документация](readme.md)
#Простые и быстрые решения



#####Сформировать путь к странице на основе правил формирования URL 

```php
\Dvelum\Request::factory()->url(['controller' , 'action' , 'subaction']);
```

Стандартная конфигурация использует «/» как разделитель и «.html» как расширение. Будет сформирован путь: /controller/action/subaction.html
Сформировать путь к странице публичной части, использующей определенную функциональность (модуль)

Допустим, к странице с кодом newspage прикреплена функциональность news, необходимо сформировать ссылку на вторую новостную страницу:
```php
$frontendRouter = new \Dvelum\App\Router\Module(); 
$url = \Dvelum\Request::factory()->url([$frontendRouter->findUrl('news') , 2 ]);
```
Будет сформирован путь: /news/2.html
Определить, авторизован ли пользователь
```php
$user = \Dvelum\App\Session\User::factory(); 
$authorized = $user->isAuthorized();
```
#####Авторизовать пользователя по логину и паролю
```php
$user = \Dvelum\App\Session\User::factory->login($login , $password);
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
#####Переиндексировать многомерный массив (актуально для результатов выборок из базы данных)
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
    [45] => Array([id] => 45[name] => Name4[group] => admin)
    [52] => Array([id] => 52[name] => Name5[group] => dev)
    [61] => Array([id] => 61[name] => Name6[group] => user)
)
```
#####Перегруппировать данные многомерного массива (актуально для результатов выборок из базы данных)
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
#####Получить объект конфигурации. Подключить конфигурационный файл php (содержащий массив)

Получим объект конфигурации новостей, расположенный по адресу: ./system/app/config/news.php
```php
/**
 * @var \Dvelum\Config\ConfigInterface $someCfg
 */
$someCfg = Config::storage()->get('news.php');
```
#####Получить объект, содержащий основную конфигурацию. Получить Main Config

```php
/**
 * @var \Dvelum\Config\ConfigInterface $cfg
 */
$cfg = Config::storage()->get('main.php');
```
#####Узнать количество объектов определенного типа (количество строк в таблице БД)

Получим количество активных учетных записей пользователей (объект User):
```php
$count = Model::factory('user')->query()->filters(['enabled'=>1); 
```
#####Выборка данных объекта при помощи модели
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
$filters = array('published'=>true); 
/*  
 * Список полей, которые необходимо выбрать  
 */ 
$fields = array('id','title','news_date'); 
/*  
 * Инициализируем модель  
 */ 
$model = Model::factory('News'); 
$data = $model->query()->params($params)->($filters)->fields($fields)->fetchAll(); 
```
#####Получить ссылку на инстанцированый адаптер подключения к базе данных
```php
// Адаптер подключения к базе данных, используемый моделью User
/**
 * @var \Dvelum\Db\Adapter
 */
$db = Model::factory('user')->getDbConnection();
```
#####Получить ссылку на инстанцированый адаптер кэша
```php
// Получить адаптер кэширования данных
$cache = Model::factory('user')->getCacheAdapter();
/**
 * @var \Cache_Interface | false $cache
 */
```
#####Вставить html в центральную часть страницы вне шаблона (публичная часть)
```php
$page = \Page::getInstance();
$page->text.='Some text';
```
#####Изменение html-заголовка страницы и мета-содержания
```php
namespace App\Frontend\News;
class Controller extends \Dvelum\App\Frontend\Controller 
{
   public function someAction()
   {

       $this->page = \Page::getInstance();
       // дополнение текста
       $this->page->text.=’Some text’;
       // изменение html-заголовка страницы (тот, что отображается в заголовке окна браузера или его вкладки)
       $this->page->html_title = 'Page HTML Title';
       // изменение заголовка страницы (обычно выводится внутри шаблона в теге)
       $this->page->page_title = 'Page Title';
       // изменение содержания тега 
       $this->page->meta_keywords = 'page meta key words';
       // изменение содержания тега 
       $this->page->meta_description = 'page meta description';
    }
 }
```
#####Шаблон. Вывести блоки, назначенные на определенную позицию

Блоки выводятся в шаблонах, для этого нам необходима ссылка на менеджер блоков, она автоматически передается в шаблон layout.php. Если менеджер блоков вам нужен на вложенном уровне шаблонов, то перед рендерингом передайте ссылку на менеджер блоков. Конфигурация макета (laout_cfg.php) содержит набор плейсхолдеров, к которым привязываются блоки в административном интерфейсе. Каждый плейсхолдер имеет свой код, зная этот код можно вывести блоки в шаблон:
```php
$blockManager = $this->get('blockManager');
echo $blockManager->getBlocksHtml('placecode');
```

#####Вывести в таблицу заголовок вместо ссылки на объект</a>

Самое простое решение - доопределить listField контроллера, ели он унаследован от \Dvelum\App\Backend\Api\Controller:
```php
protected $listFields = ['link_field'];
```
или если нужно положить заголовок в отдельное поле результата
```php
protected $listFields = ['new_result_field'=>'link_field'];
```

