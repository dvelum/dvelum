<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2013  Kirill A Egorov
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
define('DVELUM', true);
define('DVELUM_ROOT' , str_replace('\\', '/' , __DIR__));
/*
 * Httponly cookies (brakes the uplodify multy file uploader)
 */
ini_set("session.cookie_httponly", 1);
/*
 * Turning on output buffering
 */
ob_start();
/*
 * Connecting main configuration file
 */
$config = include './system/config/main.php';
/*
 * Disable op caching for development mode
 */
if($config['development']){
	ini_set('opcache.enable', 0);
}
/*
 * Including Autoloader class
 */
require './system/library/Autoloader.php';
/*
 * Setting autoloader config
 */
$autoloaderCfg = $config['autoloader'];
$autoloaderCfg['debug'] = $config['development'];

if(!isset($autoloaderCfg['useMap']))
    $autoloaderCfg['useMap'] = true;

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
$app->run();
/*
 * Clean the buffer and send response
 */
echo ob_get_clean();
/*
 * Print debug information (development mode)
 */
if($config['development'] && $config['debug_panel'])
{
	Debug::setScriptStartTime($scriptStart);
	Debug::setLoadedClasses($autoloader->getLoadedClasses());
	echo Debug::getStats();
}
exit;