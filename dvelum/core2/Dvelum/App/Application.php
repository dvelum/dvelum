<?php
declare(strict_types=1);

namespace Dvelum\App;

use Dvelum\Template;
use Dvelum\Autoloader;
use Dvelum\Request;
use Dvelum\Resource;
use Dvelum\Config;
use Dvelum\Model;
use Dvelum\Orm;
use Dvelum\Lang;
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
     * @deprecated
     * @var string
     */
    protected static $_templates = '';
    /**
     * Application config
     * @var Config\Config
     */
    protected $config;

    /**
     * @var \Cache_Interface
     */
    protected $cache = false;
    /**
     * @var boolean
     */
    protected $initialized = false;
    
    /**
     * The constructor accepts the main configuration object as an argument
     * @param Config\Config $config
     */
    public function __construct(Config\Config $config)
    {
        $this->config = $config;
    }
    
    /**
     * Inject Auto-loader
     * @param Autoloader $al
     */
    public function setAutoloader(Autoloader $al)
    {
        $this->autoloader = $al;
    }

    /**
     * Initialize the application, configure the settings, inject dependencies
     * Adjust the settings necessary for running the system
     */
    public function init()
    {
        if($this->initialized)
            return;

        date_default_timezone_set($this->config->get('timezone'));

        /*
         * Init cache connection
         */
        $this->initCache();

        /*
         * Init database connection
         */
        $conManager = $this->initDb();

        /*
         * Apply configs
         */
        \Filter::setDelimiter($this->config->get('urlDelimiter'));

        /*
         * Init localization storage options
         */
        $langStorage = Lang::storage();
        $langStorage->setConfig(
            Config\Factory::storage()->get('lang_storage.php')->__toArray()
        );

        /*
         * Init templates storage
         */
        $templateStorage = Template::storage();
        $templateStorage->setConfig(
            Config\Factory::storage()->get('template_storage.php')->__toArray()
        );

        $request = Request::factory();
        $request->setConfig(Config\Factory::create([
            'delimiter' => $this->config->get('urlDelimiter'),
            'extension' => $this->config->get('urlExtension'),
            'wwwRoot' => $this->config->get('wwwroot')
        ]));

        $resource = Resource::factory();
        $resource->setConfig(Config\Factory::create([
            'jsCacheSysUrl' => $this->config->get('jsCacheSysUrl'),
            'jsCacheSysPath' => $this->config->get('jsCacheSysPath'),
            'wwwRoot' => $this->config->get('wwwroot'),
            'wwwPath' => $this->config->get('wwwpath'),
            'cache'=>$this->cache
        ]));


        \Utils::setSalt($this->config->get('salt'));
        \Trigger::setApplicationConfig($this->config);

        /*
         * Init lang dictionary (Lazy Load)
         */
        $lang = $this->config->get('language');
        Lang::addDictionaryLoader($lang ,  $lang . '.php' , Config\Factory::File_Array);
        Lang::setDefaultDictionary($this->config->get('language'));

        $eventManager = new \Eventmanager();

        if($this->cache)
        {
            $eventManager->setCache($this->cache);
            Resource::setCache($this->cache);
            Template::setCache($this->cache);
            if($this->config->offsetExists('template_check_mtime'))
                Template::checkMtime($this->config->get('template_check_mtime'));
        }
        /*
         * Prepare Db object storage
         */
        $objectStore = new Orm\Object\Store(array(
            'linksObject' => $this->config->get('orm_links_object'),
            'historyObject' => $this->config->get('orm_history_object'),
            'versionObject' => $this->config->get('orm_version_object'),
        ));
        $objectStore->setEventManager($eventManager);

        /*
         * Prepare models
         */
        \Dvelum\Model::setDefaults(array(
            'hardCacheTime'  => $this->config->get('frontend_hardcache'),
            'dataCache' => $this->cache  ,
            'dbObjectStore'  => $objectStore,
            'defaultDbManager' => $conManager,
            'errorLog' => false
        ));
        /*
         * Prepare Db_Object
         */
        $translator = new Orm\Object\Config\Translator($this->config->get('language') . '/objects.php');
        Orm\Object\Config::setConfigPath($this->config->get('object_configs'));
        Orm\Object\Config::setTranslator($translator);

        if($this->config->get('db_object_error_log'))
        {
            $log = new \Log_File($this->config->get('db_object_error_log_path'));
            /*
             * Switch to Db_Object error log
             */
            if(!empty($this->config->get('error_log_object')))
            {
                $errorModel = Model::factory($this->config->get('error_log_object'));
                $errorTable = $errorModel->table();
                $errorDb = $errorModel->getDbConnection();

                $logOrmDb = new \Log_Db('db_object_error_log' , $errorDb , $errorTable);
                $logModelDb = new \Log_Db('model' , $errorDb , $errorTable);
                Orm\Object::setLog(new \Log_Mixed($log, $logOrmDb));
                Model::setDefaultLog(new \Log_Mixed($log, $logModelDb));
                $objectStore->setLog($logOrmDb);
            }else{
                Orm\Object::setLog($log);
                Model::setDefaultLog($log);
                $objectStore->setLog($log);
            }
        }
        /*
         * Prepare dictionaries
         */
        \Dictionary::setConfigPath($this->config->get('dictionary_folder') . $this->config->get('language').'/');

        // init external modules
        $externalsCfg = $this->config->get('externals');
        if($externalsCfg['enabled']){
            $this->initExternals();
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
            'appConfig'=>$this->config,
            'autoloader' =>$this->autoloader
        ]);

        if($externals->getCount()){
            \Externals_Manager::factory()->loadModules();
        }
    }

    /**
     * Initialize Cache connections
     */
    protected function initCache()
    {
        if(!$this->config->get('use_cache'))
            return;

        $cacheConfig = Config::storage()->get('cache.php')->__toArray();
        $cacheManager = new \Cache_Manager();

        foreach($cacheConfig as $name => $cfg)
            if($cfg['enabled'])
                $cacheManager->connect($name , $cfg);

        if($this->config->get('development'))
            \Debug::setCacheCores($cacheManager->getRegistered());

        $this->cache = $cacheManager->get('data');
    }

    /**
     * Initialize Database connection
     * @return \Db_Manager_Interface
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
        $conManager = new \Db_Manager($this->config);
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

        if($page === $this->config->get('adminPath'))
            $this->routeBackoffice();
        else
            $this->routeFrontend();
    }

    /**
     * Run backend application
     */
    protected function routeBackoffice()
    {
        if($this->cache)
            Blockmanager::setDefaultCache($this->cache);

        /*
         * Prepare objects
         */
        Orm\Object\Builder::useForeignKeys($this->config->get('foreign_keys'));

        $cfgBackend = Config\Factory::storage()->get('backend.php');


        self::$_templates = 'system/' . $cfgBackend->get('theme') . '/';

        $page = \Page::getInstance();
        $page->setTemplatesPath(self::$_templates);

        /*
         * Start routing
         */
        $router = new \Backend_Router();
        $router->route();
        $controller = Request::factory()->getPart(1);

        /*
         * Define frontend JS variables
         */
        $res = Resource::factory();
        $res->addInlineJs('
            app.wwwRoot = "'.$this->config->get('wwwroot').'";
        	app.admin = "' . $this->config->get('wwwroot') . $this->config->get('adminPath') . '";
        	app.delimiter = "' . $this->config->get('urlDelimiter') . '";
        	app.root = "' . $this->config->get('wwwroot') .  $this->config->get('adminPath') . $this->config->get('urlDelimiter') . $controller . $this->config->get('urlDelimiter') . '";
        ');

        $modulesManager = new \Modules_Manager();
        /*
         * Load template
         */
        $template = new Template();
        $template->disableCache();
        $template->setProperties(array(
            'wwwRoot'=>$this->config->get('wwwroot'),
            'page' => $page,
            'urlPath' => $controller,
            'resource' => $res,
            'path' => self::$_templates,
            'adminPath' => $this->config->get('adminPath'),
            'development' => $this->config->get('development'),
            'version' => Config::storage()->get('versions.php')->get('core'),
            'lang' => $this->config->get('language'),
            'modules' => $modulesManager->getList(),
            'userModules' => Session\User::factory()->getAvailableModules(),
            'useCSRFToken' => $cfgBackend->get('use_csrf_token'),
            'theme' => $cfgBackend->get('theme')
        ));

        \Response::put($template->render($page->getTemplatesPath() . 'layout.php'));
    }

    /**
     * Run frontend application
     */
    protected function routeFrontend()
    {
        \Blockmanager::useHardCacheTime($this->config->get('blockmanager_use_hardcache_time'));
        if($this->config->get('maintenance')){
            $tpl = new Template();
            $tpl->set('msg' , Lang::lang()->get('MAINTENANCE'));
            echo $tpl->render('public/error.php');
            self::close();
        }

        self::$_templates =  'public/';
        $page = \Page::getInstance();
        $page->setTemplatesPath(self::$_templates);
        /*
         * Start routing
         */
        $routerClass =  $this->config->get('frontend_router');
        $router = new $routerClass();
        $router->route();
    }
    /**
     * Close application, stop processing
     */
    static public function close()
    {
        exit();
    }

    /**
     * Get path to templates
     * @return string
     */
    static public function getTemplatesPath()
    {
        return self::$_templates;
    }
}