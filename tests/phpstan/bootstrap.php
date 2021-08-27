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
 * Register composer autoload
 */
require DVELUM_ROOT . '/vendor/autoload.php';
/*
 * Including Autoloader class
 */
require_once DVELUM_ROOT . '/extensions/dvelum-core/src/Dvelum/Autoload.php';
$autoloader = new \Dvelum\Autoload($bootCfg['autoloader']);

use Dvelum\Autoload;
use Dvelum\Config\Factory as ConfigFactory;
use Dvelum\Config\Storage\StorageInterface;
use Dvelum\DependencyContainer;

$configStorage = ConfigFactory::storage();
$configStorage->setConfig($bootCfg['config_storage']);

//==== Loading system ===========
/*
 * Reload storage options from local system
 */
$configStorage->setConfig($configStorage->get('config_storage.php')->__toArray());

/*
 * Connecting main configuration file
 */
$config = $configStorage->get('main.php');
$config->set('development', 2);
$configStorage->addPath('./application/configs/test/');

/*
 * Setting autoloader config
 */
$autoloaderCfg = $configStorage->get('autoloader.php')->__toArray();
$autoloaderCfg['psr-4']['Dvelum'] = DVELUM_ROOT.'/tests/unit/dvelum2/Dvelum';
$autoloader->setConfig($autoloaderCfg);


/*
 * Starting the application
 */
$appClass = $config->get('application');
if(!class_exists($appClass))
    throw new Exception('Application class '.$appClass.' does not exist! Check config "application" option!');

$diContainer= new DependencyContainer();
$diContainer->bind('config.main', $config);
$diContainer->bind(StorageInterface::class, $configStorage);
$diContainer->bind(Autoload::class, $autoloader);
$diContainer->bindArray($configStorage->get('dependency.php')->__toArray());

/**
 * @var \Dvelum\Application $app
 */
$app = new $appClass($diContainer);
$app->runTestMode();