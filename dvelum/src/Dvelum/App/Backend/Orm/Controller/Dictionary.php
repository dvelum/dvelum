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
use Dvelum\App\Dictionary\Manager;

class Dictionary extends Controller
{
    public function getModule(): string
    {
        return 'Orm';
    }

    public function indexAction(){}

    /**
     * Create new dictionary or rename existed
     */
    public function updateAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }
        
        $id = $this->request->post('id','string',false);
        $name = strtolower($this->request->post('name','string',false));

        $manager = Manager::factory();
        if(!$name)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        if(!$id){
            if(!$manager->create($name)){
                $this->response->error($this->lang->get('CANT_WRITE_FS') .' '. $this->lang->get('OR') .' '. $this->lang->get('DICTIONARY_EXISTS'));
            }
        }else{
            if(!$manager->rename($id, $name))
                $this->response->error($this->lang->get('CANT_WRITE_FS'));
        }

       $this->response->success();
    }
    /**
     * Remove dictionary
     */
    public function removeAction()
    {
        $manager = Manager::factory();

        if(!$this->checkCanDelete()){
            return;
        }

        $name = strtolower($this->request->post('name','string',false));
        if(empty($name))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        if(!$manager->remove($name))
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
        else
           $this->response->success();
    }
    /**
     * Get dictionary list
     */
    public function listAction()
    {
        $manager = Manager::factory();
        $data = [];
        $list = $manager->getList();

        if(!empty($list))
            foreach ($list as $v)
                $data[] = ['id' => $v,'title' => $v];

       $this->response->success($data);
    }
    /**
     * Get dictionary records list
     */
    public function recordsAction()
    {
        $name = strtolower($this->request->post('dictionary','string',false));
        if (empty($name))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $list = \Dvelum\App\Dictionary::factory($name)->getData();

        $data = [];

        if(!empty($list))
            foreach ($list as $k=>$v)
                $data[] = ['id' => $k,'key' => $k,'value' => $v];

       $this->response->success($data);
    }
    /**
     * Update dictionary records
     */
    public function updateRecordsAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $dictionaryName = strtolower($this->request->post('dictionary','string',false));
        $data = $this->request->post('data','raw',false);
        $data = json_decode($data, true);

        if(empty($data) || !strlen($dictionaryName))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $dictionary = \Dvelum\App\Dictionary::factory($dictionaryName);
        foreach ($data as $v)
        {
            if($dictionary->isValidKey($v['key']) && $v['key'] != $v['id'])
                $this->response->error($this->lang->get('WRONG_REQUEST'));

            if(!empty($v['id']))
                $dictionary->removeRecord($v['id']);
            $dictionary->addRecord($v['key'], $v['value']);
        }
        if(!Manager::factory()->saveChanges($dictionaryName))
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
       $this->response->success();
    }
    /**
     * Remove dictionary record
     */
    public function removeRecordsAction()
    {
        $dictionaryName = strtolower($this->request->post('dictionary','string',false));
        $name = $this->request->post('name','string',false);

        if(!strlen($name) || !strlen($dictionaryName))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $dictionary = \Dvelum\App\Dictionary::factory($dictionaryName);
        $dictionary->removeRecord($name);

        if(!Manager::factory()->saveChanges($dictionaryName))
            $this->response->error($this->lang->get('CANT_WRITE_FS'));

       $this->response->success();
    }
}