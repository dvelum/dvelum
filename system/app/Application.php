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
/**
 * Application - is the main class that initializes system configuration
 * settings. The system starts working with running an object of this class.
 * @author Kirill A Egorov
 */
class Application
{

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
     * @var Externals_Expert
     */
    protected $_externalsExpert = false;

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
        Request::setDelimiter($this->_config->get('urlDelimiter'));
        Request::setExtension($this->_config->get('urlExtension'));
        Request::setRoot($this->_config->get('wwwroot'));
        Resource::setCachePaths($this->_config->get('jsCacheSysUrl') , $this->_config->get('jsCacheSysPath'));
        Resource::setDocRoot($this->_config->get('docroot'));
        Resource::setResourceRoot($this->_config->get('wwwroot'));
        Utils::setSalt($this->_config->get('salt'));

       /*
        * Init lang dictionary (Lazy Load)
        */
        $lang = $this->_config->get('language');
        Lang::addDictionaryLoader($lang ,  $this->_config->get('docroot') . '/system/lang/' . $lang . '.php' , Config::File_Array);
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
        $objectStore = new Db_Object_Store();
        $objectStore->setEventManager($eventManager);
        $objectStore->setLinksObjectName($this->_config->get('orm_links_object'));
        $objectStore->setHistoryObject($this->_config->get('orm_history_object'));
        $objectStore->setVersionObject($this->_config->get('orm_version_object'));

        /*
         * Prepare models
         */
        Model::setDataCache($this->_cache);
        Model::setGlobalHardcacheTime($this->_config->get('frontend_hardcache'));
        Model::setGlobalObjectStore($objectStore);
        Model::setDefaultDbManager($conManager);

        /*
         * Prepare Db_Object
         */
        $translator = new Db_Object_Config_Translator( $this->_config->get('lang_path') . $this->_config->get('language') . '/objects.php');
        $translator->addTranslations(array($this->_config->get('lang_path') . $this->_config->get('language') . '/system_objects.php'));
        Db_Object_Config::setConfigPath($this->_config->get('object_configs'));
        Db_Object_Config::setTranslator($translator);

        if($this->_config->get('db_object_error_log')){
            $log = new Log_File($this->_config->get('db_object_error_log_path'));
            Db_Object::setLog($log);
            Model::setDefaultLog($log);
        }
        /*
         * Prepare dictionaries
         */
        Dictionary::setConfigPath($this->_config->get('dictionary'));

        /*
         * Prepare Controllers
         */
        Controller::setDefaultDb($this->_db);

        /*
         * Prepare Externals
         */
        if($this->_config->get('allow_externals'))
            $this->_initExternals();

        
       /*
        * Switch to Db_Object error log
        */
        if($this->_config->get('db_object_error_log'))
        {
            $errorModel = Model::factory($this->_config->get('erorr_log_object'));
            $errorTable = $errorModel->table(true);
            $errorDb = $errorModel->getDbConnection();
        
            $logOrmDb = new Log_Db('db_object_error_log' , $errorDb , $errorTable);
        
            Db_Object::setLog(new Log_Mixed($log, $logOrmDb));
        
            $logModelDb = new Log_Db('model' , $errorDb , $errorTable);
        
            Model::setDefaultLog(new Log_Mixed($log, $logModelDb));
        }
        
        $this->_init = true;
    }

    /**
     * Initialize Cache connections
     */
    protected function _initCache()
    {
        if(!$this->_config->get('use_cache'))
            return;

        $cacheConfig = include $this->_config->get('configs') . 'cache.php';
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
     * @param array | Config_Abstract $dbConfig
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
                echo $tpl->render($templatesPath . 'public/error.php');
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
        $cfg = Registry::get('main' , 'config');

        if($page === $cfg['adminPath'])
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

        /*
         * Inject Externals exper ino Objects Manager
         */
        if($this->_config->get('allow_externals'))
            Db_Object_Manager::setExternalsExpert($this->_getExternalsExpert());

        $cfgBackend = Config::factory(Config::File_Array , $this->_config->get('configs') . 'backend.php');

        Registry::set('backend' , $cfgBackend , 'config');

        self::$_templates = $this->_config->get('templates') . 'system/' . $cfgBackend->get('theme') . '/';
        $page = Page::getInstance();
        $page->setTemplatesPath(self::$_templates);

        $user = User::getInstance();
        /*
         * Update "Users Online" statistics
         */
        if($this->_config->get('usersOnline') && $user->isAuthorized())
            Model::factory('Online')->addOnline(session_id() , $user->id);

        /*
         * Start routing
         */
        $router = new Backend_Router();
        $router->route();

        $controller = Request::getInstance()->getPart(1);

        /*
         * Define frontent JS variables
         */
        $res = Resource::getInstance();
        $res->addInlineJs('
            app.wwwRoot = "'.$this->_config->get('wwwroot').'";
        	app.admin = "' . $this->_config->get('wwwroot') . $this->_config->get('adminPath') . '";
        	app.delimiter = "' . $this->_config->get('urlDelimiter') . '";
        	app.root = "' . $this->_config->get('wwwroot') .  $this->_config->get('adminPath') . $this->_config->get('urlDelimiter') . $controller . $this->_config->get('urlDelimiter') . '";
        ');

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
                        'version' => Config::factory(Config::File_Array , $this->_config->get('configs') . 'versions.php')->get('core'),
                        'lang' => $this->_config->get('language'),
                        'modules' => Config::factory(Config::File_Array , $this->_config->get('backend_modules')),
                        'userModules' => $user->getAvailableModules(),
                        'useCSRFToken' => $cfgBackend->get('use_csrf_token')
        ));
        Response::put($template->render(self::$_templates . 'layout.php'));
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
            echo $tpl->render( $this->_config->get('templates') . 'public/error.php');
            self::close();
        }

        /*
         * Update "Users Online" statistics
         */
        if($this->_config->get('usersOnline')){
            $user = User::getInstance();
            if($user->isAuthorized())
                Model::factory('Online')->addOnline(session_id() , $user->id);
        }

        self::$_templates = $this->_config->get('templates') . 'public/';
        $page = Page::getInstance();
        $page->setTemplatesPath(self::$_templates);
        /*
         * Start routing
         */
        $router = new Frontend_Router();
        $router->route();
    }

    protected function _getExternalsExpert()
    {
        if($this->_externalsExpert)
            return $this->_externalsExpert;

        $config = Config::factory(Config::File_Array , $this->_config->get('configs') . 'externals.php');

        if($this->_cache)
            Externals_Expert::setDefaultCache($this->_cache);

        $this->_externalsExpert = new Externals_Expert($this->_config , $config);

        return $this->_externalsExpert;
    }

    protected function _initExternals()
    {
        $eExpert = $this->_getExternalsExpert();
        if(!$eExpert->hasExternals())
            return;
       /*
        * Register external classes
        */
        $classes = $eExpert->getClasses();
        if(!empty($classes))
            $this->_autoloader->addMap($classes);
        /*
         * Register external objects
         */
        $objects = $eExpert->getObjects();
        if(!empty($objects))
            Db_Object_Config::registerConfigs($objects);

        $curLang = $this->_config->get('language');
        /*
         * Register external translations
         */
        $translations = $eExpert->getTranslations($curLang);

        if(!empty($translations))
            Db_Object_Config::getTranslator()->addTranslations($translations);

        $dictionaries = $eExpert->getDictionaries();
        if(!empty($dictionaries))
            Dictionary::addExternal($dictionaries);

        $langs = $eExpert->getLangs($curLang);

        if(!empty($langs))
            foreach($langs as $name => $path)
                Lang::addDictionaryLoader($name , $path , Config::File_Array);
        /*
         * Inject Externals Expert
         */
        $page = Page::getInstance()->setExternalsExpert($eExpert);
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

    /**
     * Get application object configuration
     * @param string $config - optional
     * @return Config_Abstract
     * @deprecated
     */
    static public function getConfig($config = 'main')
    {
        switch($config){
            case 'main':
                return Registry::get('main' , 'config');
              break;
            case 'backend':
                return Config::factory(Config::File_Array , Registry::get('main' , 'config')->get('configs') . 'backend.php');
              break;
        }
    }

    /**
     * Get default database connector
     * @return Zend_Db_Adapter_Abstract
     * @deprecated
     */
    static public function getDbConnection()
    {
        return Registry::get('db');
    }

    /**
     * Get link to local data storage (store runtime data)
     * @return Store_Local
     * @deprecated
     */
    static public function getStorage()
    {
        return Store::factory(Store::Local);
    }

    /**
     * Get data cache frontend
     * @return Cache_Interface or false
     * @deprecated
     */
    static public function getDataCache()
    {
        $cacheManager = new Cache_Manager();
        return $cacheManager->get('data');
    }

    /**
     * Get system cache frontend
     * @return Cache_Interface
     * @deprecated
     */
    static public function getSystemCache()
    {
        $cacheManager = new Cache_Manager();
        return $cacheManager->get('system');
    }
}