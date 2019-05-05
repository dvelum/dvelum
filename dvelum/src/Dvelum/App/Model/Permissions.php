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

use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Utils;
use \Exception;

class Permissions extends Model
{
    static protected $fields = ['view', 'edit', 'delete', 'publish', 'module', 'only_own'];

    /**
     * Get modules permissions for user
     * @param integer $userId
     * @param integer $groupId
     * @throws Exception
     * @return array
     */
    public function getPermissions($userId, $groupId)
    {
        if (empty($userId))
            throw new Exception('Need user id');

        $data = [];
        /*
         * Load permissions for group
         */
        if ($groupId) {

            $sql = $this->dbSlave->select()
                ->from($this->table(), self::$fields)
                ->where('`group_id` = ' . intval($groupId))
                ->where('`user_id` IS NULL');
            $groupRights = $this->dbSlave->fetchAll($sql);

            if (!empty($groupRights))
                $data = Utils::rekey('module', $groupRights);
        }
        /*
         * Load permissions for user
         */
        $sql = $this->dbSlave->select()
            ->from($this->table(), self::$fields)
            ->where('`user_id` = ' . intval($userId))
            ->where('`group_id` IS NULL');

        $userRights = $this->dbSlave->fetchAll($sql);

        /*
         * Replace group permissions by permissions redefined for concrete user
         * (additional approved rights)
         */
        if (!empty($userRights)) {
            foreach ($userRights as $v) {
                foreach (self::$fields as $field) {
                    if ($field == 'module')
                        continue;
                    if (isset($v[$field])) {
                        if ($v[$field]) {
                            $data[$v['module']][$field] = true;
                        } elseif (!isset($data[$v['module']][$field])) {
                            $data[$v['module']][$field] = false;
                        }
                    }
                }
            }
        }

        $data[] = [
            'module' => 'index',
            'view' => true
        ];

        return $data;
    }

    /**
     * Get records from permissions table
     * for user and group
     * @param $userId
     * @param $groupId
     * @return array
     */
    public function getRecords($userId, $groupId)
    {
        $sql = $this->dbSlave->select()->from($this->table())->where('user_id =?', $userId)->orWhere('group_id =?', $groupId);
        try {
            return $this->dbSlave->fetchAll($sql);
        } catch (Exception $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Remove permissions records for
     * undefined modules
     */
    public function cleanUp()
    {
        $modules = Config::factory(Config\Factory::File_Array, Config::storage()->get('main.php')->get('backend_modules'));

        $sql = $this->dbSlave->select()
            ->from($this->table(), array('module'))
            ->distinct();

        $data = $this->dbSlave->fetchCol($sql);

        if (!empty($data))
            foreach ($data as $name)
                if (!$modules->offsetExists($name))
                    $this->db->delete($this->table(), 'module=' . $this->db->quote($name) . '');
    }

    /**
     * Get permissions for user group
     * Return permissions list indexed by module id
     * @return array
     */
    public function getGroupPermissions($groupId)
    {
        $data = [];
        /*
         * Check if cache exists
         */
        if ($this->cache && $data = $this->cache->load('group_permissions' . $groupId))
            return $data;

        $sql = $this->dbSlave->select()
            ->from($this->table(), self::$fields)
            ->where('`group_id` = ' . intval($groupId))
            ->where('`user_id` IS NULL');

        $data = $this->dbSlave->fetchAll($sql);

        if (!empty($data))
            $data = Utils::rekey('module', $data);

        /*
         * Cache info
         */
        if ($this->cache)
            $this->cache->save($data, 'group_permissions' . $groupId);

        return $data;
    }

    /**
     * Update group permissions
     * @param integer $groupId
     * @param array $data - permissions like array(
     *                                                    array(
     *                                                        'module'=>'module',
     *                                                        'view'=>true,
     *                                                        'edit'=>false,
     *                                                        'delete'=>false,
     *                                                        'publish'=>false
     *                                                    ),
     *                                                    ...
     *                                                )
     * @return bool
     */
    public function updateGroupPermissions($groupId, array $data) : bool
    {
        $modulesToRemove = Utils::fetchCol('module', $data);
        if (!empty($modulesToRemove)) {
            try {
                $this->db->delete($this->table(), '`module` IN (\'' . implode("','", $modulesToRemove) . '\') AND `group_id`=' . intval($groupId));
            } catch (Exception $e) {
                $this->logError($e->getMessage());
                return false;
            }
        }

        $errors = false;
        foreach ($data as $values) {
            /**
             * Check if all needed fields are present
             */
            $diff = array_diff(self::$fields, array_keys($values));

            if (!empty($diff))
                continue;

            try {
                $obj = Orm\Record::factory($this->name);
                $obj->setValues(array(
                    'view' => (boolean)$values['view'],
                    'edit' => (boolean)$values['edit'],
                    'delete' => (boolean)$values['delete'],
                    'publish' => (boolean)$values['publish'],
                    'only_own' => (boolean)$values['only_own'],
                    'module' => $values['module'],
                    'group_id' => $groupId,
                    'user_id' => null
                ));

                if (!$obj->save()) {
                    $errors = true;
                }

            } catch (Exception $e) {
                $errors = true;
                $this->logError($e->getMessage());
            }
        }

        if ($errors)
            return false;

        if ($this->cache){
            $this->cache->remove('group_permissions' . $groupId);
        }

        return true;
    }

    /**
     * Update group permissions
     * @param integer $userId
     * @param array $data - permissions like array(
     *                                                    array(
     *                                                        'module'=>'module',
     *                                                        'view'=>true,
     *                                                        'edit'=>false,
     *                                                        'delete'=>false,
     *                                                        'publish'=>false
     *                                                    ),
     *                                                    ...
     *                                                )
     * @return bool
     */
    public function updateUserPermissions($userId, $data) : bool
    {
        $modulesToRemove = Utils::fetchCol('module', $data);
        if (!empty($modulesToRemove)) {
            try {
                $this->db->delete($this->table(), '`module` IN (\'' . implode("','", $modulesToRemove) . '\') AND `user_id`=' . intval($userId));
            } catch (Exception $e) {
                $this->logError($e->getMessage());
                return false;
            }
        }
        $userInfo = Model::factory('User')->getCachedItem($userId);
        $groupPermissions = [];

        if ($userInfo['group_id']) {
            $sql = $this->dbSlave->select()
                ->from($this->table(), self::$fields)
                ->where('`group_id` = ' . intval($userInfo['group_id']))
                ->where('`user_id` IS NULL');

            $groupPermissions = $this->dbSlave->fetchAll($sql);
            if (!empty($groupPermissions)) {
                $groupPermissions = Utils::rekey('module', $groupPermissions);
            }
        }

        $errors = false;
        $fields = ['view', 'edit', 'delete', 'publish', 'only_own'];
        foreach ($data as $values) {
            /**
             * Check if all needed fields are present
             */
            $diff = array_diff(self::$fields, array_keys($values));

            if (!empty($diff))
                continue;

            try {
                $needUpdate = false;

                if (isset($groupPermissions[$values['module']])) {
                    foreach ($fields as $field) {
                        if ((boolean)$groupPermissions[$values['module']][$field] !== (boolean)$values[$field]) {
                            $needUpdate = true;
                        }
                    }
                } else {
                    $needUpdate = true;
                }

                if (!$needUpdate) {
                    continue;
                }

                $obj = Orm\Record::factory($this->name);
                $obj->setValues(array(
                    'view' => (boolean)$values['view'],
                    'edit' => (boolean)$values['edit'],
                    'delete' => (boolean)$values['delete'],
                    'publish' => (boolean)$values['publish'],
                    'only_own' => (boolean)$values['only_own'],
                    'module' => $values['module'],
                    'group_id' => null,
                    'user_id' => $userId
                ));

                if (!$obj->save()) {
                    $errors = true;
                }

            } catch (Exception $e) {
                $errors = true;
                $this->logError($e->getMessage());
            }
        }

        if ($errors)
            return false;
        else
            return true;
    }

    /**
     * Set group permissions
     * @param integer $group
     * @param string $module
     * @param bool $view
     * @param bool $edit
     * @param bool $delete
     * @param bool $publish
     * @return bool
     * @throws \Exception
     */
    public function setGroupPermissions($group, $module, $view, $edit, $delete, $publish) : bool
    {
        $data = $this->query()
            ->filters([
                'group_id' => $group,
                'user_id' => null,
                'module' => $module
            ])
            ->fields(['id'])
            ->fetchAll();

        $objectId = false;

        if (!empty($data))
            $objectId = $data[0]['id'];

        try {
            $groupObj = Orm\Record::factory('permissions', $objectId);
        } catch (Exception $e) {
            $groupObj = Orm\Record::factory('permissions');
        }

        $groupObj->module = $module;
        $groupObj->view = $view;
        $groupObj->edit = $edit;
        $groupObj->delete = $delete;
        $groupObj->publish = $publish;
        $groupObj->group_id = $group;
        $groupObj->user_id = null;

        return $groupObj->save(true);
    }

    /**
     * Remove group permissions
     * @param integer $groupId
     * @return bool
     */
    public function removeGroup($groupId) : bool
    {
        $select = $this->dbSlave->select()
            ->from($this->table(), 'id')
            ->where('`user_id`  IS NULL')
            ->where('`group_id` = ?', $groupId);

        $groupIds = $this->dbSlave->fetchCol($select);

        $store = $this->getStore();

        if (!empty($groupIds) && !$store->deleteObjects($this->name, $groupIds))
            return false;

        /**
         * Invalidate Cache
         */
        if ($this->cache)
            $this->cache->remove('group_permissions' . $groupId);

        return true;
    }
}