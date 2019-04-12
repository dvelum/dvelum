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

namespace Dvelum\App\Console\External;

use Dvelum\App\Console;
use Dvelum\Externals\Manager;
use Dvelum\Filter;
use Dvelum\Request;

class Add extends Console\Action
{
    public function action(): bool
    {
        $request = Request::factory();
        $vendor = Filter::filterString($request->getPart(1));
        $module = Filter::filterString($request->getPart(2));

        if(empty($vendor) || empty($module)){
            return false;
        }

        $moduleDir = $this->appConfig->get('externals')['path'] . '/' . $vendor . '/' . $module;

        if(!is_dir($moduleDir)){
            return false;
        }

        if(!file_exists($moduleDir.'/config.php')){
            return false;
        }

        $moduleInfo = include $moduleDir . '/config.php';
        if(!is_array($moduleInfo) || empty($moduleInfo)){
            return false;
        }

        $manager = Manager::factory();
        if($manager->moduleExists($moduleInfo['id'])){
            return true;
        }

        return $manager->add($moduleInfo['id'],[
            'enabled' => true,
            'installed' => false,
            'path' =>$moduleDir
        ]);
    }
}