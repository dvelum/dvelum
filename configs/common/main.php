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
     */
    'development' => 1,

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
    'timezone' => 'Europe/Moscow',

    /**
     * Frontend controllers directories
     */
    'frontend_controllers_dirs' => ['Dvelum/App/Frontend','App/Frontend'],
    /**
     * Local controllers path
     */
    'local_controllers' => './application/classes/',
    /*
     * Frontend modules config file
     */
    'frontend_modules' => 'modules_frontend.php',
    /*
    * Use memcached
    */
    'use_cache' => false,
    /*
     * Show debug panel
     */
    'debug_panel' => false,
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
    'application' => '\\Dvelum\\App\\Application\\WebService',
);
