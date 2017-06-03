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

namespace Dvelum\App\Router;

use Dvelum\App\Router;
use Dvelum\Config;
use Dvelum\View;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Orm\Model;
use Dvelum\App\BlockManager;
use Dvelum\Service as ServiceLocator;

/**
 * Back office
 */
class Module extends Router
{
    const CACHE_KEY_ROUTES = 'Frontend_Routes';
    
    protected $appConfig = false;
    protected $moduleRoutes;

    public function __construct()
    {
        $this->appConfig = Config::storage()->get('main.php');
    }

    /**
     * Route request
     * @param Request $request
     * @param Response $response
     */
    public function route(Request $request , Response $response) : void
    {
        $vers = $request->get('vers' , 'int' , false);
        $showRevision = false;
        $pageCode = $request->getPart(0);

        if(!is_string($pageCode) || !strlen($pageCode))
            $pageCode = 'index';

        $pageData = Model::factory('Page')->getCachedItemByField('code' , $pageCode);

        if(empty($pageData)){
            $response->redirect('/');
            return;
        }

        $cacheManager = new \Cache_Manager();
        $cache = $cacheManager->get('data');

        if($vers)
        {
            $user = \User::getInstance();
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

        if($pageData['published'] == false && ! $showRevision){
            $response->redirect('/');
        }


        $page = \Page::getInstance();

        foreach($pageData as $k => $v)
            $page->{$k} = $v;

        /**
         * Check if controller attached
         */
        if(strlen($page->func_code))
        {
            $fModules = Config::factory(Config\Factory::File_Array , $this->appConfig->get('frontend_modules'));

            if($fModules->offsetExists($page->func_code))
            {
                $controllerConfig = $fModules->get($page->func_code);
                $this->runController($controllerConfig['class'] , $request->getPart(1), $request, $response);
            }
        }

        /**
         * @var BlockManager $blockManager
         */
        $blockManager = ServiceLocator::get('blockmanager');

        if($vers){
            $blockManager->disableCache();
        }

        if($page->show_blocks)
            $blockManager->init($page->id , $page->default_blocks , $vers);

        $this->showPage($page , $blockManager, $request, $response);
    }

    /**
     * Show Page.
     * Running this method initiates rendering of templates and sending of HTML
     * data.
     *
     * @param Request $request
     * @param Response $response
     * @param \Page $page
     * @param BlockManager $blockManager
     * @return void
     */
    public function showPage(\Page $page , BlockManager $blockManager, Request $request , Response $response) : void
    {
        header('Content-Type: text/html; charset=utf-8');

        $template = new View();
        $template->disableCache();
        $template->setProperties(array(
            'development' => $this->appConfig->get('development') ,
            'page' => $page ,
            'path' => $page->getThemePath() ,
            'blockManager' => $blockManager ,
            'resource' => \Dvelum\Resource::factory(),
            'pagesTree' => Model::factory('Page')->getTree()
        ));
        $response->put($template->render($page->getThemePath().'layout.php'));
    }

    /**
     * Run controller
     * @param string $controller
     * @param null|string $action
     * @param Request $request
     * @param Response $response
     */
    public function runController(string $controller , ?string $action, Request $request , Response $response) : void
    {
        if((strpos('Backend_' , $controller) === 0)) {
            $response->redirect('/');
            return;
        }

        parent::runController($controller, $action,  $request, $response);
    }

    protected function getModulesRoutes()
    {
        if(isset($this->moduleRoutes))
            return $this->moduleRoutes;

        $this->moduleRoutes = array();

        $cacheManager = new \Cache_Manager();
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
                $this->moduleRoutes[$item['func_code']] = $item['code'];

        return $this->moduleRoutes;
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
    public function findUrl(string$module):string
    {
        if(!isset($this->moduleRoutes))
            $this->getModulesRoutes();

        if(!isset($this->moduleRoutes[$module]))
            return '';

        return $this->moduleRoutes[$module];
    }
}