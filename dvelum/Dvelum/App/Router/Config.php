<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
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
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Config as Cfg;

class Config extends Router
{
    protected $appConfig = false;

    public function __construct()
    {
        $this->appConfig = Cfg::storage()->get('main.php');
    }

    /**
     * Route request
     * @param Request $request
     * @param Response $response
     */
    public function route(Request $request , Response $response) : void
    {
        $frontConfig = Cfg::storage()->get('frontend.php');
        $defaultController =  $frontConfig->get('default_controller');

        $controller = $request->getPart(0);
        $pathCode = \Filter::filterValue('pagecode' , $controller);
        $routes = Cfg::factory(Cfg\Factory::File_Array , $this->appConfig->get('frontend_modules'))->__toArray();

        if(isset($routes[$pathCode]) && class_exists($routes[$pathCode]['class']))
            $controllerClass = $routes[$pathCode]['class'];
        else
            $controllerClass = $defaultController;

        $this->runController($controllerClass , $request->getPart(1), $request, $response);
    }

    /**
     * @param string $controller
     * @param null|string $action
     * @param Request $request
     * @param Response $response
     */
    public function runController(string $controller , ?string $action, Request $request , Response $response) : void
    {
        if((strpos('Backend_' , $controller) === 0)){
            $response->redirect('/');
            return;
        }
        parent::runController($controller, $action, $request, $response);
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
    public function findUrl(string $module) : string
    {
        return '/'.$module;
    }
}