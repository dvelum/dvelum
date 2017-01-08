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

use Dvelum\Request;
use Dvelum\Response;

/**
 * Base class for routing of requests
 */
abstract class Router implements Router\RouterInterface
{
    /**
     * @var Request $request
     */
    protected $request;
    /**
     * @var Response $response
     */
    protected $response;

    public function __construct()
    {
        $this->request = Request::factory();
        $this->response = Response::factory();
    }

    /**
     * Route request
     */
    abstract public function route();

    /**
     * Calc url for module
     * @param string $module — module name
     * @return string
     */
    abstract public function findUrl(string $module) : string;

    /**
     * Run controller
     * @param string $controller - controller class
     * @param string|boolean $action - action name
     * @return mixed
     */
    public function runController(string $controller , $action = false)
    {
        if(!class_exists($controller))
            return false;

        $controller = new $controller();
        $controller->setRouter($this);

        if($controller instanceof Router\RouterInterface){
            return $controller->route();
        }

        if(empty($action)){
            $action = 'index';
        }

        if(!method_exists($controller , $action.'Action')) {
            $this->response->error(Lang::lang()->get('WRONG_REQUEST').' ' . $this->request->getUri());
        }

        return $controller->{$action.'Action'}();
    }
}