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
use Dvelum\Db\ManagerInterface;
use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\View;
use Dvelum\Externals;
use \Exception;

class Platform extends Application
{

    /**
     * Initialize the application, configure the settings, inject dependencies
     * Adjust the settings necessary for running the system
     * @return void
     * @throws Exception
     */
    protected function init() : void
    {
        if ($this->initialized) {
            return;
        }

        parent::init();

        // init external modules
        $this->initExternals();

        $request = Request::factory();
        $response = Response::factory();

        if ($request->isAjax()) {
            $response->setFormat(Response::FORMAT_JSON);
        } else {
            $response->setFormat(Response::FORMAT_HTML);
        }
    }

    /**
     * Init additional external modules
     * defined in external_modules option
     * of main configuration file
     */
    protected function initExternals()
    {
        $externals = Config\Factory::storage()->get('external_modules.php');

        Externals\Manager::setConfig([
            'appConfig' => $this->config,
            'autoloader' => $this->autoloader
        ]);

        if ($externals->getCount()) {
            Externals\Manager::factory()->loadModules();
        }
    }

    /**
     * Initialize Database connection
     * @return ManagerInterface
     * @throws Exception
     */
    protected function initDb() : ManagerInterface
    {
        $manager = parent::initDb();

        $dev = $this->config->get('development');
        $dbErrorHandler = function ( \Dvelum\Db\Adapter\Event $e) use( $dev){
            $response = Response::factory();
            $request = Request::factory();
            if($request->isAjax()){
                $response->error(Lang::lang()->get('CANT_CONNECT'));
                exit();
            }else{
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
     * @throws Exception
     */
    public function run() : void
    {
        parent::run();
        $page = Request::factory()->getPart(0);

        if ($page === $this->config->get('adminPath')) {
            $this->routeBackOffice();
        }else{
            $this->routeFrontend();
        }
    }

    /**
     * Run backend application
     */
    protected function routeBackOffice()
    {
        $request = Request::factory();
        $response = Response::factory();
        /*
         * Start routing
         */
        $router = new \Dvelum\App\Router\Backend();
        $router->route($request, $response);

        if (!$response->isSent()) {
            $response->send();
        }
    }

    /**
     * Run frontend application
     * @return void
     * @throws Exception
     */
    protected function routeFrontend() : void
    {
        $request = Request::factory();
        $response = Response::factory();

        if ($this->config->get('maintenance')) {
            $tpl = View::factory();
            $tpl->set('msg', Lang::lang()->get('MAINTENANCE'));
            $response->put($tpl->render('public/maintenance.php'));
            $response->send();
            return;
        }

        /*
         * Start routing
        */
        $frontConfig = Config::storage()->get('frontend.php');
        $routerClass = '\\Dvelum\\App\\Router\\' . $frontConfig->get('router');

        if (!class_exists($routerClass)) {
            $routerClass = $frontConfig->get('router');
        }

        /**
         * @var \Dvelum\App\Router $router
         */
        $router = new $routerClass();
        $router->route($request, $response);

        if (!$response->isSent()) {
            $response->send();
        }
    }
}