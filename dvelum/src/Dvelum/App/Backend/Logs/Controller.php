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
use Dvelum\Orm\Record;
use Dvelum\Filter;
use Dvelum\Utils;
use Dvelum\Db\Select;
use \Exception;

class Controller extends Backend\Ui\Controller
{
    protected $canViewObjects = ['User'];

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
        $pager = $this->request->post('pager', 'array', []);
        $filter = $this->request->post('filter', 'array', []);

        $history = Model::factory('Historylog');

        if (isset($filter['date']) && !empty($filter['date'])) {
            $date = date('Y-m-d', strtotime($filter['date']));
            $filter['date'] = new Select\Filter(
                'date',
                [
                    $date . ' 00:00:00', $date . ' 23:59:59'
                ],
                Select\Filter::BETWEEN
            );
        }

        $fields = ['date', 'type', 'id', 'object', 'user_id', 'record_id'];
        $data = $history
            ->query()
            ->params($pager)
            ->filters($filter)
            ->fields($fields)->fetchAll();

        if (!empty($data)) {
            $users = Utils::fetchCol('user_id', $data);
            $users = Record::factory('User', $users);

            foreach ($data as &$v) {
                if (!empty($v['user_id']) && isset($users[$v['user_id']])) {
                    $v['user_name'] = $users[$v['user_id']]->getTitle();
                }
                if (!empty($v['object']) && Record\Config::configExists($v['object'])) {
                    $v['object_title'] = Record\Config::factory($v['object'])->getTitle();
                }
            }
            unset($v);
        }

        $this->response->success($data, [
            'count' => $history->query()->filters($filter)->getCount()
        ]);
    }

    /**
     * Get list of registered DB Objects
     */
    public function objectsListAction()
    {
        $manager = new Orm\Record\Manager();
        $list = $manager->getRegisteredObjects();
        $data = [];
        if(!empty($list)){
            foreach ($list as $object) {
                $data[] = ['id' => $object, 'title' => Record\Config::factory($object)->getTitle()];
            }
        }
        $this->response->success($data);
    }

    /**
     * Get changes list
     */
    public function changesListAction()
    {
        $filter = $this->request->post('filter', Filter::FILTER_ARRAY, false);

        if (empty($filter['id'])) {
            $this->response->success();
            return;
        }

        $id = intval($filter['id']);

        try {
            /**
             * @var Orm\RecordInterface $rec
             */
            $rec = Orm\Record::factory('Historylog', $id);
        } catch (Exception $e) {
            Model::factory('Historylog')->logError('Invalid id requested: ' . $id);
            $this->response->success();
            return;
        }

        $before = $rec->get('before');
        $after = $rec->get('after');

        if (empty($before) && empty($after)) {
            $this->response->success();
            return;
        }

        $before = json_decode($before, true);
        $after = json_decode($after, true);

        $data = [];
        if (!empty($before)) {
            foreach ($before as $field => $value) {
                $data[$field]['id'] = $field;
                $data[$field]['before'] = $value;
            }
        }
        if (!empty($after)) {
            foreach ($after as $field => $value) {
                $data[$field]['id'] = $field;
                $data[$field]['after'] = $value;
            }
        }
        $this->response->success(array_values($data));
    }
}