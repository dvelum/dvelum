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
namespace Dvelum\App\Backend\Orm\Controller;

use Dvelum\App\Backend\Controller;
use Dvelum\App\Backend\Orm\Manager;
use Dvelum\Orm;

class Index extends Controller
{
    public function getModule(): string
    {
        return 'Orm';
    }

    public function indexAction(){}

    /**
     * Save Object indexes
     * @todo validate index columns, check if they exists in config
     */
    public function saveAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $object =  $this->request->post('object', 'string', false);
        $index =   $this->request->post('index', 'string', false);
        $columns = $this->request->post('columns', 'array', array());
        $name = $this->request->post('name', 'string', false);
        $unique = $this->request->post('unique', 'boolean', false);
        $fulltext =$this->request->post('fulltext', 'boolean', false);

        if(!$object){
            $this->response->error($this->lang->get('WRONG_REQUEST').' code 1');
            return;
        }

        if(!$name){
            $this->response->error($this->lang->get('FILL_FORM') , [['id'=>'name','msg'=>$this->lang->get('CANT_BE_EMPTY')]]);
            return;
        }

        try{
            $objectCfg = Orm\Record\Config::factory($object);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('WRONG_REQUEST') .' code 2');
            return;
        }

        $indexData = array(
            'columns'=>$columns,
            'unique'=>$unique,
            'fulltext'=>$fulltext,
            'PRIMARY'=>false
        );

        $indexes = $objectCfg->getIndexesConfig();

        if($index !== $name && array_key_exists((string)$name, $indexes)){
            $this->response->error($this->lang->get('FILL_FORM') , [['id'=>'name','msg'=>$this->lang->get('SB_UNIQUE')]]);
            return;
        }

        $indexManager = new Orm\Record\Config\IndexManager();
        if($index!=$name){
            $indexManager->removeIndex($objectCfg, $index);
        }

        $indexManager->setIndexConfig($objectCfg, $name, $indexData);

        if($objectCfg->save())
            $this->response->success();
        else
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
    }

    /**
     * Delete object index
     */
    public function deleteAction()
    {
        if(!$this->checkCanDelete()){
            return;
        }

        $object =  $this->request->post('object', 'string', false);
        $index =   $this->request->post('name', 'string', false);

        if(!$object || !$index){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        try{
            $objectCfg = Orm\Record\Config::factory($object);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('WRONG_REQUEST') .' code 2');
            return;
        }

        $indexManager = new Orm\Record\Config\IndexManager();
        $indexManager->removeIndex($objectCfg, $index);

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

        if(!$object || !$index){
            $this->response->error($this->lang->get('INVALID_VALUE'));
            return;
        }

        $manager = new Manager();
        $indexConfig = $manager->getIndexConfig($object, $index);

        if($indexConfig === false)
            $this->response->error($this->lang->get('INVALID_VALUE'));
        else
            $this->response->success($indexConfig);

    }
}