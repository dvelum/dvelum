<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2017  Kirill Yegorov, Sergey Leschenko
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

use Dvelum\App\Session\User;
use Dvelum\Config\ConfigInterface;
use Dvelum\App\Module\Manager as ModuleManager;
use Dvelum\Lang;
use Dvelum\Request;

/**
 * Class Menu
 * @package Dvelum\App\Backend\Menu
 * @author Sergey Leschenko
 */
class Menu
{
    /**
     * @var ConfigInterface
     */
    protected $appConfig;
    /**
     * @var array
     */
    protected $options = [];
    /**
     * @var array
     */
    protected $menuData = [];
    /**
     * @var User
     */
    protected $user;
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Menu constructor.
     * @param User $user
     * @param ModuleManager $modulesManager
     * @param ConfigInterface $appConfig
     */
    public function __construct(User $user, ModuleManager $modulesManager, ConfigInterface $appConfig, Request $request)
    {
        $this->user = $user;
        $this->moduleManager = $modulesManager;
        $this->appConfig = $appConfig;
        $this->request = $request;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getData(): array
    {
        $menuData = [];
        $modules = $this->moduleManager->getList();
        $userModules = $this->user->getModuleAcl()->getAvailableModules();
        foreach($modules as $data)
        {
            if(!$data['active'] || !$data['in_menu'] || !isset($userModules[$data['id']])){
                continue;
            }
            $menuData[] = [
                'id' => $data['id'],
                'dev' => $data['dev'],
                'url' =>  $this->request->url(array($this->appConfig->get('adminPath'),$data['id'])),
                'title'=> $data['title'],
                'icon'=> $this->request->wwwRoot().$data['icon']
            ];
        }
        $menuData[] = [
            'id' => 'logout',
            'dev' => false,
            'url' =>  $this->request->url([$this->appConfig->get('adminPath'),'']) . 'login/logout',
            'title'=> Lang::lang()->get('LOGOUT'),
            'icon' => $this->request->wwwRoot() . 'i/system/icons/logout.png'
        ];
        return  $menuData;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getIncludes() : array
    {
        $wwwRoot = $this->appConfig->get('wwwroot');
        $data = [
            'css' => [],
            'js' => [
                str_replace('//', '/', $wwwRoot . '/js/app/system/MenuPanel.js') => ['order' => 0, 'minified' => false]
            ]
        ];
        return $data;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function render() : string
    {
        $menuData = $this->getData();

        return "Ext.create('app.menuPanel',{
			menuData:" . json_encode($menuData) . ",
			isVertical:" . intval($this->options['isVertical']) . ",
			devMode:" . intval($this->options['development']) . ",
			stateEvents: ['menuCollapsed', 'menuExpanded'],
			stateful:" . intval($this->options['stateful']) . ",
			stateId:'_appMenuState'
		});";
    }
}