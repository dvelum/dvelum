<?php
$dvelumRoot =  str_replace('\\', '/' ,  dirname(dirname(dirname(__FILE__))));
// should be without last slash
if ($dvelumRoot[strlen($dvelumRoot) - 1] == '/')
    $dvelumRoot = substr($dvelumRoot, 0, -1);

define('DVELUM', true);
define('DVELUM_ROOT' ,$dvelumRoot);
define('DVELUM_WWW_PATH', $dvelumRoot.'/www');
$_SERVER['DOCUMENT_ROOT'] = DVELUM_WWW_PATH;


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
Config::setStorageOptions($bootCfg['config_storage']);

//==== Loading system ===========
/*
 * Reload storage options from local system
 */
$storageConfig = Config::storage()->get('config_storage.php')->__toArray();
$storageConfig['file_array'] = array(
    'paths' => array(
        './application/configs/dist/',
        './application/configs/local/',
        './tests/configs/',
    ),
    'write' =>  './tests/configs/',
    'apply_to' => './application/configs/dist/',
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
File::rmdirRecursive('./tests/configs/' , false);

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