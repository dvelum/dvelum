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

use Dvelum\Config;
use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Filter;

/**
 * Back office
 */
class Backend extends \Dvelum\App\Router
{
    /**
     * Route request to the Controller
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     * @return void
     */
    public function route(Request $request, Response $response): void
    {
        $configBackend = Config::storage()->get('backend.php');

        $controllerCode = $request->getPart(1);
        $controller = \Dvelum\Utils\Strings::formatClassName(Filter::filterValue('pagecode', $controllerCode));

        if (empty($controller)) {
            $controller = 'Index';
        }

        $coreClass = 'Backend_' . $controller . '_Controller';
        $core2Class = 'Dvelum\\App\\Backend\\' . $controller . '\\Controller';

        if (in_array($coreClass, $configBackend->get('system_controllers')) && class_exists($coreClass)) {
            $controller = $coreClass;
        } elseif (in_array($core2Class, $configBackend->get('system_controllers'))  && class_exists($core2Class)) {
            $controller = $core2Class;
        } else {
            $manager = new \Modules_Manager();
            $controller = $manager->getModuleController($controller);
            if (empty($controller)) {
                $response->error(Lang::lang()->get('WRONG_REQUEST') . ' ' . $request->getUri());
                return;
            }
        }

        $this->runController($controller, $request->getPart(2), $request, $response);
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
