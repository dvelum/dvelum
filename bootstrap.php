<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
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

/*
 * Installation mode
 */
if($config->get('development') === 3 && strpos($_SERVER['REQUEST_URI'],'install')!==false){
    $controller = new Install_Controller();
    $controller->setAutoloader($autoloader);
    $controller->run();
    exit;
}

Registry::set('main', $config , 'config');
/*
 * Starting the application
 */
$app = new Application($config);
$app->setAutoloader($autoloader);
$app->init();

$app->run();
/*
 * Clean the buffer and send response
 */
echo ob_get_clean();
/*
 * Print debug information (development mode)
 */
if($config['development'])
{
    $debugCfg = $config->get('debug_panel');
    if($debugCfg['enabled']){
        Debug::setScriptStartTime($scriptStart);
        Debug::setLoadedClasses($autoloader->getLoadedClasses());
        Debug::setLoadedConfigs(Config::storage()->getDebugInfo());
        echo Debug::getStats($debugCfg['options']);
    }
}
exit;