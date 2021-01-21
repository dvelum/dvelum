Роутинг в DVelum
===

[<< документация](readme.md)

В дистрибутив DVelum включены три роутера для публичной части сайта
 (нужный вариант указан в extensions/dvelum-core/application/configs/dist/frontend.php), 
 при необходимости можно переопределить в application/configs/local/frontend.php: 
 
**возможные варианты**
'router' => 'path' // 'path', 'config', 'cms'.

**path** - (Dvelum\App\Router\Path) Роутинг на основе файловых путей, 
например http://site.ru/news/list, в этом случае ищется App\Frontend\News\Controller::listAction, 
при отсутствии запускается Dvelum\App\Frontend\Index\Controller::indexAction. 

**config**  - (Dvelum\App\Router\Config) Роутинг на основе таблицы маршрутизации. 
Интерфейс управления модулями публичной части позволяет создавать алиасы (url-коды) запуска контроллеров,
 при отсутствии алиаса запускается Dvelum\App\Frontend\Index\Controller::indexAction.
 
**cms** -  (Dvelum\App\Router\Cms) Доступен в модуле [module-cms](https://github.com/dvelum/module-cms)
представляет из себя роутинг на основе дерева страниц с подключенными к ним модулями. 
Пути прописываются в интерфейсе управления страницами, каждая страница имеет уникальный код, который является маршрутом.
К странице может быть прикреплен контироллер модуля (похож на роутинг различных CMS).

--------------------

**backend** - Админ панель использует отдельный роутер Dvelum\App\Router\Backend 

**console**  - Консольные прилодения используют роутер Dvelum\App\Router\Console, он работает с настройками запуска [консольных Action](console.md)


