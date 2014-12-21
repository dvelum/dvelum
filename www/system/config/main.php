<?php
/**
 *  Do not try to install platform into the server Document Root sub directory
 */
$docRoot = DVELUM_ROOT;

$len = strlen($docRoot);

// should be without last slash
if ($docRoot[$len - 1] == '/')
    $docRoot = substr($docRoot, 0, -1);

$language = 'en';
return array(
    'docroot' => $docRoot,
    /*
     * Development mode
     * 0 - production
     * 1 - development
     * 2 - test (development mode + test DB)
     */
    'development' => false,
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
    'orm_log_path' => $docRoot . '/.log/orm/',
    /*
     * Background tasks log path
     */
    'task_log_path' => $docRoot . '/.log/task/',
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
    'uploads' => $docRoot . '/media/',
    /*
     * Admin panel URL
     * For safety reasons adminPath may be changed, however,
     * keep in mind that IDE builds full paths in the current version,
     * thus, they would have to be manually updated in the projects.
     */
    'adminPath' => 'adminarea',
    /*
     * Templates directory
     */
    'templates' => $docRoot . '/templates/',
    /*
     * Url paths delimiter  "_" , "-" or "/"
     */
    'urlDelimiter' => '/',
    'urlExtension' => '.html',
    /*
     * System language
     * Please note. Changing the language will switch ORM storage settings.
     */
    'language' => $language,
    'system' => $docRoot . '/system/',
    'lang_path' => $docRoot . '/system/lang/',
    'js_lang_path' => $docRoot . '/js/lang/',
    'salt' => 'ThSc-9086',
    'timezone' => 'Europe/Moscow',

    'jsCacheUrl' => 'js/cache/',
    'jsCachePath' => $docRoot . '/js/cache/',

    'jsCacheSysUrl' => 'js/syscache/',
    'jsCacheSysPath' => './js/syscache/',
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
    'configs' => $docRoot . '/system/config/', // configs path
    /*
    * ORM configs directory
    */
    'object_configs' => $docRoot . '/system/config/objects/',
    /*
     * Report configs directory
     */
    'report_configs' => $docRoot . '/system/config/reports/',
    /*
     * Modules directory
     */
    'modules' => $docRoot . '/system/config/modules/',
    /*
     * Backend modules config file
     */
    'backend_modules' => $docRoot . '/system/config/modules/' . $language . '/backend_modules.php',
    /*
     * Backend controllers path
     */
    'backend_controllers' => $docRoot . '/system/app/Backend/',
    /*
     * Frontend controllers path
     */
    'frontend_controllers' => $docRoot . '/system/app/Frontend/',
    /*
     * Frontend modules config file
     */
    'frontend_modules' => $docRoot . '/system/config/modules/' . $language . '/frontend_modules.php',
    /*
     * Application path
     */
    'application_path' => $docRoot . '/system/app/',
    /*
     * Blocks path
     */
    'blocks' => $docRoot . '/system/app/Block/',
    /*
     * Dictionary configs directory depending on localization
     */
    'dictionary' => $docRoot . '/system/config/dictionary/' . $language . '/',
    /*
     * Dictionary directory
     */
    'dictionary_folder' => $docRoot . '/system/config/dictionary/',
    /*
     * Backups directory
     */
    'backups' => $docRoot . '/.backups/',
    'tmp' => $docRoot . '/.tmp/',
    'mysqlExecPath' => 'mysql',
    'mysqlDumpExecPath' => 'mysqldump',
    /*
     * the type of frontend router with two possible values:
     * 'module' — using tree-like page structure  (‘Pages’ section of the administrative panel);
     * 'path' — the router based on the file structure of client controllers.
     */
    'frontend_router_type' => 'module', // 'module','path','config'
    /*
    * Use memcached
    */
    'use_cache' => false,
    /*
     * Hard caching time (without validation) for frondend , seconds
     */
    'frontend_hardcache' => 30,
    'themes' => $docRoot . '/templates/public/',
    'usersOnline' => false, // Collect users online info,
    // Autoloader config
    'autoloader' => array(
        // Paths for autoloading
        'paths' => array(
            './system/rewrite',
            './system/app',
            './system/library'
        ),
        /*
        * Use class map
        */
        'useMap' => false,
        /*
          *	Use precompiled code packages
          *	requires useMap property to be set to true
          */
        'usePackages' => false,
        // Use class map (Reduce IO load during autoload)
        // Class map file path (string / false)
        'map' => $docRoot . '/system/config/class_map.php',
        // Class map file path (with packages)
        'mapPackaged' => $docRoot . '/system/config/class_map_packaged.php',
        // Packages config path
        'packagesConfig' => $docRoot . '/system/config/packages.php'
    ),
    /*
     * Stop the site with message "Essential maintenance in progress. Please check back later."
     */
    'maintenance' => false,
    /*
     * Show debug panel (development mode)
     */
    'debug_panel' => false,
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
     * Allow external modules
     */
    'allow_externals' => false,
    /*
     * www root
     */
    'wwwroot' => '/',
    /*
     * External modules path (Experimental)
    */
    'external_modules' => './system/external/',
    /*
     * Log Db_Object errors
     */
    'db_object_error_log' => true,
    'db_object_error_log_path' => $docRoot . '/.log/error/db_object.error.log',
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
            'dir' => $docRoot . '/system/config/db/prod/'
        ),
        1 => array(
            'title' => 'DEVELOPMENT',
            'dir' => $docRoot . '/system/config/db/dev/'
        ),
        2 => array(
            'title' => 'TEST',
            'dir' => $docRoot . '/system/config/db/test/'
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
    'erorr_log_object' => 'error_log'
);
