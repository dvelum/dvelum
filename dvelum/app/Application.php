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

    protected static $_templates = '';
    /**
     * Application config
     * @var Config_Abstract
     */
    protected $_config;

    /**
     * @var Cache_Abstract
     */
    protected $_cache = false;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db = false;

    /**
     * @var boolean
     */
    protected $_init = false;

    /**
     * @var Autoloader
     */
    protected $_autoloader = false;

    /**
     * The constructor accepts the main configuration object as an argument
     * @param Config_Abstract $config
     */
    public function __construct(Config_Abstract $config)
    {
        $this->_config = $config;
    }

    /**
     * Set adapter for caching application data
     * @param Cache_Abstract $cache
     */
    public function setCache(Cache_Abstract $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Inject Autoloader
     * @param Autoloader $al
     */
    public function setAutoloader(Autoloader $al)
    {
        $this->_autoloader = $al;
    }

    /**
     * Initialize the application, configure the settings, inject dependencies
     * Adjust the settings necessary for running the system
     */
    public function init()
    {
        if($this->_init)
            return;

        date_default_timezone_set($this->_config->get('timezone'));

        /*
         * Init cache connection
         */
        $this->_initCache();
        /*
         * Init database connection
         */
        $conManager = $this->_initDb();

        /*
         * Apply configs
         */
        Filter::setDelimiter($this->_config->get('urlDelimiter'));

        /*
         * Init localization storage options
         */
        $langStorage = Lang::storage();
        $langStorage->setConfig(
            Config::storage()->get('lang_storage.php')->__toArray()
        );

        /*
         * Init templates storage
         */
        $templateStorage = Template::storage();
        $templateStorage->setConfig(
            Config::storage()->get('template_storage.php')->__toArray()
        );

        Request::setConfig(array(
            'delimiter' => $this->_config->get('urlDelimiter'),
            'extension' => $this->_config->get('urlExtension'),
            'wwwRoot' => $this->_config->get('wwwroot')
        ));

        Resource::setCachePaths($this->_config->get('jsCacheSysUrl') , $this->_config->get('jsCacheSysPath'));
        Resource::setResourceRoot($this->_config->get('wwwroot'));
        Resource::setResourcePath($this->_config->get('wwwPath'));
        Utils::setSalt($this->_config->get('salt'));
        Trigger::setApplicationConfig($this->_config);

       /*
        * Init lang dictionary (Lazy Load)
        */
        $lang = $this->_config->get('language');
        Lang::addDictionaryLoader($lang ,  $lang . '.php' , Config::File_Array);
        Lang::setDefaultDictionary($this->_config->get('language'));

        $eventManager = new Eventmanager();

        if($this->_cache)
        {
            $eventManager->setCache($this->_cache);
            Resource::setCache($this->_cache);
            Template::setCache($this->_cache);
            if($this->_config->offsetExists('template_check_mtime'))
              Template::checkMtime($this->_config->get('template_check_mtime'));
        }
       /*
        * Prepare Db object storage
        */
        $objectStore = new Db_Object_Store(array(
            'linksObject' => $this->_config->get('orm_links_object'),
            'historyObject' => $this->_config->get('orm_history_object'),
            'versionObject' => $this->_config->get('orm_version_object'),
        ));
        $objectStore->setEventManager($eventManager);

        /*
         * Prepare models
         */
        Model::setDefaults(array(
            'hardCacheTime'  => $this->_config->get('frontend_hardcache'),
            'dataCache' => $this->_cache  ,
            'dbObjectStore'  => $objectStore,
            'defaultDbManager' => $conManager,
            'errorLog' => false
        ));
        /*
         * Prepare Db_Object
         */
        $translator = new Db_Object_Config_Translator($this->_config->get('language') . '/objects.php');
        Db_Object_Config::setConfigPath($this->_config->get('object_configs'));
        Db_Object_Config::setTranslator($translator);

        if($this->_config->get('db_object_error_log'))
        {
            $log = new Log_File($this->_config->get('db_object_error_log_path'));
            /*
             * Switch to Db_Object error log
             */
            if(!empty($this->_config->get('error_log_object')))
            {
                $errorModel = Model::factory($this->_config->get('error_log_object'));
                $errorTable = $errorModel->table();
                $errorDb = $errorModel->getDbConnection();

                $logOrmDb = new Log_Db('db_object_error_log' , $errorDb , $errorTable);
                $logModelDb = new Log_Db('model' , $errorDb , $errorTable);
                Db_Object::setLog(new Log_Mixed($log, $logOrmDb));
                Model::setDefaultLog(new Log_Mixed($log, $logModelDb));
                $objectStore->setLog($logOrmDb);
            }else{
                Db_Object::setLog($log);
                Model::setDefaultLog($log);
                $objectStore->setLog($log);
            }
        }
        /*
         * Prepare dictionaries
         */
        Dictionary::setConfigPath($this->_config->get('dictionary_folder') . $this->_config->get('language').'/');

        // init external modules
        $externalsCfg = $this->_config->get('externals');
        if($externalsCfg['enabled']){
            $this->_initExternals();
        }

        $this->_init = true;
    }

    /**
     * Init additional external modules
     * defined in external_modules option
     * of main configuration file
     */
    protected function _initExternals()
    {
        $externals = Config::storage()->get('external_modules.php');

        Externals_Manager::setConfig([
            'appConfig'=>$this->_config,
            'autoloader' =>$this->_autoloader
        ]);

        if($externals->getCount()){
            Externals_Manager::factory()->loadModules();
        }
    }

    /**
     * Initialize Cache connections
     */
    protected function _initCache()
    {
        if(!$this->_config->get('use_cache'))
            return;

        $cacheConfig = Config::storage()->get('cache.php')->__toArray();
        $cacheManager = new Cache_Manager();

        foreach($cacheConfig as $name => $cfg)
            if($cfg['enabled'])
                $cacheManager->connect($name , $cfg);

        if($this->_config->get('development'))
            Debug::setCacheCores($cacheManager->getRegistered());

        $this->_cache = $cacheManager->get('data');
    }

    /**
     * Initialize Database connection
     * @return Db_Manager_Interface
     */
    protected function _initDb()
    {
        $templatesPath = $this->_config->get('templates');
        $dev = $this->_config->get('development');
        $dbErrorHandler = function (Exception $e) use($templatesPath , $dev){
            if(Request::isAjax()){
                Response::jsonError(Lang::lang()->CANT_CONNECT);
            }else{
                $tpl = new Template();
                $tpl->set('error_msg', 'MySQL : '.$e->getMessage());
                $tpl->set('development', $dev);
                echo $tpl->render('public/error.php');
                exit();
            }
        };

        $conManager = new Db_Manager($this->_config);
        try{
            $dbConfig = $conManager->getDbConfig('default');
            $this->_db = $conManager->getDbConnection('default');
            if($dbConfig->get('adapterNamespace') == 'Db_Adapter')
                $this->_db->setConnectionErrorHandler($dbErrorHandler);
        }
        catch (Exception $e){
            $dbErrorHandler($e);
        }
        /*
         * Store connection config in Registry
         */
        Registry::set('db' , $dbConfig , 'config');
        Registry::set('db' , $this->_db);

        return $conManager;
    }

    /**
     * Start application
     */
    public function run()
    {
        $this->init();
        $page = Request::getInstance()->getPart(0);

        if($page === $this->_config->get('adminPath'))
            $this->_runBackend();
        else
            $this->_runFrontend();
    }

    /**
     * Run backend application
     */
    protected function _runBackend()
    {
        if($this->_cache)
            Blockmanager::setDefaultCache($this->_cache);

        /*
         * Prepare objects
         */
        Db_Object_Builder::useForeignKeys($this->_config->get('foreign_keys'));

        $cfgBackend = Config::storage()->get('backend.php');

        Registry::set('backend' , $cfgBackend , 'config');

        self::$_templates = 'system/' . $cfgBackend->get('theme') . '/';
        $page = Page::getInstance();
        $page->setTemplatesPath(self::$_templates);

        /*
         * Start routing
         */
        $router = new Backend_Router();
        $router->route();
        $controller = Request::getInstance()->getPart(1);

        /*
         * Define frontend JS variables
         */
        $res = Resource::getInstance();
        $res->addInlineJs('
            app.wwwRoot = "'.$this->_config->get('wwwroot').'";
        	app.admin = "' . $this->_config->get('wwwroot') . $this->_config->get('adminPath') . '";
        	app.delimiter = "' . $this->_config->get('urlDelimiter') . '";
        	app.root = "' . $this->_config->get('wwwroot') .  $this->_config->get('adminPath') . $this->_config->get('urlDelimiter') . $controller . $this->_config->get('urlDelimiter') . '";
        ');

        $modulesManager = new Modules_Manager();
        /*
         * Load template
         */
        $template = new Template();
        $template->disableCache();
        $template->setProperties(array(
            'wwwRoot'=>$this->_config->get('wwwroot'),
            'page' => $page,
            'urlPath' => $controller,
            'resource' => $res,
            'path' => self::$_templates,
            'adminPath' => $this->_config->get('adminPath'),
            'development' => $this->_config->get('development'),
            'version' => Config::storage()->get('versions.php')->get('core'),
            'lang' => $this->_config->get('language'),
            'modules' => $modulesManager->getList(),
            'userModules' => User::getInstance()->getAvailableModules(),
            'useCSRFToken' => $cfgBackend->get('use_csrf_token'),
            'theme' => $cfgBackend->get('theme')
        ));

        Response::put($template->render($page->getTemplatesPath() . 'layout.php'));
    }

    /**
     * Run frontend application
     */
    protected function _runFrontend()
    {
        Blockmanager::useHardCacheTime($this->_config->get('blockmanager_use_hardcache_time'));
        if($this->_config->get('maintenance')){
            $tpl = new Template();
            $tpl->set('msg' , Lang::lang()->get('MAINTENANCE'));
            echo $tpl->render('public/error.php');
            self::close();
        }

        self::$_templates =  'public/';
        $page = Page::getInstance();
        $page->setTemplatesPath(self::$_templates);
        /*
         * Start routing
         */
        $routerClass =  $this->_config->get('frontend_router');
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