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

namespace Dvelum\App\Backend\Vcs;

use Dvelum\App;
use Dvelum\Orm;
use Dvelum\Orm\Model;

class Controller extends App\Backend\Api\Controller
{
    public function getModule(): string
    {
        return '\\Dvelum\\App\\Backend\\Vcs\\Controller';
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

    public function listAction()
    {
        $object = $this->request->post('object', 'string', false);

        if (!$object) {
           $this->response->success([]);
           return;
        }

        $pager = $this->request->post('pager', 'array', null);
        $filter = $this->request->post('filter', 'array', null);

        $filter['object_name'] = $object;

        $model = Model::factory('Vc');

        $data = $model->query()->params($pager)->filters($filter)->fields(['version', 'date', 'id', 'record_id', 'user_id'])->fetchAll();

        $objectConfig = Orm\Record\Config::factory('Vc');
        $this->addLinkedInfo($objectConfig, ['user_name' => 'user_id'], $data, $objectConfig->getPrimaryKey());


       $this->response->success($data,['count'=>$model->query()->filters($filter)->getCount()]);
    }
}