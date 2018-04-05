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
namespace Dvelum\App\Backend\Orm\Controller;

use Dvelum\App\Backend\Orm\Manager;
use Dvelum\App\Backend\Controller;


use \Exception;
use Dvelum\Orm\Record;

class Distributed extends Controller
{
    public function getModule(): string
    {
        return 'Orm';
    }

    public function indexAction(){}
    /**
     * Add distributed index
     */
    public function addDistributedIndexAction()
    {
        $object = $this->request->post('object', 'string', false);
        $field = $this->request->post('field','string',false);

        if(!$object){
            $this->response->error($this->lang->get('INVALID_VALUE'));
            return;
        }
           

        try{
            $objectConfig = Record\Config::factory($object);
        }catch (Exception $e){
            $this->response->error($this->lang->get('INVALID_VALUE'));
            return;
        }

        if(!$objectConfig->fieldExists($field)){
            $this->response->error($this->lang->get('INVALID_VALUE'));
            return;
        }

        $objectConfig->setDistributedIndexConfig($field, ['field'=>$field,'is_system'=>false]);

        $manager = new Manager();

        if($objectConfig->save()){

            try{
                if(!$manager->syncDistributedIndex($object)){
                    $this->response->error($this->lang->get('CANT_WRITE_FS'));
                    return;
                }
                $this->response->success();
            }catch (Exception $e){
                $this->response->error($e->getMessage());
            }
        } else{
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
        }

    }

    /**
     * Get distributed indexes
     */
    public function distIndexesAction()
    {
        $object = $this->request->post('object', 'string', false);

        if(!$object)
            $this->response->error($this->lang->get('INVALID_VALUE'));

        try{
            $objectConfig =  Record\Config::factory($object);
        }catch (Exception $e){
            $this->response->error($this->lang->get('INVALID_VALUE'));
            return;
        }

        $list = [];
        $indexCfg = $objectConfig->getDistributedIndexesConfig();

        if(!empty($indexCfg)){
            foreach ($indexCfg as $v){
                $list[] = ['field'=>$v['field'],'is_system'=>$v['is_system']];
            }
        }
        $this->response->json(array_values($list));
    }

    /**
     * Delete distributed index
     */
    public function deleteDistributedIndexAction()
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
            $objectCfg =  Record\Config::factory($object);
        }catch (Exception $e){
            $this->response->error($this->lang->get('WRONG_REQUEST '.' code 2'));
            return;
        }

        $objectCfg->removeDistributedIndex($index);
        $manager = new Manager();

        if($objectCfg->save() && $manager->syncDistributedIndex($object))
            $this->response->success();
        else
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
    }

    /**
     * Get list of fields that can be added as distributed index
     */
    public function acceptedDistributedFieldsAction()
    {
        $object = $this->request->post('object', 'string', false);
        
        if(!$object){
            $this->response->error($this->lang->get('INVALID_VALUE'));
            return;
        }

        try{
            $objectConfig =  Record\Config::factory($object);
        }catch (Exception $e){
            $this->response->error($this->lang->get('INVALID_VALUE'));
            return;
        }

        $indexCfg = $objectConfig->getDistributedIndexesConfig();
        $fields = $objectConfig->getFieldsConfig();

        $data = [];
        foreach ($fields as $name=>$config){
            if(isset($indexCfg[$name])){
                continue;
            }
            $dbType = $config['db_type'];
            if(
                in_array($dbType, Record\Builder::$charTypes, true)
                ||
                in_array($dbType, Record\Builder::$numTypes, true)
                ||
                in_array($dbType, Record\Builder::$dateTypes, true)
            ){
                $data[] = ['name'=>$name];
            }
        }
        $this->response->success($data);
    }

}