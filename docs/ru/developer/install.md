[документация](readme.md)

#Установка и настройка Dvelum 3.x


##Системные требования


* Linux (при запуске под windows возможны проблемы, ведем работы над совместимостью);
* PHP 7.2  и выше;
* Mysql 5.5  и выше;
* веб-сервер Apache + mod_rewrite или Nginx + php-fpm;
* желательно наличие memcached.

##Установка c использованием  Composer
```
composer create-project  dvelum/dvelum
```

##Настройка  web-сервера

Предположим, что путь к проекту на вашем сервере /var/www/dvelum

**Apache**

```
<VirtualHost *:80>
    ServerName dvelum.local
    DocumentRoot /var/www/dvelum/www
    <Directory "/var/www/dvelum/www">
        Require all granted
        AllowOverride All
        Options +ExecCGI -Includes -Indexes
     </Directory>
</VirtualHost>
```
**Nginx**

```
upstream dvelum_backend{ 	    
    server unix:/path/to/php-fpm.sock; 	
}  	

server {
    server_name dvelum.local; 		
    listen 80; 	
    charset utf8; 		
    index index.php index.html;

    root $root_path;
    set $root_path /var/www/dvelum/www;
			 		
    location / { 		    
      root $root_path; 		    
      index index.php; 		   
      try_files $uri $uri/ @dvelum; 		
    } 		 		

   location ~* ^.+\.(ico|txt|jpg|jpeg|gif|png|svg|js|css|mp3|ogg|mpeg|avi|zip|gz|bz2|rar|swf)$ {
      root $root_path; 			
      access_log off; 			
      expires max; 		
   } 	

   location ~ .php$ { 		    
        index index.php;
        root $root_path;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        fastcgi_pass dvelum_backend;
        fastcgi_param DOCUMENT_ROOT $root_path;
        fastcgi_param SCRIPT_FILENAME $root_path$fastcgi_script_name;
        fastcgi_param PATH_TRANSLATED $root_path$fastcgi_script_name;
        include fastcgi_params;
   } 		

   location @dvelum{ 		    
        index index.php; 		    
        root $root_path;		    
        fastcgi_pass dvelum_backend;
        fastcgi_param DOCUMENT_ROOT $root_path;
        fastcgi_param SCRIPT_FILENAME $root_path/index.php;
        fastcgi_param PATH_TRANSLATED $root_path/index.php;
        include fastcgi_params;
   } 	
}
```
Прописать 127.0.0.1 dvelum.local в /etc/hosts для локальной разработки

Открыть браузер, запустить dvelum.local/install/, следовать инструкциям.

###Настройка после установки

####Настройка режима разработки

Платформа устанавливается с настройками режима разработки.

Во избежание проблем с наличием прав на запись в файлы, предлагаем на машине разработчика разрешить запись во все файлы и директории с установленной системой DVelum.

Для удобства анализа ajax запросов административной панели средствами подобными firebug рекомендуем отключить  CSRF токен
в application/config/local/backend.php:
'use_csrf_token' => false

Кэш

Желательно иметь установленный memcached, в этом случае необходимо указать настройки подключения к memcached в файле application/config/local/cache.php (создать скопировав из application/config/dist/cache.php) и перевести систему на работу с этим сервером, указав в файле application/config/local/main.php

 'use_cache' => true

#### Настройка PRODUCTION режима

Для того чтобы перевести систему в режим PRODUCTION, необходимо внести следующие изменения в конфигурацию системы в файле application/config/local/main.php (добавить параметры, либо изменить существующие при наличии):

Переключить режим работы:

'development' => false

Для увеличения производительности можно включить кэширование и использование карты классов:

'use_cache' => true

'useMap' => true