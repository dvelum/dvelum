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
$bootCfg = include DVELUM_ROOT . '/application/configs/dist/init.php';
/*
 * Including Autoloader class
 */
require DVELUM_ROOT . '/dvelum2/Dvelum/Autoload.php';
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

/*
 * Disable op caching for development mode
 */
if($config->get('development')){
    ini_set('opcache.enable', 0);
    $configStorage->setConfig(['debug' => true]);
}
/*
 * Setting autoloader config
 */
$autoloaderCfg = ConfigFactory::storage()->get('autoloader.php')->__toArray();
$autoloaderCfg['debug'] = $config->get('development');

if(!isset($autoloaderCfg['useMap']))
    $autoloaderCfg['useMap'] = true;

if($autoloaderCfg['useMap'] && $autoloaderCfg['map'])
    $autoloaderCfg['map'] = require ConfigFactory::storage()->getPath($autoloaderCfg['map']);
else
    $autoloaderCfg['map'] = false;

$autoloader->setConfig($autoloaderCfg);

/*
 * Register composer autoload
 */
if($config->get('use_composer_autoload') && file_exists(__DIR__ . '/vendor/autoload.php')){
    require __DIR__ . '/vendor/autoload.php';
}

/*
 * Starting the application
 */
$appClass = $config->get('application');
if(!class_exists($appClass))
    throw new Exception('Application class '.$appClass.' does not exist! Check config "application" option!');

\Dvelum\File::rmdirRecursive('./tests/data/configs/' , false);
\Dvelum\File::copyDir('./tests/data/test_configs/', './tests/data/configs/');
\Dvelum\File::copyDir('./tests/data/test_objects/', './tests/data/configs/objects/');

$storage = \Dvelum\Config::storage();
$storage->addPath('./tests/data/configs/');

$app = new $appClass($config);
$app->setAutoloader($autoloader);
$app->init();

$dbObjectManager = new \Dvelum\Orm\Record\Manager();
foreach($dbObjectManager->getRegisteredObjects() as $object)
{
        echo 'build ' . $object . ' : ';
        $builder = \Dvelum\Orm\Record\Builder::factory($object);
        if($builder->build(false)){
            echo 'OK';
        }else{
           echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
        }
        echo "\n";
}
echo  PHP_EOL.'BUILD FOREIGN KEYS' . PHP_EOL. PHP_EOL;
foreach($dbObjectManager->getRegisteredObjects() as $object)
{
    echo 'build ' . $object . ' : ';
    $builder = \Dvelum\Orm\Record\Builder::factory($object);
    if($builder->build(true)){
        echo 'OK';
    }else{
        echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
    }
    echo "\n";
}
echo 'BUILD SHARDS ' . PHP_EOL;

$sharding = \Dvelum\Config::storage()->get('sharding.php');
$shardsFile = $sharding->get('shards');
$shardsConfig = \Dvelum\Config::storage()->get($shardsFile, true, false);
$registeredObjects = $dbObjectManager->getRegisteredObjects();

foreach ($shardsConfig as $item)
{
    $shardId = $item['id'];
    echo "\t" . 'BUILD ' . $shardId . ' ' . PHP_EOL;

    foreach ($registeredObjects as $index => $object) {
        if (!\Dvelum\Orm\Record\Config::factory($object)->isDistributed()) {
            unset($registeredObjects[$index]);
            continue;
        }

        echo "\t\t" . $object . ' : ';

        $builder = \Dvelum\Orm\Record\Builder::factory($object);
        $builder->setConnection(\Dvelum\Orm\Model::factory($object)->getDbShardConnection($shardId));
        if ($builder->build(true, true)) {
            echo 'OK' . PHP_EOL;
        } else {
            $success = false;
            echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
        }
    }
}



// init default objects

$session = \Dvelum\App\Session\User::factory();
$session->setId(1);
$session->setAuthorized();

$modelContent = \Dvelum\Orm\Model::factory('Page');
$modelContent->getDbConnection()->delete($modelContent->table());

$modelUser = \Dvelum\Orm\Model::factory('User');
$modelUser->getDbConnection()->delete($modelUser->table());

$modelGroup = \Dvelum\Orm\Model::factory('Group');
$modelGroup->getDbConnection()->delete($modelGroup->table());

$group = \Dvelum\Orm\Record::factory('Group');
$group->setInsertId(1);
$group->setValues(array('title' => date('YmdHis'), 'system' =>false));
$group->save();

$user = \Dvelum\Orm\Record::factory('User');
$user->setInsertId(1);
$user->setValues(array(
        'login' => uniqid(date('YmdHis')),
        'pass' => '111',
        'email' => uniqid(date('YmdHis')) . '@mail.com',
        'enabled' => 1,
        'admin' => 1,
        'name'=>'Test User',
        'group_id' => $group->getId()
    ));
$user->save();