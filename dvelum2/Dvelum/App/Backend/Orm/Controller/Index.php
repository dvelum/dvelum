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

namespace Dvelum\App\Backend\Orm\Controller;

use Dvelum\App\Backend\Orm\Manager;
use Dvelum\Config;
use Dvelum\Model;
use Dvelum\Orm;
use Dvelum\Lang;
use Dvelum\View;
use Dvelum\Template;

class Index extends \Dvelum\App\Backend\Controller
{
    public function getModule()
    {
        return 'Orm';
    }

    public function indexAction(){}

    /**
     * Save Object indexes
     * @todo validate index columns, check if they exists in config
     */
    public function saveIndexAction()
    {
        $this->checkCanEdit();

        $object =  $this->request->post('object', 'string', false);
        $index =   $this->request->post('index', 'string', false);
        $columns = $this->request->post('columns', 'array', array());
        $name = $this->request->post('name', 'string', false);
        $unique = $this->request->post('unique', 'boolean', false);
        $fulltext =$this->request->post('fulltext', 'boolean', false);

        if(!$object)
            $this->response->error($this->lang->get('WRONG_REQUEST').' code 1');

        if(!$name)
            $this->response->error($this->lang->get('FILL_FORM') , [['id'=>'name','msg'=>$this->lang->get('CANT_BE_EMPTY')]]);

        try{
            $objectCfg = Orm\Object\Config::factory($object);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('WRONG_REQUEST') .' code 2');
        }

        $indexData = array(
            'columns'=>$columns,
            'unique'=>$unique,
            'fulltext'=>$fulltext,
            'PRIMARY'=>false
        );

        $indexes = $objectCfg->getIndexesConfig();

        if($index !== $name && array_key_exists((string)$name, $indexes))
            $this->response->error($this->lang->get('FILL_FORM') , [['id'=>'name','msg'=>$this->lang->get('SB_UNIQUE')]]);

        if($index!=$name)
            $objectCfg->removeIndex($index);

        $objectCfg->setIndexConfig($name, $indexData);

        if($objectCfg->save())
            $this->response->success();
        else
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
    }

    /**
     * Delete object index
     */
    public function deleteIndexAction()
    {
        $this->checkCanDelete();

        $object =  $this->request->post('object', 'string', false);
        $index =   $this->request->post('name', 'string', false);

        if(!$object || !$index)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        try{
            $objectCfg = Orm\Object\Config::factory($object);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('WRONG_REQUEST') .' code 2');
        }

        $objectCfg->removeIndex($index);

        if($objectCfg->save())
            $this->response->success();
        else
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
    }

    /**
     * Load index config action
     */
    public function loadAction()
    {
        $object = $this->request->post('object', 'string',false);
        $index = $this->request->post('index', 'string',false);

        if(!$object || !$index)
            $this->response->error($this->lang->get('INVALID_VALUE'));

        $manager = new Manager();
        $indexConfig = $manager->getIndexConfig($object, $index);

        if($indexConfig === false)
            $this->response->error($this->lang->get('INVALID_VALUE'));
        else
            $this->response->success($indexConfig);

    }
}