<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
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
 */
declare(strict_types=1);

namespace Dvelum\App\Backend\Index;

use Dvelum\App;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config;

/**
 * Default backoffice controller
 */
class Controller extends App\Backend\Controller
{
    public function getModule() : string
    {
        return 'index';
    }

    public function indexAction()
    {
        $config = Config::storage()->get('backend.php');
        $this->includeScripts();
        if(!in_array($config->get('theme') , $config->get('desktop_themes') , true)){
            $this->resource->addJs('js/app/system/crud/index.js', 4);
            return;
        }
    }

    /**
     * Get modules list
     */
    public function listAction()
    {
        $modulesManager = new App\Module\Manager();

        $data = $modulesManager->getList();

        $modules = $this->user->getModuleAcl()->getAvailableModules();

        $data = \Dvelum\Utils::sortByField($data  , 'title');

        $isDev = (boolean) $this->appConfig->get('development');
        $wwwRoot = $this->appConfig->get('wwwroot');
        $adminPath =  $this->appConfig->get('adminPath');

        $result = [];
        $devItems = [];

        foreach($data as $config)
        {
            if(!$config['active'] || !$config['in_menu'] || ($config['dev'] && !$isDev) || !isset($modules[$config['id']])){
                continue;
            }
            $item =[
                'id' => $config['id'],
                'icon'=> $wwwRoot.$config['icon'],
                'title'=> $config['title'],
                'url'=> $this->request->url([$adminPath , $config['id']]),
                'itemCls'=>$config['dev']?'dev':''
            ];
            if($config['dev']){
                $devItems[] = $item;
            }else{
                $result[] = $item;
            }

        }
        $this->response->success(array_merge($result,$devItems));
    }

    /**
     * Get module info
     */
    public function moduleInfoAction()
    {
        $module = $this->request->post('id' , \Dvelum\Filter::FILTER_STRING , false);

        $manager = new App\Module\Manager();
        $moduleCfg = $manager->getModuleConfig($module);

        $info = [];

        if(!$module || !$this->user->getModuleAcl()->canView($module) || !$moduleCfg['active']){
            $this->response->error($this->lang->get('CANT_VIEW'));
            return;
        }

        $controller = $moduleCfg['class'];

        if(!class_exists($controller)){
            $this->response->error('Undefined controller');
            return;
        }

        $controller = new $controller($this->request, $this->response);

        if(method_exists($controller,'desktopModuleInfo')){
            $info['layout'] = $controller->desktopModuleInfo();
        }else{
            $info['layout'] = false;
        }

        $info['permissions'] = $this->user->getModuleAcl()->getModulePermissions($module);
        $this->response->success($info);
    }
}