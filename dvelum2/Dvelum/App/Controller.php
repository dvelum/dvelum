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

use Dvelum\Config;
use Dvelum\Request;
use Dvelum\Resource;
use Dvelum\Response;

class Controller
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Config\Config|false
     */
    protected $appConfig;
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var \Router_Interface
     */
    protected $router;

    /**
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->resource = Resource::factory();
        $this->appConfig = Config::storage()->get('main.php');
    }

    /**
     * Set link to router
     * @param Router\RouterInterface $router
     */
    public function setRouter(Router\RouterInterface $router)
    {
        $this->router = $router;
    }
}