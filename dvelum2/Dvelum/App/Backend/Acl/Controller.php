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

namespace Dvelum\App\Backend\Acl;

use Dvelum\App\Backend;
use Dvelum\Orm\Model;
use Dvelum\Orm;

class Controller extends Backend\Ui\Controller
{

    public function getModule(): string
    {
        return 'Acl';
    }

    public function getObjectName(): string
    {
        return 'Acl';
    }

    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
    public function indexAction()
    {
        $this->resource->addJs('/js/app/system/Acl.js' , true , 1);
        $this->resource->addJs('/js/app/system/crud/acl.js' , true , 2);
        $this->resource->addInlineJs('
        	var canEdit = ' . ((integer) $this->checkCanEdit()) . ';
        	var canDelete = ' . ((integer) $this->checkCanDelete()) . ';
        ');
    }
    /**
     * Groups list action
     */
    public function grouplistAction()
    {
        $data = Model::factory('Group')
                ->query()
                ->fields([
                    'id' ,
                    'title' ,
                    'system'
                ])->fetchAll();

        $this->response->success($data);
    }
    /**
     * List permissions action
     */
    public function permissionsAction()
    {
        $user = $this->request->post('user_id' , 'int' , 0);
        $group = $this->request->post('group_id' , 'int' , 0);

        if($user && $group)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        if($group)
            $data = Model::factory('acl_simple')->getGroupPermissions($group);

        if(!empty($data))
            $data = \Utils::rekey('object' , $data);

        $manager = new Orm\Object\Manager();
        $objects = $manager->getRegisteredObjects();

        foreach($objects as $name)
        {
            if(!isset($data[$name]))
            {
                $data[$name] = array(
                    'object' => $name ,
                    'create' => false,
                    'view' => false ,
                    'edit' => false ,
                    'delete' => false ,
                    'user_id'=>null,
                    'publish'=>false,
                    'group_id'=>$group
                );
            }
        }

        foreach($data as $k => &$v)
        {
            if(!Orm\Object\Config::configExists($k))
            {
                unset($data[$k]);
                continue;
            }
            $cfg = Orm\Object\Config::factory($k);

            if($cfg->isRevControl())
                $v['rc'] = true;
            else
                $v['rc'] = false;

            $v['title'] = $cfg->getTitle();
        }
        unset($v);
        $this->response->success(array_values($data));
    }
    /**
     * Save permissions action
     */
    public function savepermissionsAction()
    {
        $this->checkCanEdit();

        $data = $this->request->post('data' , 'raw' , false);
        $groupId = $this->request->post('group_id' , 'int' , false);
        $data = json_decode($data , true);

        if(empty($data) || ! $groupId)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        if(Model::factory('acl_simple')->updateGroupPermissions($groupId , $data))
            $this->response->success();
        else
            $this->response->error($this->lang->get('CANT_EXEC'));
    }
    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Acl.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->appConfig->get('jsPath').'app/system/desktop/' . strtolower($this->getModule()) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->getModule()) .'.js';

        return $projectData;
    }
}