<?php
/*
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
 * Copyright (C) 2011-2015  Kirill A Egorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/*
 * Startup time
 */
$scriptStart = microtime(true);

$dvelumRoot =  str_replace('\\', '/' , __DIR__);
// should be without last slash
if ($dvelumRoot[strlen($dvelumRoot) - 1] == '/')
    $dvelumRoot = substr($dvelumRoot, 0, -1);

define('DVELUM', true);
define('DVELUM_ROOT' ,$dvelumRoot);

chdir(DVELUM_ROOT);

/*
 * Httponly cookies
 */
ini_set("session.cookie_httponly", 1);
/*
 * Turning on output buffering
 */
ob_start();

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

switch ($config->get('development')){
    // production
    case 0 :
        $configStorage->addPath('./application/configs/prod/');
        break;
    // development
    case 1 :
        $configStorage->addPath('./application/configs/dev/');
        /*
         * Disable op caching for development mode
         */
        ini_set('opcache.enable', 0);
        $configStorage->setConfig(['debug' => true]);
        break;
    // test
    case 2 :
        $configStorage->addPath('./application/configs/test/');
        break;
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
 * Installation mode
 */
if($config->get('development') === 3){
    if(strpos($_SERVER['REQUEST_URI'],'install')!==false){
        $controller = new Dvelum\App\Install\Controller();
        $controller->setAutoloader($autoloader);
        $controller->run();
        exit;
    }else{
        echo 'DVelum software is not installed';
        exit;
    }
}




/*
 * Starting the application
 */
$appClass = $config->get('application');
if(!class_exists($appClass))
    throw new Exception('Application class '.$appClass.' does not exist! Check config "application" option!');

$app = new $appClass($config);
$app->setAutoloader($autoloader);
$app->run();
/*
 * Clean the buffer and send response
 */
echo ob_get_clean();
/*
 * Print debug information (development mode)
 */
if($config['development'] && $config->get('debug_panel') && !\Dvelum\Request::factory()->isAjax())
{
    $debugCfg = \Dvelum\Config::storage()->get('debug_panel.php');
    \Dvelum\Debug::setScriptStartTime($scriptStart);
    \Dvelum\Debug::setLoadedClasses($autoloader->getLoadedClasses());
    \Dvelum\Debug::setLoadedConfigs(\Dvelum\Config::storage()->getDebugInfo());
    echo \Dvelum\Debug::getStats($debugCfg->get('options'));
}
exit;