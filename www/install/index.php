<?php
  define('DVELUM_ROOT' , str_replace('\\', '/' , dirname(__DIR__)));
  date_default_timezone_set('Europe/Moscow');
	include './app/Install/Controller.php';
	/**
	 * Connect an autoloader
	 */
	include '../system/library/Autoloader.php';
	/**
	 *  Add the library path
	 */
	$autoloader = new Autoloader(array(
		'paths'=>array(
			'../system/library',
			'../system/app'
		),
		'useMap'=>false	
	));
	
	$controller = new Install_Controller();
	$controller->run();