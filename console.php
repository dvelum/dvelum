<?php
/*
 * DVelum console application
 * Return codes
 * 0 - Good
 * 1 - Empty URI
 * 2 - Wrong URI
 * 3 - Application Error
 */
if (isset($_SERVER['argc']) && $_SERVER['argc']!==2 ){
    exit(1);   
}

$scriptStart = microtime(true);

$dvelumRoot =  str_replace('\\', '/' , __DIR__);
// should be without last slash
if ($dvelumRoot[strlen($dvelumRoot) - 1] == '/')
    $dvelumRoot = substr($dvelumRoot, 0, -1);

define('DVELUM', true);
define('DVELUM_ROOT' ,$dvelumRoot);
define('DVELUM_CONSOLE', true);
define('DVELUM_WWW_PATH', DVELUM_ROOT . '/www/');

chdir(DVELUM_ROOT);

//===== loading kernel =========
/*
 * Including initial config
 */
$bootCfg = include DVELUM_ROOT . '/application/configs/dist/init.php';
/*
 * Including Autoloader class
 */
require DVELUM_ROOT . '/dvelum/library/Autoloader.php';
$autoloader = new Autoloader($bootCfg['autoloader']);

$configStorage = Config::storage();
$configStorage->setConfig($bootCfg['config_storage']);

//==== Loading system ===========
/*
 * Reload storage options from local system
 */
$configStorage->setConfig(Config::storage()->get('config_storage.php')->__toArray());
/*
 * Connecting main configuration file
 */
$config = Config::storage()->get('main.php');
$_SERVER['DOCUMENT_ROOT'] = $config->get('wwwpath');

/*
 * Disable op caching for development mode
 */
if($config->get('development')){
    ini_set('opcache.enable', 0);
}
/*
 * Setting autoloader config
 */
$autoloaderCfg = $config->get('autoloader');
$autoloaderCfg['debug'] = $config->get('development');

if(!isset($autoloaderCfg['useMap']))
    $autoloaderCfg['useMap'] = true;

if($autoloaderCfg['useMap'] && $autoloaderCfg['map'])
    $autoloaderCfg['map'] = require Config::storage()->getPath($autoloaderCfg['map']);
else
    $autoloaderCfg['map'] = false;

$autoloader->setConfig($autoloaderCfg);

/**
 * Enable Zend Framework 1.x library support
 */
set_include_path(get_include_path() . PATH_SEPARATOR . $config->get('vendor_lib'));

Registry::set('main', $config , 'config');
/*
 * Starting the application
 */
$app = new Application($config);
$app->setAutoloader($autoloader);
$app->init();

Request::getInstance()->setUri($_SERVER['argv'][1]);

$app->run();