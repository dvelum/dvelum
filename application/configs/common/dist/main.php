<?php
$docRoot = DVELUM_ROOT;
$wwwPath = DVELUM_WWW_PATH;
$wwwRoot = '/';
$language = 'en';
return array(
    'docRoot' => $docRoot,
    'wwwPath' => $wwwPath,
    'wwwRoot' => $wwwRoot,
    /*
     * Development mode
     * 0 - production
     * 1 - development
     * 2 - test (development mode + test DB)
     * 3 - installation mode
     */
    'development' => 3,
    /*
     * Development version (used by use_orm_build_log)
     */
    'development_version' => '0.1',
    /*
     * Background tasks log path
     */
    'task_log_path' => $docRoot . '/data/logs/task/',
    /*
     * File uploads path
     */
    'uploads' => $wwwPath . 'media/',
    /*
     * Back-office panel URL
     * For safety reasons adminPath may be changed, however,
     * keep in mind that IDE builds full paths in the current version,
     * thus, they would have to be manually updated in the projects.
     */
    'adminPath' => 'backoffice',
    /*
     * Templates directory
     */
    'templates' => $docRoot . '/application/templates/',
    /*
     * Url paths delimiter  "_" , "-" or "/"
     */
    'urlDelimiter' => '/',
    'urlExtension' => '',
    /*
     * System language
     * Please note. Changing the language will switch ORM storage settings.
     */
    'language' => $language,
    /**
     * Localization files path
     */
    'js_lang_path' => $wwwPath . 'js/lang/',
    'salt' => 'ThSc-9086',
    'timezone' => 'Europe/Moscow',

    'jsPath' => $wwwPath . 'js/',
    'jsCacheUrl' => 'js/cache/',
    'jsCachePath' => $wwwPath . 'js/cache/',

    'cssPath' => $wwwPath . 'css/',
    'cssCacheUrl' => 'css/cache/',
    'cssCachePath' => $wwwPath . 'css/cache/',

    /*
     * Main directory for config files
     */
    'configs' => '', // configs path $docRoot . '/config/',
    /*
     * Report configs directory
     */
    'report_configs' => 'reports/',
    /*
     * Modules directory
     */
    'modules' => 'modules/',
    /*
     * Backend modules config file
     */
    'backend_modules' => 'modules_backend.php',
    /**
     * Backend controllers directories
     */
    'backend_controllers_dirs' => ['Dvelum/App/Backend','App/Backend','Backend'],
    /**
     * Frontend controllers directories
     */
    'frontend_controllers_dirs' => ['Dvelum/App/Frontend','App/Frontend', 'Frontend'],
    /**
     * Local controllers path
     */
    'local_controllers' => './application/classes/',
    /**
     * Local models path
     */
    'local_models' => './application/classes/app/',
    /*
     * Frontend modules config file
     */
    'frontend_modules' => 'modules_frontend.php',
    /*
     * Blocks path
     */
    'blocks' => ['Block/','App/Block','Dvelum/App/Block'],
    /*
     * Dictionary directory
     */
    'dictionary_folder' => 'dictionary/',
    /*
     * Temporary files directory
     */
    'tmp' => $docRoot . '/temp/',
    /*
    * Use memcached
    */
    'use_cache' => false,
    'themes' => 'public/',
    /*
     * Stop the site with message "Essential maintenance in progress. Please check back later."
     */
    'maintenance' => false,
    /*
     * Show debug panel
     */
    'debug_panel' => false,
    /*
     * HTML WYSIWYG Editor
     * default  - ckeditor
     */
    'html_editor' => 'ckeditor',
    /*
     *  Use hard cache expiration time defined in frontend_hardcache for caching blocks;
     *  allows to reduce the cache time of dynamic blocks;
     *  is used if there are not enough triggers for cache invalidation
     */
    'blockmanager_hard_cache' => false,
    /*
     * www root
     */
    'wwwroot' => $wwwRoot,
    'wwwpath' => $wwwPath,
    /**
     * Relative path to DB configs
     */
    'db_config_path' => 'db/',
    /*
     * Directories for storing data base connection settings as per the system mode
     */
    'db_configs' => array(
        /* key as development mode code */
        0 => array(
            'title' => 'PRODUCTION',
            'dir' => './application/configs/prod/db/'
        ),
        1 => array(
            'title' => 'DEVELOPMENT',
            'dir' => './application/configs/dev/db/'
        ),
        2 => array(
            'title' => 'TEST',
            'dir' =>  './application/configs/test/db/'
        )
    ),
    /*
     * Application class
     */
    'application' => '\\Dvelum\\App\\Application',
    /*
     * Vendor library path
     */
    'vendor_lib'=> $docRoot . '/vendor/',
    /*
     * Default Authentication provider. Or uncomment 'uprovider' select in login template.
     */
    'default_auth_provider' => 'dvelum',
    /*
     * Fallback Authentification provider. If empty or not set - fallback disabled.
     */
    'fallback_auth_provider' => '',
    /*
     * External modules configuration
     */
    'externals' =>[
        'path' => './modules',
        'resources_path' => $wwwPath . 'resources/',
        'resources_root' => $wwwRoot . 'resources/',
        'repo' => [
            'dvelum-packagist'=> [
                'title' => 'DVelum official',
                'adapter' => '\\Dvelum\\Externals\\Client\\Packagist',
                'adapterConfig' => [
                    'vendor' => 'dvelum',
                    'type'=>'dvelum-module'
                ]
            ]
        ]
    ],
    /*
     * Composer autoloader breaks autoloaders queue with prepend option by default
     * it makes impossible to override vendor classes. It also slow. Using autoloader config is preferable
     */
    'use_composer_autoload' => true
);
