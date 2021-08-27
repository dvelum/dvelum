<?php

/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
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

namespace Dvelum\App\Model;

use Dvelum\Orm;
use Dvelum\Orm\Model;
use Exception;

/**
 * History logger
 * @author Kirill Egorov 2011
 */
class Historylog extends Model
{
    /**
     * Action types
     * @var array
     */
    public static $actions = array(
        1 => 'Delete',
        2 => 'Create',
        3 => 'Update',
        4 => 'Publish',
        5 => 'Sort',
        6 => 'Unpublish',
        7 => 'New Version'
    );

    public const Delete = 1;
    public const Create = 2;
    public const Update = 3;
    public const Publish = 4;
    public const Sort = 5;
    public const Unpublish = 6;
    public const NewVersion = 7;

    /**
     * Log action. Fill history table
     * @param integer $user_id
     * @param integer $record_id
     * @param integer $type
     * @param string $object
     * @return bool
     * @throws Exception
     */
    public function log($user_id, $record_id, $type, $object): bool
    {
        if (!is_integer($type)) {
            throw new Exception('History::log Invalid type');
        }

        $obj = Orm\Record::factory($this->name);
        $obj->setValues(array(
                            'user_id' => intval($user_id),
                            'record_id' => intval($record_id),
                            'type' => intval($type),
                            'date' => date('Y-m-d H:i:s'),
                            'object' => $object
                        ));
        return (bool)$obj->save(false);
    }

    /**
     * Get log for the  data item
     * @param string $table_name
     * @param integer $record_id
     * @param integer $start - optional
     * @param integer $limit - optional
     * @return array
     */
    public function getLog($table_name, $record_id, $start = 0, $limit = 25): array
    {
        $db = $this->getDbConnection();
        $sql = $db->select()
            ->from(array('l' => $this->table()), ['type', 'date'])
            ->where('l.table_name = ?', $table_name)
            ->where('l.record_id = ?', $record_id)
            ->joinLeft(
                array('u' => Model::factory('User')->table()),
                ' l.user_id = u.id',
                array('user' => 'u.name)')
            )
            ->order('l.date DESC')
            ->limit($limit, $start);

        $data = $db->fetchAll($sql);

        if (!empty($data)) {
            foreach ($data as &$v) {
                if (isset(self::$actions[$v['type']])) {
                    $v['type'] = self::$actions[$v['type']];
                } else {
                    $v['type'] = 'unknown';
                }
            }
            return $data;
        } else {
            return [];
        }
    }

    /**
     * Save object state
     * @param integer $operation
     * @param string $objectName
     * @param integer $objectId
     * @param integer $userId
     * @param string $date
     * @param string $before
     * @param string $after
     * @return integer | false
     */
    public function saveState($operation, $objectName, $objectId, $userId, $date, $before = null, $after = null)
    {
        // проверяем, существует ли такой тип объектов
        if (!Orm\Record\Config::configExists($objectName)) {
            $this->logError('Invalid object name "' . $objectName . '"');
            return false;
        }

        try {
            $o = Orm\Record::factory('Historylog');
            $o->setValues(array(
                              'type' => $operation,
                              'object' => $objectName,
                              'record_id' => $objectId,
                              'user_id' => $userId,
                              'date' => $date,
                              'before' => $before,
                              'after' => $after
                          ));

            $id = $o->save(false);
            if (!$id) {
                throw new Exception('Cannot save object state ' . $objectName . '::' . $objectId);
            }

            return $id;
        } catch (Exception $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
}
