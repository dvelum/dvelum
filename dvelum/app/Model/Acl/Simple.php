<?php

use Dvelum\Orm;
use Dvelum\Orm\Model;

class Model_Acl_Simple extends Model
{
    static protected $fields = array('view','create','edit','delete','object','publish');

    /**
     * Get object permissions for user
     * @param integer $userId
     * @param integer $groupId
     * @throws Exception
     * @return array
     */
    public function getPermissions($userId , $groupId)
    {
        if(empty($userId))
            throw new Exception('Need user id');

        $cache = $this->cache;

        /*
         * Check if cache exists
         */
        if($cache && $data = $cache->load('object_permissions_' . $userId))
            return $data;

        $data = array();
        /*
         * Load permissions for group
        */
        if($groupId){

            $sql = $this->dbSlave->select()
                ->from($this->table() , self::$fields)
                ->where('`group_id` = '.intval($groupId))
                ->where('`user_id` IS NULL');

            $groupRights = $this->dbSlave->fetchAll($sql);

            if(!empty($groupRights))
                $data =  Utils::rekey('object', $groupRights);
        }
        /*
         * Load permissions for user
         */
        $sql = $this->dbSlave->select()
            ->from($this->table() , self::$fields)
            ->where('`user_id` = '.intval($userId))
            ->where('`group_id` IS NULL');

        $userRights = $this->dbSlave->fetchAll($sql);

        /*
         * Replace group permissions by permissions redefined for concrete user
         */
        if(!empty($userRights))
            $data = array_merge($data , Utils::rekey('object', $userRights));

        /*
         * Cache info
        */
        if($cache)
            $cache->save($data , 'object_permissions' . $userId);

        return $data;
    }

    /**
     * Get permissions for user group
     * Return permissions list indexed by module id
     * @return array
     */
    public function getGroupPermissions($groupId)
    {
        $data = [];
        $cache = $this->cache;
        /*
         * Check if cache exists
         */
        if($cache && $data = $cache->load('acl_simple_group_permissions' . $groupId))
            return $data;

        $sql = $this->dbSlave->select()
            ->from($this->table() , self::$fields)
            ->where('`group_id` = '.intval($groupId))
            ->where('`user_id` IS NULL');

        $data = $this->dbSlave->fetchAll($sql);

        if(!empty($data))
            $data =  Utils::rekey('object', $data);

        /*
         * Cache info
        */
        if($cache)
            $cache->save($data , 'acl_simple_group_permissions' . $groupId);

        return $data;
    }

    /**
     * Update group permissions
     * @param integer $groupId
     * @param array $data - permissions like array(
     * array(
     * 			'object'=>'object',
     * 			'view'=>true,
     *             'create'=>false,
     * 			'edit'=>false,
     * 			'delete'=>false,
     * 			'publish'=>false
     * 	),
     * 	...
     * )
     * @return boolean
     */
    public function updateGroupPermissions($groupId , array $data)
    {
        $modulesToRemove = Utils::fetchCol('object', $data);
        if(!empty($modulesToRemove))
        {
            try{
                $this->db->delete($this->table(),'`object` IN (\''.implode("','", $modulesToRemove).'\') AND `group_id`='.intval($groupId));
            }catch (Exception $e){
                $this->logError($e->getMessage());
                return false;
            }
        }

        $errors = false;

        foreach ($data as $values)
        {
            /**
             * Check if all needed fields are present
             */
            $diff = array_diff(self::$fields, array_keys($values));

            if(!empty($diff))
                continue;

            try{
                $obj = Orm\Record::factory($this->name);
                $obj->setValues(array(
                    'view'=>(boolean)$values['view'],
                    'create'=>(boolean)$values['create'],
                    'edit'=>(boolean)$values['edit'],
                    'delete'=>(boolean)$values['delete'],
                    'publish'=>(boolean)$values['publish'],
                    'object'=>$values['object'],
                    'group_id'=>$groupId,
                    'user_id'=>null
                ));

                if(!$obj->save()){
                    $errors = true;
                }

            }catch (Exception $e){
                $this->logError($e->getMessage());
                $errors = true;
            }
        }

        if($errors)
            return false;
        else
            return true;
    }
}