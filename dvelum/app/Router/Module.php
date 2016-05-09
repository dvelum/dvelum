<?php
class Router_Module extends Router
{
    const CACHE_KEY_ROUTES = 'Frontend_Router_routes';
    protected $_appConfig = false;
    protected $_moduleRoutes;

    public function __construct()
    {
        parent::__construct();
        $this->_appConfig = Registry::get('main' , 'config');
    }

    /**
     * Route request
     * @throws Exception
     */
    public function route()
    {
        $vers = Request::get('vers' , 'int' , false);
        $showRevision = false;
        $pageCode = $this->_request->getPart(0);

        if(!strlen($pageCode))
            $pageCode = 'index';

        $pageData = Model::factory('Page')->getCachedItemByField('code' , $pageCode);

        if(empty($pageData))
            Response::redirect('/');

        $cacheManager = new Cache_Manager();
        $cache = $cacheManager->get('data');
        $blockManager = false;

        if($vers)
        {
            $user = User::getInstance();
            if($user->isAuthorized() && $user->isAdmin())
            {
                $pageData = array_merge($pageData , Model::factory('Vc')->getData('page' , $pageData['id'] , $vers));
                $showRevision = true;
            }
            else
            {
                $vers = false;
            }
        }

        if(!$vers && $cache)
            Blockmanager::setDefaultCache($cache);

        if($pageData['published'] == false && ! $showRevision)
            Response::redirect('/');

        $page = Page::getInstance();

        foreach($pageData as $k => $v)
            $page->$k = $v;

        /**
         * Check if controller attached
         */
        if(strlen($page->func_code))
        {
            $fModules = Config::factory(Config::File_Array , $this->_appConfig->get('frontend_modules'));

            if($fModules->offsetExists($page->func_code))
            {
                $controllerConfig = $fModules->get($page->func_code);
                $this->runController($controllerConfig['class'] , $this->_request->getPart(1));
            }
        }

        if(!$vers && $cache)
            Blockmanager::setDefaultCache($cache);

        $blockManager = new Blockmanager();

        if($page->show_blocks)
            $blockManager->init($page->id , $page->default_blocks , $vers);

        $this->showPage($page , $blockManager);
    }

    /**
     * Show Page.
     * Running this method initiates rendering of templates and sending of HTML
     * data.
     *
     * @param Page $page
     * @param Blockmanager $blockManager
     */
    public function showPage(Page $page , Blockmanager $blockManager)
    {
        header('Content-Type: text/html; charset=utf-8');

        $template = new Template();
        $template->disableCache();
        $template->setProperties(array(
            'development' => $this->_appConfig->get('development') ,
            'page' => $page ,
            'path' => $page->getThemePath() ,
            'templatesRoot' => Application::getTemplatesPath() ,
            'blockManager' => $blockManager ,
            'resource' => Resource::getInstance(),
            'pagesTree' => Model::factory('Page')->getTree()
        ));
        Response::put($template->render($page->getThemePath().'layout.php'));
    }

    /**
     * Run controller
     *
     * @param string $controller - controller class
     * @param string $action - action name
     * @return mixed
     */
    public function runController($controller , $action = false)
    {
        if((strpos('Backend_' , $controller) === 0))
            Response::redirect('/');

        parent::runController($controller , $action);
    }

    protected function _getModulesRoutes()
    {
        if(isset($this->_moduleRoutes))
            return $this->_moduleRoutes;

        $this->_moduleRoutes = array();

        $cacheManager = new Cache_Manager();
        $cache = $cacheManager->get('data');

        if(! $cache || ! $list = $cache->load(self::CACHE_KEY_ROUTES))
        {
            $pageModel = Model::factory('Page');
            $db = $pageModel->getDbConnection();
            $sql = $db->select()
                ->from($pageModel->table() , array(
                    'code' ,
                    'func_code'
                ))
                ->where('`published` = 1')
                ->where('`func_code` !="" ');
            $list = $db->fetchAll($sql);
            if($cache)
                $cache->save($list , self::CACHE_KEY_ROUTES);
        }

        if(!empty($list))
            foreach($list as $item)
                $this->_moduleRoutes[$item['func_code']] = $item['code'];

        return $this->_moduleRoutes;
    }
    /**
     * Define url address to call the module
     * The method locates the url of the published page with the attached
     * functionality
     * specified in the passed argument.
     * Thus, there is no need to know the exact page URL.
     *
     * @param string $module- module name
     * @return string
     */
    public function findUrl($module)
    {
        if(!isset($this->_moduleRoutes))
            $this->_getModulesRoutes();

        if(!isset($this->_moduleRoutes[$module]))
            return '';

        return $this->_moduleRoutes[$module];
    }
}