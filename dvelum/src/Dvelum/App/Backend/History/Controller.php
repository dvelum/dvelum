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

namespace Dvelum\App\Backend\History;

use Dvelum\App;
use Dvelum\Orm;
use Dvelum\Orm\Model;

class Controller extends App\Backend\Api\Controller
{
    public function getModule(): string
    {
        return '\\Dvelum\\App\\Backend\\History\\Controller';
    }

    public function getObjectName(): string
    {
        return '';
    }

    /**
     * Check view permissions
     * @return bool
     */
    protected function checkCanView() : bool
    {
        return true;
    }

    /**
     * Get object history
     */
    public function listAction()
    {
        $object = $this->request->post('object', 'string' , false);

        if(!$object){
            $this->response->success([]);
            return;
        }

        $pager = $this->request->post('pager', 'array', []);
        $filter = $this->request->post('filter', 'array', []);

        if(!isset($filter['record_id']) || empty($filter['record_id'])){
            $this->response->success([]);
            return;
        }

        try{
            /**
             * @var Orm\RecordInterface
             */
            $object = Orm\Record::factory($object);
        }catch (\Exception $e){
            $this->response->success([]);
            return;
        }

        $filter['object'] = $object->getName();

        $history = Model::factory('Historylog');

        $data = $history->query()
            ->filters($filter)
            ->params($pager)
            ->fields(['date','type','id'])
            ->fetchAll();

        $objectConfig = Orm\Record\Config::factory('Historylog');

        $this->addLinkedInfo($objectConfig,['user_name'=>'user_id'], $data, $objectConfig->getPrimaryKey());

        if(!empty($data)) {
            foreach ($data as &$v) {
                if(isset(App\Model\Historylog::$actions[$v['type']])){
                    $v['type'] = App\Model\Historylog::$actions[$v['type']];
                }
            }unset($v);
        }
        $this->response->success($data , ['count'=>$history->query()->filters($filter)->getCount()]);
    }
}