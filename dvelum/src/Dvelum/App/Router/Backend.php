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

use Dvelum\App\Module\Manager;
use Dvelum\Config;
use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Filter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Back office
 */
class Backend extends \Dvelum\App\Router
{
    /**
     * Route request to the Controller
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function route(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $requestHelper = new Request($request);
        $responseHelper = new Response($response);


        $configBackend = Config::storage()->get('backend.php');


        $controllerCode = $requestHelper->getPart(1);
        $controller = \Dvelum\Utils\Strings::formatClassName(Filter::filterValue('pagecode', $controllerCode));

        if (empty($controller)) {
            $controller = 'Index';
        }

        $coreClass = '\\Dvelum\\App\\Backend\\' . $controller . '\\Controller';

        if (in_array($coreClass, $configBackend->get('system_controllers')) && class_exists($coreClass)) {
            $controller = $coreClass;
        } else {
            $manager = $this->container->get(\Dvelum\App\Module\Manager::class);
            $controller = $manager->getModuleController($controller);
            if (empty($controller)) {
                $responseHelper->error(
                    $this->container->get(Lang::class)->lang()->get('WRONG_REQUEST') . ' ' . $requestHelper->getUri()
                );
                return $responseHelper->getPsrResponse();
            }
        }
        return $this->runController($controller, $requestHelper->getPart(2), $requestHelper, $responseHelper);
    }

    /**
     * @param string $module
     * @return string
     */
    public function findUrl(string $module): string
    {
        $cfg = Config::storage()->get('backend.php');
        return Request::factory()->url([$cfg['adminPath'], $module]);
    }
}
