<?php
$docRoot = DVELUM_ROOT;
$wwwPath = DVELUM_WWW_PATH;
$wwwRoot = '/';
$language = 'en';
return array(
    'docroot' => $docRoot,
    'wwwPath' => $wwwPath,
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
     * Write SQL commands when updating Database structure.
     * It can help to determine if there have been performed any rename operations.
     * Please note that renaming operations in ORM interface causes loss of data
     * during server synchronization, so it's better to use SQL log.
     */
    'use_orm_build_log' => true,
    /*
     * ORM SQL logs path
     */
    'orm_log_path' => $docRoot . '/data/logs/orm/',
    /*
     * Background tasks log path
     */
    'task_log_path' => $docRoot . '/data/logs/task/',
    /*
     * ORM system object used as links storage
     */
    'orm_links_object' => 'Links',
    /*
     * ORM system object used as history storage
     */
    'orm_history_object' => 'Historylog',
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
     * Сlear the object version history when deleting an object.
     * The recommended setting is “false”.  Thus, even though the object has been deleted,
     * it can be restored from the previous control system revision.
     * If set to "true", the object and its history will be  totally removed. However,
     * this allows you to get rid of redundant records in the database.
     */
    'vc_clear_on_delete' => false,
    /*
     * Main directory for config files
     */
    'configs' => '', // configs path $docRoot . '/config/',
    /*
    * ORM configs directory
    */
    'object_configs' => 'objects/',
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
    'tmp' => './temp/',
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
    /*
     * Hard caching time (without validation) for frontend , seconds
     */
    'frontend_hardcache' => 30,
    'themes' => 'public/',
    // Autoloader config
    'autoloader' => array(
        // Paths for autoloading
        'paths' => array(
            './application/controllers',
            './application/models',
            './application/library',
            './dvelum/app',
            './dvelum/library',
            './vendor'
        ),
        /*
        * Use class maps
        */
        'useMap' => false,
        // Use class map (Reduce IO load during autoload)
        // Class map file path (string / false)
        'map' => 'classmap.php',
    ),
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
     * Use the console command to compile the file system map
     * (accelerates the compilation process; works only on Linux systems;
     * execution of the system function should be allowed).
     */
    'deploy_use_console' => false,
    /*
     *  Use hard cache expiration time defined in frontend_hardcache for caching blocks;
     *  allows to reduce the cache time of dynamic blocks;
     *  is used if there are not enough triggers for cache invalidation
     */
    'blockmanager_use_hardcache_time' => false,
    /*
     * Use foreign keys
     */
    'foreign_keys' => false,
    /*
     * www root
     */
    'wwwroot' => $wwwRoot,
    'wwwpath' => $wwwPath,
    /*
     * Get real rows count for innodb tables (COUNT(*))
     * Set it "false" for large data volumes
     */
    'orm_innodb_real_rows_count' => false,
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
     * ORM system object used as version storage
     */
    'orm_version_object' => 'Vc',
    /*
     * Db_Object for error log 
     */
    'error_log_object' => 'error_log',
    /*
     * Log Db_Object errors
     */
    'db_object_error_log' => true,
    'db_object_error_log_path' => $docRoot . '/data/logs/error/db_object.error.log',
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
