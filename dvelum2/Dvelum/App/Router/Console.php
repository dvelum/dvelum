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
use Dvelum\Request;
use Dvelum\Response;

class Console extends Router
{
    /**
     * Route request
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function route(Request $request , Response $response) : void
    {
        $controllerClass = '\\Dvelum\\App\\Console\\Controller';
        $this->runController($controllerClass , $request->getPart(0), $request, $response);
    }

    /**
     * Calc url for module
     * @param string $module — module name
     * @return string
     */
    public function findUrl(string $module) : string{
        return '';
    }
}