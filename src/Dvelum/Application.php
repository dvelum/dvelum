<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2020  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace Dvelum;

use Dvelum\{Debug,
    Externals,
    Request,
    Response,
    Resource,
    View,
    Autoload,
    Config,
    Config\ConfigInterface,
    Db,
    Lang,
    Utils,
    Service,
    Cache\CacheInterface};


/**
 * Application - is the main class that initializes system configuration
 * settings. The system starts working with running an object of this class.
 * @author Kirill A Egorov
 */
abstract class Application
{
    const MODE_PRODUCTION = 0;
    const MODE_DEVELOPMENT = 1;
    const MODE_TEST = 2;

    /**
     * Application config
     * @var Config\ConfigInterface
     */
    protected $config;

    /**
     * @var CacheInterface|null
     */
    protected $cache;

    /**
     * @var boolean
     */
    protected $initialized = false;

    /**
     * @var Autoload
     */
    protected $autoloader;

    /**
     * The constructor accepts the main configuration object as an argument
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Inject Auto-loader
     * @param Autoload $autoloader
     */
    public function setAutoloader(Autoload $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * Initialize the application, configure the settings, inject dependencies
     * Adjust the settings necessary for running the system
     */
    protected function init()
    {
        if ($this->initialized) {
            return;
        }

        $config = & $this->config->dataLink();

        date_default_timezone_set($config['timezone']);

        /*
         * Init cache connection
         */
        $cache = $this->initCache();
        $this->cache = $cache;

        /*
         * Init database connection
         */
        $dbManager = $this->initDb();


        $request = Request::factory();
        $request->setConfig(Config\Factory::create([
            'delimiter' => $config['urlDelimiter'],
            'extension' => $config['urlExtension'],
            'wwwRoot' => $config['wwwRoot']
        ]));

        /*
         * Register Services
         */
        Service::register(
            Config::storage()->get('services.php'),
            Config\Factory::create([
                'appConfig' => $this->config,
                'dbManager' => $dbManager,
                'cache' => $cache
            ])
        );

        $this->initialized = true;
    }


    /**
     * Initialize Cache connections
     * @return CacheInterface | null
     * @throws \Exception
     */
    protected function initCache(): ? CacheInterface
    {
        if (!$this->config->get('use_cache')) {
            return null;
        }

        $cacheConfig = Config::storage()->get('cache.php')->__toArray();
        $cacheManager = new Cache\Manager();

        foreach ($cacheConfig as $name => $cfg) {
            if ($cfg['enabled']) {
                $cacheManager->connect($name, $cfg);
            }
        }

        if ($this->config->get('development')) {
            Debug::setCacheCores($cacheManager->getRegistered());
        }
        /**
         * @var CacheInterface $cache
         */
        $cache = $cacheManager->get('data');

        if(empty($cache)){
            return null;
        }

        return $cache;
    }

    /**
     * Initialize Database connection
     * @return Db\ManagerInterface
     * @throws \Exception
     */
    protected function initDb()
    {
        $dev = $this->config->get('development');
        $dbErrorHandler = function ( Db\Adapter\Event $e) use( $dev){
            $response = Response::factory();
            $response->setResponseCode(500);
            $response->error(Lang::lang()->get('CANT_CONNECT'));
            exit();
        };

        $useProfiler = false;
        if($dev && $this->config->get('debug_panel')){
            $useProfiler = Config::storage()->get('debug_panel.php')->get('options')['sql'];
        }

        $this->config->set('use_db_profiler', $useProfiler);

        $conManager = new Db\Manager($this->config);
        $conManager->setConnectionErrorHandler($dbErrorHandler);
        return $conManager;
    }

    /**
     * Start application
     */
    abstract public function run();

    /**
     * Start application in test mode
     */
    public function runTestMode()
    {
        $this->init();
    }

    /**
     * Start application in install mode
     */
    public function runInstallMode(){
        $this->init();
    }

    /**
     * Start console application
     */
    public function runConsole()
    {
        $this->init();
        $request = Request::factory();
        $response = Response::factory();
        $config = Config::storage()->get('console.php');
        $routerClass = $config->get('router');
        $router = new $routerClass();
        $router->route($request, $response);
        if (!$response->isSent()) {
            $response->send();
        }
    }

    /**
     * Run frontend application
     */
    protected function routeFrontend()
    {
        $request = Request::factory();
        $response = Response::factory();

        /*
         * Start routing
        */
        $frontConfig = Config::storage()->get('frontend.php');
        $routerClass = '\\Dvelum\\App\\Router\\' . $frontConfig->get('router');

        if (!class_exists($routerClass)) {
            $routerClass = $frontConfig->get('router');
        }

        /**
         * @var \Dvelum\App\Router $router
         */
        $router = new $routerClass();
        $router->route($request, $response);

        if (!$response->isSent()) {
            $response->send();
        }
    }
}