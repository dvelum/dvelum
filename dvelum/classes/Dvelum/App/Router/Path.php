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
use Dvelum\Config;
use Dvelum\Filter;
use Dvelum\Request;
use Dvelum\Response;

class Path extends Router
{
    protected $appConfig = false;

    public function __construct()
    {
        $this->appConfig = Config::storage()->get('main.php');
    }

    /**
     * Route request
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     * @return void
     */
    public function route(Request $request , Response $response) :void
    {
        $controller = $request->getPart(0);
        $controller = ucfirst(Filter::filterValue('pagecode' , $controller));

        $controllerClass = 'Frontend\\Index\\Controller';

        if($controller !== false && strlen($controller)){
            $classNamespace1 = 'Frontend_' . $controller . '_Controller';
            $classNamespace2 = 'Frontend\\' . $controller . '\\Controller';
            $classNamespace3 = 'Dvelum\\App\\Frontend\\' . $controller . '\\Controller';

            if(class_exists($classNamespace1)){
                $controllerClass = $classNamespace1;
            }elseif (class_exists($classNamespace2)){
                $controllerClass = $classNamespace2;
            }elseif (class_exists($classNamespace3)){
                $controllerClass = $classNamespace3;
            }
        }
        $this->runController($controllerClass , $request->getPart(1), $request, $response);
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
        if((strpos('Backend_' , $controller) === 0) || strpos('\\Backend\\', $controller)!==false) {
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
    public function findUrl(string $module): string
    {
        return '/' . $module;
    }
}