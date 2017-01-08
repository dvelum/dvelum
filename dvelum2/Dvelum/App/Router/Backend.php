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

use Dvelum\Config;
use Dvelum\Lang;

/**
 * Back office
 */
class Backend extends \Dvelum\App\Router
{
    /**
     * Route request to the Controller
     * @return void
     */
    public function route()
    {
        $cfg = Config::storage()->get('backend.php');

        $controller = $this->request->getPart(1);
        $controller = \Utils_String::formatClassName(\Filter::filterValue('pagecode', $controller));

        if(empty($controller)){
            $controller = 'Index';
        }

        if(in_array('Backend_' . $controller . '_Controller', $cfg->get('system_controllers')))
        {
            $controller = 'Backend_' . $controller . '_Controller';
        }
        else
        {
            $manager = new \Modules_Manager();
            $controller = $manager->getModuleController($controller);

            if($controller === false) {
                $this->response->error(Lang::lang()->get('WRONG_REQUEST') . ' ' . $this->request->getUri());
            }
        }
        $this->runController($controller,  $this->request->getPart(2));
    }

    /**
     * (non-PHPdoc)
     * @see Router::findUrl()
     */
    public function findUrl(string $module) : string
    {
        $cfg = Config::storage()->get('backend.php');
        return $this->request->url(array($cfg['adminPath'] , $module),false);
    }
}
