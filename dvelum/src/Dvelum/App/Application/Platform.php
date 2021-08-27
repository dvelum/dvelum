<?php

/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
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

namespace Dvelum\App\Application;

use Dvelum\Application;
use Dvelum\Config;
use Dvelum\Config\Storage\StorageInterface as ConfigStorageInterface;
use Dvelum\Db\ManagerInterface;
use Dvelum\Lang;
use Dvelum\Orm\Orm;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Template\Engine\EngineInterface;
use Dvelum\Template\Service;
use Dvelum\View;
use Dvelum\Externals;
use \Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Platform extends Application
{

    protected function init(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initExternals();
        // Init deprecated factories

        \Dvelum\View::setContainer($this->diContainer);
        \Dvelum\Orm::setContainer($this->diContainer);
    }

    /**
     * Init additional external modules
     * defined in external_modules option
     * of main configuration file
     */
    protected function initExternals()
    {
        $configStorage = $this->diContainer->get(Config\Storage\StorageInterface::class);
        $externals = $configStorage->get('external_modules.php');

        if ($externals->getCount()) {
            /**
             * @var Externals\Manager $manager
             */
            $manager = $this->diContainer->get(Externals\Manager::class);
            $manager->loadModules();
        }
    }

    /**
     * Initialize Database connection
     * @return ManagerInterface
     * @throws Exception
     */
    protected function initDb(): ManagerInterface
    {
        $manager = parent::initDb();

        $dev = $this->config->get('development');
        $dbErrorHandler = function (\Dvelum\Db\Adapter\Event $e) use ($dev) {
            $response = Response::factory();
            $request = Request::factory();
            if ($request->isAjax()) {
                $response->error(Lang::lang()->get('CANT_CONNECT'));
                exit();
            } else {
                $tpl = View::factory();
                $tpl->set('error_msg', ' ' . $e->getData()['message']);
                $tpl->set('development', $dev);
                echo $tpl->render('public/error.php');
                exit();
            }
        };
        $manager->setConnectionErrorHandler($dbErrorHandler);
        return $manager;
    }

    /**
     * Start application
     * @return void
     */
    public function run(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->init();

        $dvelumRequest = new Request($request);
        $page = $dvelumRequest->getPart(0);

        if ($page === $this->config->get('adminPath')) {
            $response = $this->routeBackOffice($request, $response);
        } else {
            $response = $this->routeFrontend($request, $response);
        }

        return $response;
    }

    /**
     * Run backend application
     */
    protected function routeBackOffice(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /*
         * Start routing
         */
        $router = new \Dvelum\App\Router\Backend($this->diContainer);
        return $router->route($request, $response);
    }

    /**
     * Run frontend application
     * @return void
     * @throws Exception
     */
    protected function routeFrontend(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $configStorage = $this->diContainer->get(ConfigStorageInterface::class);

        if ($this->config->get('maintenance')) {
            /**
             * @var  \Dvelum\Template\Service $tplService
             */
            $tplService = $this->diContainer->get(\Dvelum\Template\Service::class);
            $tpl = $tplService->getTemplate();
            $tpl->setData([
                              'request' => new Request($request),
                              'msg' => $this->diContainer->get(Lang::class)->getDictionary()->get('MAINTENANCE')
                          ]);

            $response->getBody()->write($tpl->render('public/maintenance.php'));
            return $response;
        }
        /*
         * Start routing
        */
        $frontConfig = $configStorage->get('frontend.php');
        $routerClass = $frontConfig->get('router');

        if (!class_exists($routerClass)) {
            $routerClass = $frontConfig->get('router');
        }

        /**
         * @var \Dvelum\App\Router\RouterInterface $router
         */
        $router = new $routerClass($this->diContainer);
        return $router->route($request, $response);
    }
}