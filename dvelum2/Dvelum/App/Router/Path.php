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
use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;

class Path extends Router
{
    protected $appConfig = false;

    public function __construct()
    {
        parent::__construct();
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

        $controllerClass = 'Frontend_' . $controller . '_Controller';

        if($controller !== false && strlen($controller) && class_exists($controllerClass)) {
            $controller = $controllerClass;
        } else {
            $controller = 'Frontend_Index_Controller';
        }
        $this->runController($controller , $request->getPart(1));
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
        return '/'.$module;
    }
}