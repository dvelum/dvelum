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

namespace Dvelum\App\Backend\Menu;

use Dvelum\Config;

/**
 * Class Desktop
 * @package Dvelum\App\Backend\Menu
 * @author Sergey Leschenko
 */
class Desktop extends Menu
{
    /**
     * @return array
     */
    public function getIncludes() : array
    {
        return [
            'css' => [],
            'js' => []
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function render() : string
    {
        return '
        app.menuData = ' . json_encode($this->getData()) . ';
		app.permissions = Ext.create("app.PermissionsStorage");
		var rights = ' . json_encode($this->user->getModuleAcl()->getPermissions()) . ';
		app.permissions.setData(rights);
		app.version = "' . Config::storage()->get('versions.php')->get('platform') . '"
		app.user = {
			name: "' . $this->user->getInfo()['name'] . '"
		}
        ';
    }
}