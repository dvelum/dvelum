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

    'jsCacheSysUrl' => 'js/syscache/',
    'jsCacheSysPath' => $wwwPath . 'js/syscache/',
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
     * Backend controllers  dir
     */
    'backend_controllers_dir' => 'Backend',
    /**
     * Frontend controllers  dir
     */
    'frontend_controllers_dir' => 'Frontend',
    /**
     * Local controllers path
     */
    'local_controllers' => './application/controllers/',
    /*
     * Frontend modules config file
     */
    'frontend_modules' => 'modules_frontend.php',
    /*
     * Blocks path
     */
    'blocks' => 'Block/',
    /*
     * Dictionary directory
     */
    'dictionary_folder' => 'dictionary/',
    /*
     * Temporary files directory
     */
    'tmp' => $docRoot . '/data/temp/',
    /*
     * the type of frontend router with two possible values:
     * 'Router_Module' — using tree-like page structure  (‘Pages’ section of the back-office panel);
     * 'Router_Path' — the router based on the file structure of client controllers.
     * 'Router_Config' - using frontend modules configuration
     */
    'frontend_router' => 'Router_Module', // 'Router_Module','Router_Path','Router_Config'
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
     * Debug panel configuration (Development mode)
     */
    'debug_panel' => array(
        'enabled' => false,
        'options' =>array(
            // cache requests
            'cache' => true,
            // sql queries list
            'sql' => false,
            // list of autoloaded classes
            'autoloader' => false,
            // list of included configs
            'configs' =>false,
            // list of included files
            'includes' => false,
        )
    ),
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
    'blockmanager_use_hardcache_time' => false,
    /*
     * www root
     */
    'wwwroot' => $wwwRoot,
    'wwwpath' => $wwwPath,
    /*
     * Directories for storing data base connection settings as per the system mode
     */
    'db_configs' => array(
        /* key as development mode code */
        0 => array(
            'title' => 'PRODUCTION',
            'dir' => 'db/prod/'
        ),
        1 => array(
            'title' => 'DEVELOPMENT',
            'dir' => 'db/dev/'
        ),
        2 => array(
            'title' => 'TEST',
            'dir' =>  'db/test/'
        )
    ),
    /*
     * Check modification time for template file. Invalidate cache
     */
    'template_check_mtime' => true,
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
        'enabled' => true,
        'path' => './modules',
        'resources_path' => $wwwPath . 'resources/',
        'resources_root' => $wwwRoot . 'resources/',
        'repo' => [
            [
                'id' => 'dvelum_official',
                'title' => 'DVelum official',
                'url' => 'https://addons.dvelum.net/api/modules/'
            ]
        ]
    ]
);
