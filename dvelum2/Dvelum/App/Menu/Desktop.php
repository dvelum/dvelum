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

namespace Dvelum\App\Menu;

use Dvelum\Config;
use Dvelum\App\Menu;
use User;

class Desktop extends Menu{

    public function getIncludes(){
        return [
            'css' => [],
            'js' => []
        ];
    }

    public function render(){
        return '
        app.menuData = '.json_encode($this->menuData).';
		app.permissions = Ext.create("app.PermissionsStorage");
		var rights = '.json_encode(User::getInstance()->getPermissions()).';
		app.permissions.setData(rights);
		app.version = "'.Config::storage()->get('versions.php')->get('core').'"
		app.user = {
			name: "'.User::getInstance()->getInfo()['name'].'"
		}
        ';
    }
}