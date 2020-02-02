[![PHP Version](https://img.shields.io/badge/php-7.3%2B-blue.svg)](https://packagist.org/packages/dvelum/dvelum)
[![Total Downloads](https://img.shields.io/packagist/dt/dvelum/dvelum.svg?style=flat-square)](https://packagist.org/packages/dvelum/dvelum)
[![Build Status](https://travis-ci.org/dvelum/dvelum.svg?branch=dev-3.x)](https://travis-ci.org/dvelum/dvelum)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/da4a46fc1a1f4f29a758a5eb82344977)](https://www.codacy.com/app/dvelum/dvelum?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=dvelum/dvelum&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/da4a46fc1a1f4f29a758a5eb82344977)](https://www.codacy.com/app/dvelum/dvelum?utm_source=github.com&utm_medium=referral&utm_content=dvelum/dvelum&utm_campaign=Badge_Coverage)

DVelum
======

PHP/ExtJS-based web development platform
------


DVelum is a professional web-development platform based on PHP and ExtJS that aims at automating routine development tasks and facilitates programming by means of a graphical interface.
It offers highest real-time performance, facilitates and speeds up the development process letting you concentrate on business logic and ignore minor issues.
DVelum is an indispensable toolkit for development of both complex-structured systems (eCommerce, CRM, WebScada, etc. ) and simple websites.

GNU General Public License version 3.0

![](docs/images/1.jpeg) ![](docs/images/2.png) ![](docs/images/3.png) ![](docs/images/4.png) ![](docs/images/5.png)

Local installation
-----

```
composer create-project dvelum/dvelum
```
Apache VirtualHost configuration example
```
<VirtualHost *:80>
    ServerName dvelum.local
    DocumentRoot /path/to/dvelum/www
    <Directory "/path/to/dvelum/www">
        Require all granted
        AllowOverride All
        Options +ExecCGI -Includes -Indexes
     </Directory>
</VirtualHost>
```
Add local domain to /etc/hosts
```
127.0.0.1 dvelum.local
```

Open Web Browser at http://dvelum.local/install and follow the instructions

---
[Документация](docs/ru/developer/readme.md)

Issues https://github.com/dvelum/dvelum/issues

Old Version Downloads https://sourceforge.net/projects/dvelum/files/ and https://code.google.com/p/dvelum/downloads/list?can=1

DVelum 0.x/1.x Repo https://github.com/k-samuel/dvelum

Official Site (ENG) http://dvelum.net

Official Site (RU)  http://dvelum.ru



