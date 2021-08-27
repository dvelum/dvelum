<?php

/*
 * DVelum console application
 * Return codes
 * 0 - Good
 * 1 - Empty URI
 * 2 - Wrong URI
 * 3 - Application Error
 */
if (isset($_SERVER['argc']) && $_SERVER['argc'] < 2) {
    exit(1);
}

$scriptStart = microtime(true);

$dvelumRoot = str_replace('\\', '/', __DIR__);
// should be without last slash
if ($dvelumRoot[strlen($dvelumRoot) - 1] == '/') {
    $dvelumRoot = substr($dvelumRoot, 0, -1);
}

define('DVELUM', true);
define('DVELUM_ROOT', $dvelumRoot);
define('DVELUM_CONSOLE', true);
define('DVELUM_WWW_PATH', DVELUM_ROOT . '/www/');

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
$configStorage->setConfig(ConfigFactory::storage()->get('config_storage.php')->__toArray());
/*
 * Connecting main configuration file
 */
$config = ConfigFactory::storage()->get('main.php');

$config->set('frontend_router', \Dvelum\App\Router\Path::class);

$_SERVER['DOCUMENT_ROOT'] = $config->get('wwwpath');

switch ($config->get('development')) {
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
$autoloaderCfg = $configStorage->get('autoloader.php')->__toArray();
$autoloaderCfg['debug'] = $config->get('development');

if (!isset($autoloaderCfg['useMap'])) {
    $autoloaderCfg['useMap'] = true;
}

if ($autoloaderCfg['useMap'] && $autoloaderCfg['map']) {
    $autoloaderCfg['map'] = require $configStorage->getPath($autoloaderCfg['map']);
} else {
    $autoloaderCfg['map'] = false;
}

$autoloader->setConfig($autoloaderCfg);


/*
 * Starting the application
 */
$appClass = $config->get('application');
if (!class_exists($appClass)) {
    throw new Exception('Application class ' . $appClass . ' does not exist! Check config "application" option!');
}


$diContainer = new DependencyContainer();
$diContainer->bind('config.main', $config);
$diContainer->bind(StorageInterface::class, $configStorage);
$diContainer->bind(Autoload::class, $autoloader);
$diContainer->bindArray($configStorage->get('dependency.php')->__toArray());

/**
 * @var Dvelum\Application $app
 */
$app = new $appClass($diContainer);

$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$serverRequest = $creator->fromArrays([
  'REQUEST_URI' => $_SERVER['argv'][1],
  'REQUEST_METHOD' => 'GET'
]);

$response = $psr17Factory->createResponse(200);

/**
 * @var Psr\Http\Message\ResponseInterface $resp
 */
$resp = $app->runConsole($serverRequest, $response);

(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($resp);