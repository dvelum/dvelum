<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2017  Kirill Yegorov
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

namespace Dvelum\App;

use Dvelum\Response;
use Dvelum\Resource;
use Dvelum\View;
use Dvelum\Autoload;
use Dvelum\Request;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Db;
use Dvelum\Orm;
use Dvelum\Lang;
use Dvelum\Utils;
use Dvelum\App\Router\Backend as RouterBackend;
use Dvelum\Service;

/**
 * Application - is the main class that initializes system configuration
 * settings. The system starts working with running an object of this class.
 * @author Kirill A Egorov
 */
class Application
{
    const MODE_PRODUCTION = 0;
    const MODE_DEVELOPMENT = 1;
    const MODE_TEST = 2;
    const MODE_INSTALL = 3;

    /**
     * Application config
     * @var Config\Adapter
     */
    protected $config;

    /**
     * @var \Cache_Interface
     */
    protected $cache;

    /**
     * @var boolean
     */
    protected $initialized = false;

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
    public function init()
    {
        if ($this->initialized) {
            return;
        }

        date_default_timezone_set($this->config->get('timezone'));

        /*
         * Init cache connection
         */
        $cache = $this->initCache();
        $this->cache = $cache;

        /*
         * Init database connection
         */
        $dbManager = $this->initDb();

        /*
         * Init templates storage
         */
        $templateStorage = View::storage();
        $templateStorage->setConfig(Config\Factory::storage()->get('template_storage.php')->__toArray());

        $request = Request::factory();
        $request->setConfig(Config\Factory::create([
            'delimiter' => $this->config->get('urlDelimiter'),
            'extension' => $this->config->get('urlExtension'),
            'wwwRoot' => $this->config->get('wwwRoot')
        ]));

        $resource = Resource::factory();
        $resource->setConfig(Config\Factory::create([
            'jsCacheSysUrl' => $this->config->get('jsCacheSysUrl'),
            'jsCacheSysPath' => $this->config->get('jsCacheSysPath'),
            'wwwRoot' => $this->config->get('wwwRoot'),
            'wwwPath' => $this->config->get('wwwPath'),
            'cache' => $cache
        ]));

        Utils::setSalt($this->config->get('salt'));

        /*
         * Init lang dictionary (Lazy Load)
         */
        $lang = $this->config->get('language');

        if ($cache) {
            View::setCache($cache);
            if ($this->config->offsetExists('template_check_mtime')) {
                View::checkMtime($this->config->get('template_check_mtime'));
            }
        }

        /*
         * Register Localization service
         */
        Service::register('lang', function () use ($lang) {
            $langService = new Lang();
            $langService->addLoader($lang, $lang . '.php', Config\Factory::File_Array);
            $langService->setDefaultDictionary($lang);
            $langStorage = $langService->getStorage();
            $langStorage->setConfig(Config\Factory::storage()->get('lang_storage.php')->__toArray());
            return $langService;
        });

        /*
         *  Register ORM service
         */
        Service::register('orm', function () use ($dbManager, $lang, $cache) {
            $orm = new Orm();
            $orm->init(Config::storage()->get('orm.php'), $dbManager, $lang, $cache);
            return $orm;
        });

        /*
         * Register Dictionary service
         */
        Service::register('dictionary', function () {
            $service = new Dictionary\Service();
            $service->setConfig(Config\Factory::create([
                'configPath' => $this->config->get('dictionary_folder') . $this->config->get('language') . '/'
            ]));
            return $service;
        });

        /**
         * Register BlockManager Service
         */
        Service::register('blockmanager', function() use ($cache){
             $blockManager = new BlockManager();

             if($cache){
                 $blockManager->setCache($cache);
             }

             if($this->config->get('blockmanager_hard_cache')){
                 $blockManager->useHardCacheTime(true);
             }

             return $blockManager;
        });

        // init external modules
        $externalsCfg = $this->config->get('externals');
        if ($externalsCfg['enabled']) {
            $this->initExternals();
        }

        $request = Request::factory();
        $response = Response::factory();
        if ($request->isAjax()) {
            $response->setFormat(Response::FORMAT_JSON);
        } else {
            $response->setFormat(Response::FORMAT_HTML);
        }

        $this->initialized = true;
    }

    /**
     * Init additional external modules
     * defined in external_modules option
     * of main configuration file
     */
    protected function initExternals()
    {
        $externals = Config\Factory::storage()->get('external_modules.php');

        \Externals_Manager::setConfig([
            'appConfig' => $this->config,
            'autoloader' => $this->autoloader
        ]);

        if ($externals->getCount()) {
            \Externals_Manager::factory()->loadModules();
        }
    }

    /**
     * Initialize Cache connections
     * @return \Cache_Interface | null
     */
    protected function initCache(): ?\Cache_Interface
    {
        if (!$this->config->get('use_cache')) {
            return null;
        }

        $cacheConfig = Config::storage()->get('cache.php')->__toArray();
        $cacheManager = new \Cache_Manager();

        foreach ($cacheConfig as $name => $cfg) {
            if ($cfg['enabled']) {
                $cacheManager->connect($name, $cfg);
            }
        }

        if ($this->config->get('development')) {
            \Debug::setCacheCores($cacheManager->getRegistered());
        }

        return $cacheManager->get('data');
    }

    /**
     * Initialize Database connection
     * @return Db\ManagerInterface
     */
    protected function initDb()
    {
        //        $templatesPath = $this->config->get('templates');
        //        $dev = $this->config->get('development');
        //        $dbErrorHandler = function (Exception $e) use($templatesPath , $dev){
        //            if(Request::isAjax()){
        //                Response::jsonError(Lang::lang()->CANT_CONNECT);
        //            }else{
        //                $tpl = new Template();
        //                $tpl->set('error_msg', 'MySQL : '.$e->getMessage());
        //                $tpl->set('development', $dev);
        //                echo $tpl->render('public/error.php');
        //                exit();
        //            }
        //        };

        /**
         * @todo handle connection error
         */
        $conManager = new Db\Manager($this->config);
        //        try{
        //            $dbConfig = $conManager->getDbConfig('default');
        //            $this->_db = $conManager->getDbConnection('default');
        //                        if($dbConfig->get('adapterNamespace') == 'Db_Adapter')
        //                            $this->_db->setConnectionErrorHandler($dbErrorHandler);
        //        }
        //        catch (Exception $e){
        //            $dbErrorHandler($e);
        //        }
        return $conManager;
    }

    /**
     * Start application
     */
    public function run()
    {
        $this->init();
        $page = Request::factory()->getPart(0);

        if ($page === $this->config->get('adminPath')) {
            $this->routeBackOffice();
        } else {
            $this->routeFrontend();
        }
    }

    /**
     * Run backend application
     */
    protected function routeBackOffice()
    {
        $cfgBackend = Config\Factory::storage()->get('backend.php');
        $templatesPath = 'system/' . $cfgBackend->get('theme') . '/';

        $page = \Page::getInstance();
        $page->setTemplatesPath($templatesPath);

        $request = Request::factory();
        $response = Response::factory();
        /*
         * Start routing
         */
        $router = new RouterBackend();
        $router->route($request, $response);

        if ($response->isSent()) {
            return;
        }

        $controller = $request->getPart(1);

        /*
         * Define frontend JS variables
         */
        $res = Resource::factory();
        $res->addInlineJs('
            app.wwwRoot = "' . $this->config->get('wwwroot') . '";
        	app.admin = "' . $this->config->get('wwwroot') . $this->config->get('adminPath') . '";
        	app.delimiter = "' . $this->config->get('urlDelimiter') . '";
        	app.root = "' . $this->config->get('wwwroot') . $this->config->get('adminPath') . $this->config->get('urlDelimiter') . $controller . $this->config->get('urlDelimiter') . '";
        ');

        $modulesManager = new \Modules_Manager();
        /*
         * Load template
         */
        $template = new View();
        $template->disableCache();
        $template->setProperties(array(
            'wwwRoot' => $this->config->get('wwwroot'),
            'page' => $page,
            'urlPath' => $controller,
            'resource' => $res,
            'path' => $templatesPath,
            'adminPath' => $this->config->get('adminPath'),
            'development' => $this->config->get('development'),
            'version' => Config::storage()->get('versions.php')->get('core'),
            'lang' => $this->config->get('language'),
            'modules' => $modulesManager->getList(),
            'userModules' => Session\User::factory()->getModuleAcl()->getAvailableModules(),
            'useCSRFToken' => $cfgBackend->get('use_csrf_token'),
            'theme' => $cfgBackend->get('theme')
        ));

        $response->put($template->render($page->getTemplatesPath() . 'layout.php'));

        $response->send();
    }

    /**
     * Run frontend application
     */
    protected function routeFrontend()
    {
        $request = Request::factory();
        $response = Response::factory();

        if ($this->config->get('maintenance')) {
            $tpl = new View();
            $tpl->set('msg', Lang::lang()->get('MAINTENANCE'));
            $response->put($tpl->render('public/error.php'));
            $response->send();
            return;
        }

        $page = \Page::getInstance();
        $page->setTemplatesPath('public/');

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

        $response->send();
    }

    /**
     * Close application, stop processing
     * @deprecated
     */
    static public function close()
    {
        exit();
    }
}