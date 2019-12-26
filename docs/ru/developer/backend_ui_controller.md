UI контроллеры админ панели
===
[<< документация](readme.md)

Стандартные контроллеры, которые создает генератор модулей (интерфейс управления модулями админ панели).

Генератор модулей создает файлы в папке application/classes/App/Backend/[Название модуля]/

Контроллер вашего модуля наследуются от базового контроллера в котором заложен функционал по работе с проктами 
дизайнера интерфейсов.

Пример:

```php
<?php

namespace \App\Backend\MyModule;

use Dvelum\App\Backend;

class Controller extends Backend\Ui\Controller
{
    /*
     * Список дополнительных ORM объектов к данным которых разрешен доступ из UI  
     * Например, для редактирования полей автор в нашем модуле нужен доступ к списку пользователей
     */  
    protected $canViewObjects = ['User'];
    // список колонок (полей ORM) основной таблицы UI
    protected $listFields = ['id','title','date','user_id'];
    /*
     * список колонк основной таблицы UI для которых в качестве данных
     * нужно получить заголовок связанного объекта, например вместо ID пользователя
     * показать его имя
     * 
     * Какое поле и шаблон используется в качестве заголовка можно указать
     * в интерфейсе ORM (окно редактирования объекта) 
     * Если написать 'user_id' то заменятся данные колонки
     * Если написать 'author_name'=>'user_id' то JSON ответе c серевера
     * у каждого элемента списка появится дополнительное поле вида {... author_name:'Имя пользователя'}
     * 
     */ 
    protected $listLinks = ['user_id','author_name'=>'user_id'];
    
    /**
     * Метод должен возвращать код модуля к которому осуществляется доступ,
     * по коду модуля проверяются права доступа польззователя 
     * @return string
     */
    public function getModule(): string
    {
        return 'MyModule';
    }
    /**
     * Метод возвращает имя объекта ORM, который редактируется в данном интерфейсе
     * @return string
     */
    public function getObjectName(): string
    {
        return 'MyModuleObject';
    }
}
```

## Взаимодействие с UI

UI взаимодействует с бэкендов вызывая различные базовые действия (Action) контроллера 

* indexAction - отображение интерфейса
* listAction - список записей для основной таблицы
* editAction - редактирование записи
  * createAction - создание новой записи
  * updateAction - обновление записи
* deleteAction - удаление записи
* linkedListAction - получение списка для выбора связанного объекта (для формы редактирования)
* objectTitleAction - получение заголовка связанного объекта (для формы редактирования)
* loadDataAction - получение всех данных записи (для формы редактирования)
* publishAction - публикация изменений (для объектов с версионным контролем)
* unpublishAction - снятие с публикации (для объектов с версионным контролем)

## Перехват событий

Иногда возникает необходимость переопределить наборы данных, которые контроллер возвращает в UI,
контроллер генерирует события на разных этапах подготовки данных. На эти события можно подписаться
внутри контроллера и изменить данные с которыми он оперирует.

Пример событий:

* EventManager::BEFORE_LIST - перед тем как начать готовить ответ listAction
* EventManager::AFTER_LIST - после того как список записей listAction подготовлен к отправке в UI
* EventManager::BEFORE_LOAD - перед тем как начать готовить данные loadAction
* EventManager::AFTER_LOAD - после того как данные loadAction готовы к отправке в UI
* EventManager::BEFORE_LINKED_LIST - перед тем как начать готовить данные списка связанных объектов linkedListAction
* EventManager::AFTER_LINKED_LIST - - после того как данные linkedListAction готовы к отправке в UI

Эти события можно перехватить, пример:

```php
use Dvelum\App\Backend;
use Dvelum\App\Controller\Event;
use Dvelum\App\Controller\EventManager;

class Controller extends Backend\Ui\Controller
{
    // переопределяем метод инициализации listeners
    public function initListeners()
    {
        // после того как список данных для таблицы готов, вызовем метод prepareList
        $this->eventManager->on(EventManager::AFTER_LIST, [$this, 'prepareList']);
    }

    /**
     * Дополнительная обработка данных перед оправкой в UI
     * @param Event $event
     * @return void
     */
    public function prepareList(Event $event): void
    {
        // ссылка на данные которые будут отправлены в UI
        $data = &$event->getData()->data;

        // пример, в каждую строку данных добавляем вычисляемые данные
        foreach ($data as $k => &$v) {
           $v['some_field'] = 'some_calc';
        }
        unset($v);
    }
}
```