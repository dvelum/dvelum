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

namespace Dvelum\App\Backend\Logs;

use Dvelum\App\Backend;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Filter;
use Dvelum\Utils;
use Dvelum\Db\Select;

class Controller extends Backend\Ui\Controller
{
	protected  $canViewObjects = ['User'];

    public function getModule(): string
    {
        return 'Logs';
    }

    public function getObjectName(): string
    {
        return 'Historylog';
    }

    public function listAction()
	{
		$pager = $this->request->post('pager', 'array', array());
		$filter = $this->request->post('filter', 'array', array());

		$history = Model::factory($this->getObjectName());

        if(isset($filter['date']) && !empty($filter['date'])){
            $date = date('Y-m-d' ,strtotime($filter['date']));
            $filter['date'] = new Select\Filter('date',array(
                $date.' 00:00:00', $date.' 23:59:59'
            ),Select\Filter::BETWEEN);
        }

        $data = $history->getList($pager, $filter, ['date','type','id','object','user_id','record_id']);

		if(!empty($data))
		{
            $users = Utils::fetchCol('user_id' , $data);
            $users = Orm\Object::factory('User' , $users);

			foreach ($data as $k=>&$v)
			{
                if(!empty($v['user_id']) && isset($users[$v['user_id']])){
                    $v['user_name'] = $users[$v['user_id']]->getTitle();
                }
                if(!empty($v['object']) && Orm\Object\Config::configExists($v['object'])){
                    $v['object_title'] = Orm\Object\Config::factory($v['object'])->getTitle();
                }
			}unset($v);
		}

		$this->response->success(
		    $data ,
            [
                'count'=>$history->query()
                         ->filters($filter)
                         ->getCount()
            ]
        );
	}

    /**
     * Get list of registered DB Objects
     */
    public function objectsListAction()
    {
        $manager = new Orm\Object\Manager();
        $list = $manager->getRegisteredObjects();
        $data = [];
        foreach ($list as $object){
            $data[] = ['id'=>$object, 'title' => Orm\Object\Config::factory($object)->getTitle()];
        }
        $this->response->success($data);
    }

    /**
     * Get changes list
     */
    public function changesListAction()
    {
        $filter = $this->request->post('filter' , Filter::FILTER_ARRAY , false);

        if(empty($filter['id'])){
            $this->response->success();
        }

        $id = intval($filter['id']);

        try{
            $rec = Orm\Object::factory($this->getObjectName() , $id);
        }catch (\Exception $e){
            Model::factory($this->getObjectName())->logError('Invalid id requested: '.$id);
            $this->response->success();
        }

        $before = $rec->get('before');
        $after = $rec->get('after');

        if(empty($before) && empty($after)){
            $this->response->success();
        }
        $before = json_decode($before , true);
        $after = json_decode($after , true);

        $data = [];
        if(!empty($before)){
            foreach($before as $field=>$value){
                $data[$field]['id'] = $field;
                $data[$field]['before'] = $value;
            }
        }
        if(!empty($after)){
            foreach($after as $field=>$value){
                $data[$field]['id'] = $field;
                $data[$field]['after'] = $value;
            }
        }
        $this->response->success(array_values($data));
    }
}