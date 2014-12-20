<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
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
 * Routing of client requests for public content
 */
class Frontend_Router extends Router
{
  const CACHE_KEY_ROUTES = 'Frontend_Router_routes';
  protected $_type = 'path';
  protected $_appConfig = false;
  protected $_moduleRoutes;

  public function __construct()
  {
    parent::__construct();
    $this->_appConfig = Registry::get('main' , 'config');
    $this->setType($this->_appConfig->get('frontend_router_type'));
  }

  /**
   * Set routing type
   * @param string $type ('module' | 'path' | 'config')
   */
  public function setType($type)
  {
    $this->_type = $type;
  }

  /**
   * Route request
   *
   * @throws Exception
   */
  public function route()
  {
    $routeMethod = '_route' . ucfirst($this->_type);

    if(!method_exists($this , $routeMethod))
      throw new Exception('Undefined type of frontend routing. "' . $this->_type . '"');

    $this->$routeMethod();
  }

  protected function _routeModule()
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
    Response::put($template->render($page->getTemplatePath('layout.php')));
  }

  protected function _routePath()
  {
    $controller = $this->_request->getPart(0);
    $controller = ucfirst(Filter::filterValue('pagecode' , $controller));

    $controllerClass = 'Frontend_' . $controller . '_Controller';

    if($controller !== false && strlen($controller) && class_exists($controllerClass))
    {
      $controller = $controllerClass;
    }
    else
    {
      $controller = 'Frontend_Index_Controller';
    }

    $this->runController($controller , $this->_request->getPart(1));
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

  public function _routeConfig()
  {
    $controller = $this->_request->getPart(0);
    $pathCode = Filter::filterValue('pagecode' , $controller);
    $routes = Config::factory(Config::File_Array , $this->_appConfig->get('frontend_modules'))->__toArray();

    $controllerClass = false;

    if(isset($routes[$pathCode]) && class_exists($routes[$pathCode]['class']))
      $controllerClass = $routes[$pathCode]['class'];
    else
      $controllerClass = 'Frontend_Index_Controller';

    $this->runController($controllerClass , $this->_request->getPart(1));
  }
}