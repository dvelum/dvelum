<?php
$docRoot = dirname(dirname(dirname(__FILE__))) . '/www';
$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/' ,$docRoot);

define('DVELUM', true);
define('DVELUM_ROOT' , dirname($docRoot));

chdir(DVELUM_ROOT);

//===== loading kernel =========
/*
 * Including initial config
 */
$bootCfg = include DVELUM_ROOT . '/config/dist/init.php';
/*
 * Including Autoloader class
 */
require DVELUM_ROOT . '/system/library/Autoloader.php';
$autoloader = new Autoloader($bootCfg['autoloader']);
Config::setStorageOptions($bootCfg['config_storage']);

//==== Loading system ===========
/*
 * Reload storage options from local system
 */
$storageConfig = Config::storage()->get('config_storage.php')->__toArray();
$storageConfig['file_array'] = array(
    'paths' => array(
        './config/dist/',
        './config/local/',
        './test/config/',
    ),
    'write' =>  './test/config/',
    'apply_to' => false,
);

Config::setStorageOptions(
    $storageConfig
);
/*
 * Connecting main configuration file
 */
$config = Config::storage()->get('main.php');
$config->set('development' ,2);

/*
 * Disable op caching for test mode
 */
ini_set('opcache.enable', 0);

/*
 * Setting autoloader config
 */
$autoloaderCfg = $config->get('autoloader');
$autoloaderCfg['debug'] = true;
$autoloaderCfg['map'] = false;

$autoloader->setConfig($autoloaderCfg);

Registry::set('main', $config , 'config');

// clear test configs
File::rmdirRecursive('./test/config/' , false);

/*
 * Starting the application
 */
$app = new Application($config);
$app->setAutoloader($autoloader);
$app->init();

$dbObjectManager = new Db_Object_Manager();
foreach($dbObjectManager->getRegisteredObjects() as $object)
{
        echo 'build ' . $object . ' : ';
        $builder = new Db_Object_Builder($object);
        if($builder->build()){
                echo 'OK';
        }else{
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
        }
        echo "\n";
}