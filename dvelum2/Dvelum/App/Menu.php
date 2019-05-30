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

class Menu{
    protected $mainConfig;
    protected $options = [];
    protected $menuData = [];

    public function __construct(){
        $this->mainConfig = Config::storage()->get('main.php');
    }

    public function setOptions(array $options = []){
        $this->options = $options;
    }

    public function setData(array $menuData = []){
        $this->menuData = $menuData;
    }

    public function getIncludes(){
        $wwwRoot = $this->mainConfig->get('wwwroot');
        $data = [
            'css' => [],
            'js' => [
                str_replace('//', '/',
                $wwwRoot.'/js/app/system/MenuPanel.js') => ['order' => 0, 'minified' => false]
            ]
        ];
        return $data;
    }

    public function render(){
        return "Ext.create('app.menuPanel',{
			menuData:".json_encode($this->menuData).",
			isVertical:".intval($this->options['isVertical']).",
			devMode:".intval($this->options['development']).",
			stateEvents: ['menuCollapsed', 'menuExpanded'],
			stateful:". intval($this->options['stateful']).",
			stateId:'_appMenuState'
		});";
    }
}