Компонент SearchPanel
===
[<< документация](readme.md)

Панель поиска данных для тулбара, привязывается к  Ext.data.Store, осуществляет фильтрацию или поиск данных, может работать в двух режимах:
* локальная фильтрация;
* фильтрация за счет серверного компонента.

##### Основные свойства:

* **store** {Ext.data.Store} - хранилище данных;
* **fieldNames** {array} - массив имен полей модели, по которым осуществляется поиск;
* **local** {boolean} - режим работы, по умолчанию false (запрос на сервер);
* **searchParam** {string} - имя параметра при передаче строки поиска на сервер.
    
![DVelum Search Panel](../../images/SearchPanel.png)