<?php

$dvelumRoot =  str_replace('\\', '/' ,  dirname(dirname(dirname(__FILE__))));
// should be without last slash
if ($dvelumRoot[strlen($dvelumRoot) - 1] == '/')
    $dvelumRoot = substr($dvelumRoot, 0, -1);

define('DVELUM', true);
define('DVELUM_ROOT' ,$dvelumRoot);
define('DVELUM_WWW_PATH', $dvelumRoot.'/www/');

$_SERVER['DOCUMENT_ROOT'] = DVELUM_WWW_PATH;

chdir(DVELUM_ROOT);

//===== loading kernel =========
/*
 * Including initial config
 */
$bootCfg = include DVELUM_ROOT . '/application/configs/common/dist/init.php';
/*
 * Including Autoloader class
 */
require_once DVELUM_ROOT . '/dvelum2/Dvelum/Autoload.php';
$autoloader = new \Dvelum\Autoload($bootCfg['autoloader']);

use \Dvelum\Config\Factory as ConfigFactory;

$configStorage = ConfigFactory::storage();
$configStorage->setConfig($bootCfg['config_storage']);

//==== Loading system ===========
/*
 * Reload storage options from local system
 */
$configStorage->setConfig(ConfigFactory::storage()->get('config_storage.php')->__toArray());

/*
 * Connecting main configuration file
 */
$config = ConfigFactory::storage()->get('main.php');
$config->set('development', 2);
$configStorage->addPath('./application/configs/test/');

/*
 * Setting autoloader config
 */
$autoloaderCfg = ConfigFactory::storage()->get('autoloader.php')->__toArray();
$autoloader->setConfig($autoloaderCfg);