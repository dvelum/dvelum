<?php
$skipBuild = false;
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
require DVELUM_ROOT . '/extensions/dvelum-core/src/Dvelum/Autoload.php';
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
$autoloaderCfg['debug'] = $config->get('development');

if(!isset($autoloaderCfg['useMap']))
    $autoloaderCfg['useMap'] = true;

if($autoloaderCfg['useMap'] && $autoloaderCfg['map'])
    $autoloaderCfg['map'] = require ConfigFactory::storage()->getPath($autoloaderCfg['map']);
else
    $autoloaderCfg['map'] = false;

$autoloader->setConfig($autoloaderCfg);

/*
 * Starting the application
 */
$appClass = $config->get('application');
if(!class_exists($appClass))
    throw new Exception('Application class '.$appClass.' does not exist! Check config "application" option!');

$app = new $appClass($config);
$app->setAutoloader($autoloader);
$app->runTestMode();

if(!$skipBuild) {
    $dbObjectManager = new \Dvelum\Orm\Record\Manager();
    foreach ($dbObjectManager->getRegisteredObjects() as $object) {
        echo 'build ' . $object . ' : ';
        $builder = \Dvelum\Orm\Record\Builder::factory($object);
        if ($builder->build(false)) {
            echo 'OK';
        } else {
            echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
        }

        if (!\Dvelum\Orm\Record\Config::factory($object)->isDistributed()) {
            echo ' clear';
            $model = \Dvelum\Orm\Model::factory($object);
            $db = $model->getDbConnection();
            $db->query('SET FOREIGN_KEY_CHECKS=0;');
            $db->delete($model->table());
            $db->query('SET FOREIGN_KEY_CHECKS=1;');
        }

        echo "\n";
    }
    echo PHP_EOL . 'BUILD FOREIGN KEYS' . PHP_EOL . PHP_EOL;
    foreach ($dbObjectManager->getRegisteredObjects() as $object) {
        echo 'build ' . $object . ' : ';
        $builder = \Dvelum\Orm\Record\Builder::factory($object);
        if ($builder->build(true)) {
            echo 'OK';
        } else {
            echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
        }
        echo "\n";
    }
    echo 'BUILD SHARDS ' . PHP_EOL;

    $sharding = \Dvelum\Config::storage()->get('sharding.php');
    $shardsFile = $sharding->get('shards');
    $shardsConfig = \Dvelum\Config::storage()->get($shardsFile, true, false);
    $registeredObjects = $dbObjectManager->getRegisteredObjects();

    foreach ($shardsConfig as $item) {
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

            if ($builder->build(false, true)) {
                echo 'OK' . PHP_EOL;
            } else {
                $success = false;
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
            }

            $model = \Dvelum\Orm\Model::factory($object);
            $db = $model->getDbShardConnection($shardId);
            $db->query('SET FOREIGN_KEY_CHECKS=0;');
            $db->delete($model->table());
            $db->query('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    foreach ($shardsConfig as $item) {
        $shardId = $item['id'];
        echo "\t" . 'BUILD KEYS ' . $shardId . ' ' . PHP_EOL;

        foreach ($registeredObjects as $index => $object) {
            echo "\t\t" . $object . ' : ';

            $builder = \Dvelum\Orm\Record\Builder::factory($object);
            $builder->setConnection(\Dvelum\Orm\Model::factory($object)->getDbShardConnection($shardId));

            if ($builder->build(true, true)) {
                echo 'OK' . PHP_EOL;
            } else {
                $success = false;
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
            };
        }
    }
}


// init default objects

$session = \Dvelum\App\Session\User::factory();
$session->setId(1);
$session->setAuthorized();

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