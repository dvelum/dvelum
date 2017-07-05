<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
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
 */

declare(strict_types=1);

namespace Dvelum\App;

use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Lang;

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

    /**
     * Route request
     * @param Request $request
     * @param Response $response
     * @return void
     */
    abstract public function route(Request $request , Response $response) : void;

    /**
     * Calc url for module
     * @param string $module — module name
     * @return string
     */
    abstract public function findUrl(string $module) : string;

    /**
     * Run controller
     * @param string $controller
     * @param null|string $action
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     */
    public function runController(string $controller , ?string $action, Request $request , Response $response) : void
    {
        if(!class_exists($controller)){
            throw new \Exception('Undefined Controller: '. $controller);
        }

        /**
         * @var \Dvelum\App\Controller $controller
         */
        $controller = new $controller($request, $response);
        $controller->setRouter($this);

        if($response->isSent()){
            return;
        }

        if($controller instanceof Router\RouterInterface || $controller instanceof \Router_Interface){
            $controller->route($request, $response);
            return;
        }

        if(empty($action)){
            $action = 'index';
        }

        if(!method_exists($controller , $action.'Action')) {
            $response->error(Lang::lang()->get('WRONG_REQUEST').' ' . $request->getUri());
            return;
        }

        $controller->{$action.'Action'}();

        if(!$response->isSent() && method_exists($controller,'showPage')){
            $controller->showPage();
        }

        return;
    }
}