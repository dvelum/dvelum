<?php

use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Orm\Model;

class Model_Permissions extends Model
{
 	static protected $_fields = array('view','edit','delete','publish','module','only_own');

	/**
     * Get modules permissions for user
     * @param integer $userId
     * @param integer $groupId
     * @throws Exception
     * @return array
     */
    public function getPermissions($userId , $groupId)
    {
         if(empty($userId))
            throw new \Exception('Need user id');

    	$data = array();
    	/*
    	 * Load permissions for group
    	 */
    	if($groupId){

    	 	$sql = $this->dbSlave->select()
            				 ->from($this->table() , self::$_fields)
            				 ->where('`group_id` = '.intval($groupId))
    	 	                 ->where('`user_id` IS NULL');
            $groupRights = $this->dbSlave->fetchAll($sql);

            if(!empty($groupRights))
            	$data =  Utils::rekey('module', $groupRights);
    	}
         /*
          * Load permissions for user
          */
         $sql = $this->dbSlave	->select()
				            ->from($this->table() , self::$_fields)
				            ->where('`user_id` = '.intval($userId))
                            ->where('`group_id` IS NULL');

         $userRights = $this->dbSlave->fetchAll($sql);

         /*
          * Replace group permissions by permissions redefined for concrete user
          * (additional approved rights)
          */
         if(!empty($userRights)){
            foreach ($userRights as $k=>$v){
                foreach (self::$_fields as $field){
                    if($field == 'module')
                        continue;
                    if(isset($v[$field])) {
                        if($v[$field]){
                            $data[$v['module']][$field] = true;
                        }elseif(!isset($data[$v['module']][$field])){
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
    public function getRecords($userId , $groupId)
    {
        $sql = $this->dbSlave->select()->from($this->table())->where('user_id =?', $userId)->orWhere('group_id =?',$groupId);
        try{
            return $this->dbSlave->fetchAll($sql);
        }catch (Exception $e){
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
    	$modules = Config::factory(Config\Factory::File_Array , Config::storage()->get('main.php')->get('backend_modules'));

    	$sql = $this->dbSlave->select()
    		  ->from($this->table() , array('module'))
    		  ->distinct();

    	$data = $this->dbSlave->fetchCol($sql);

    	if(!empty($data))
    		foreach ($data as $name)
    			if(!$modules->offsetExists($name))
    				$this->db->delete($this->table(),'module='.$this->_db->quote($name).'');
    }
    /**
     * Get permissions for user group
     * Return permissions list indexed by module id
     * @return array
     */
    public function getGroupPermissions($groupId)
    {
    	$data = array();
		/*
         * Check if cache exists
         */
    	if($this->cache && $data = $this->cache->load('group_permissions' . $groupId))
    		return $data;

    	$sql = $this->dbSlave->select()
            				->from($this->table() , self::$_fields)
            				->where('`group_id` = '.intval($groupId))
    	                    ->where('`user_id` IS NULL');

        $data = $this->dbSlave->fetchAll($sql);

        if(!empty($data))
            $data = Utils::rekey('module', $data);

         /*
          * Cache info
          */
         if($this->cache)
			 $this->cache->save($data , 'group_permissions' . $groupId);

		return $data;
    }
    /**
     * Update group permissions
     * @param integer $groupId
     * @param array $data - permissions like array(
     * 													array(
     * 														'module'=>'module',
     * 														'view'=>true,
     * 														'edit'=>false,
     * 														'delete'=>false,
     * 														'publish'=>false
     * 													),
     * 													...
     * 												)
     * @return boolean
     */
    public function updateGroupPermissions($groupId , array $data)
    {
    	$modulesToRemove = Utils::fetchCol('module', $data);
    	if(!empty($modulesToRemove))
    	{
    	    try{
                $this->db->delete($this->table(),'`module` IN (\''.implode("','", $modulesToRemove).'\') AND `group_id`='.intval($groupId));
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
    		$diff = array_diff(self::$_fields, array_keys($values));

    		if(!empty($diff))
    			continue;

    		try
            {
                $obj = Orm\Record::factory($this->name);
                $obj->setValues(array(
                        'view'=>(boolean)$values['view'],
                        'edit'=>(boolean)$values['edit'],
                        'delete'=>(boolean)$values['delete'],
                        'publish'=>(boolean)$values['publish'],
                        'only_own'=>(boolean)$values['only_own'],
                        'module'=>$values['module'],
                        'group_id'=>$groupId,
                        'user_id'=>null
                ));

			    if(!$obj->save()){
                    $errors = true;
                }

    		}catch (Exception $e){
    			$errors = true;
                $this->logError($e->getMessage());
    		}
    	}

    	if($errors)
    		return false;
    	else
    	   return true;
    }
    /**
     * Update group permissions
     * @param integer $userId
     * @param array $data - permissions like array(
     * 													array(
     * 														'module'=>'module',
     * 														'view'=>true,
     * 														'edit'=>false,
     * 														'delete'=>false,
     * 														'publish'=>false
     * 													),
     * 													...
     * 												)
     * @return boolean
     */
    public function updateUserPermissions($userId, $data)
    {
        $modulesToRemove = Utils::fetchCol('module', $data);
        if(!empty($modulesToRemove))
        {
            try{
                $this->db->delete($this->table(),'`module` IN (\''.implode("','", $modulesToRemove).'\') AND `user_id`='.intval($userId));
            }catch (Exception $e){
                $this->logError($e->getMessage());
                return false;
            }
        }
        $userInfo = Model::factory('User')->getCachedItem($userId);
        $groupPermissions = [];

        if($userInfo['group_id']){
            $sql = $this->dbSlave	->select()
                ->from($this->table() , self::$_fields)
                ->where('`group_id` = '.intval($userInfo['group_id']))
                ->where('`user_id` IS NULL');

            $groupPermissions = $this->dbSlave->fetchAll($sql);
            if(!empty($groupPermissions)){
                $groupPermissions = Utils::rekey('module', $groupPermissions);
            }
        }

        $errors = false;
        $fields = ['view','edit','delete','publish','only_own'];
        foreach ($data as $values)
        {
            /**
             * Check if all needed fields are present
             */
            $diff = array_diff(self::$_fields, array_keys($values));

            if(!empty($diff))
                continue;

            try
            {
                $needUpdate = false;

                if(isset($groupPermissions[$values['module']])){
                    foreach ($fields as $field){
                        if((boolean)$groupPermissions[$values['module']][$field] !== (boolean) $values[$field]){
                            $needUpdate = true;
                        }
                    }
                }else{
                    $needUpdate = true;
                }

                if(!$needUpdate){
                    continue;
                }

                $obj = Orm\Record::factory($this->name);
                $obj->setValues(array(
                    'view'=>(boolean)$values['view'],
                    'edit'=>(boolean)$values['edit'],
                    'delete'=>(boolean)$values['delete'],
                    'publish'=>(boolean)$values['publish'],
                    'only_own'=>(boolean)$values['only_own'],
                    'module'=>$values['module'],
                    'group_id'=>null,
                    'user_id'=>$userId
                ));

                if(!$obj->save()){
                    $errors = true;
                }

            }catch (Exception $e){
                $errors = true;
                $this->logError($e->getMessage());
            }
        }

        if($errors)
            return false;
        else
            return true;
    }
    /**
     * Set group permissions
     * @param integer $group
     * @param string $module
     * @param boolean $view
     * @param boolean $edit
     * @param boolean $delete
     * @param boolean $publish
     * @return boolean
     */
    public function setGroupPermissions($group , $module , $view, $edit , $delete , $publish)
    {
    	$data = $this->getList(
    			false,
    			array(
    				'group_id'=>$group,
    				'user_id'=>null,
    				'module'=>$module
    			),
    			array('id'),
    			false
    	);

    	$objectId = false;

    	if(!empty($data))
    		$objectId = $data[0]['id'];

        try{
    		$groupObj = Orm\Record::factory('permissions',$objectId);
    	}catch(Exception $e){
    		$groupObj = Orm\Record::factory('permissions');
    	}

    	$groupObj->module=$module;
		$groupObj->view=$view;
		$groupObj->edit=$edit;
		$groupObj->delete=$delete;
		$groupObj->publish=$publish;
		$groupObj->group_id=$group;
    	$groupObj->user_id=null;

    	return $groupObj->save(true);
    }

    /**
     * Remove group permissions
     * @param integer $groupId
     * @return boolean
     */
    public function removeGroup($groupId)
    {
    	$select = $this->dbSlave->select()
    						->from($this->table(), 'id')
    						->where('`user_id`  IS NULL')
    						->where('`group_id` = ?', $groupId);

    	$groupIds = $this->dbSlave->fetchCol($select);

		$store = $this->getStore();

        if(!empty($groupIds) && !$store->deleteObjects($this->name, $groupIds))
            return false;

    	/**
		 * Invalidate Cache
    	 */
    	if($this->cache)
			$this->cache->remove('group_permissions' . $groupId);

    	return  true;
    }
}