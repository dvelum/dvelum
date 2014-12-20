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

$_SERVER['DOCUMENT_ROOT'] = __DIR__.'/www';
chdir(__DIR__.'/www');

define('DVELUM', true);
define('DVELUM_CONSOLE', true);
define('DVELUM_ROOT' , __DIR__.'/www');

/*
 * Connecting main configuration file
*/
$config = include './system/config/main.php';
/*
 * Including Autoloader class
*/
require $config['docroot'].'/system/library/Autoloader.php';
/*
 * Setting autoloader config
 */
$autoloaderCfg = $config['autoloader'];
$autoloaderCfg['debug'] = $config['development'];

if($autoloaderCfg['useMap'] && $autoloaderCfg['usePackages'] && $autoloaderCfg['mapPackaged'])
	$autoloaderCfg['map'] = require $autoloaderCfg['mapPackaged'];
elseif($autoloaderCfg['useMap'] && !$autoloaderCfg['usePackages'] && $autoloaderCfg['map'])
	$autoloaderCfg['map'] = require $autoloaderCfg['map'];
else 
   $autoloaderCfg['map'] = false;

$autoloader = new Autoloader($autoloaderCfg);
/**
 * Convert the data of main_config file
 * in to the general form of configuration
 * and save a reference for it (for convenience)
 * @var Config_Simple $appConfig
 */
$appConfig = Config::factory(Config::Simple, 'main');
$appConfig->setData($config);
Registry::set('main', $appConfig , 'config');

/*
 * Starting the application
 */
$app = new Application($appConfig);
$app->setAutoloader($autoloader);

Request::getInstance()->setUri($_SERVER['argv'][1]);

$app->run();