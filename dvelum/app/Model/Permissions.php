<?php
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
            throw new Exception('Need user id');

		 $cache = self::$_defaults['dataCache'];

        /*
         * Check if cache exists
         */
    	if($cache && $data = $cache->load('user_permissions' . $userId))
    		return $data;

    	$data = array();
    	/*
    	 * Load permissions for group
    	 */
    	if($groupId){

    	 	$sql = $this->_dbSlave->select()
            				 ->from($this->table() , self::$_fields)
            				 ->where('`group_id` = '.intval($groupId))
    	 	                 ->where('`user_id` IS NULL');
            $groupRights = $this->_dbSlave->fetchAll($sql);

            if(!empty($groupRights))
            	$data =  Utils::rekey('module', $groupRights);
    	}
         /*
          * Load permissions for user
          */
         $sql = $this->_dbSlave	->select()
				            ->from($this->table() , self::$_fields)
				            ->where('`user_id` = '.intval($userId))
                            ->where('`group_id` IS NULL');

         $userRights = $this->_dbSlave->fetchAll($sql);

         /*
          * Replace group permissions by permissions redefined for concrete user
          */
         if(!empty($userRights))
             $data = array_merge($data , Utils::rekey('module', $userRights));

         /*
          * Cache info
          */
         if($cache)
         	$cache->save($data , 'user_permissions' . $userId);

         return $data;
    }
    /**
     * Remove permissions records for
     * undefined modules
     */
    public function cleanUp()
    {
    	$modules = Config::factory(Config::File_Array , Registry::get('main' , 'config')->get('backend_modules'));

    	$sql = $this->_dbSlave->select()
    		  ->from($this->table() , array('module'))
    		  ->distinct();

    	$data = $this->_dbSlave->fetchCol($sql);

    	if(!empty($data))
    		foreach ($data as $name)
    			if(!$modules->offsetExists($name))
    				$this->_db->delete($this->table(),'module='.$this->_db->quote($name).'');
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
    	if($this->_cache && $data = $this->_cache->load('group_permissions' . $groupId))
    		return $data;

    	$sql = $this->_dbSlave	->select()
            				->from($this->table() , self::$_fields)
            				->where('`group_id` = '.intval($groupId))
    	                    ->where('`user_id` IS NULL');

        $data = $this->_dbSlave->fetchAll($sql);

        if(!empty($data))
            $data =  Utils::rekey('module', $data);

         /*
          * Cache info
          */
         if($this->_cache)
			 $this->_cache->save($data , 'group_permissions' . $groupId);

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

    	$groupPermissions = $this->getList(false, array('group_id'=>$groupId,'user_id'=>0));
    	$sorted = Utils::rekey('module', $groupPermissions);

    	$modulesToRemove = array();

    	if(!empty($sorted))
    		$modulesToRemove = array_diff(array_keys($sorted), Utils::fetchCol('module', $data));

    	if(!empty($modulesToRemove))
    		$this->_db->delete($this->table(),'`module` IN (\''.implode("','", $modulesToRemove).'\') AND `group_id`='.intval($groupId));

        $errors = false;

    	foreach ($data as $values)
    	{
    		if(empty($values))
    			return false;
    		/**
    		 * Check if all needed fields are present
    		 */
    		$diff = array_diff(self::$_fields, array_keys($values));

    		if(!empty($diff))
    			continue;

    		try{

    			if(isset($sorted[$values['module']]))
    			{
    					$obj = new Db_Object($this->_name , $sorted[$values['module']][$this->_objectConfig->getPrimaryKey()]);
    					$obj->setValues(array(
    							'view'=>(boolean)$values['view'],
    							'edit'=>(boolean)$values['edit'],
    							'delete'=>(boolean)$values['delete'],
    							'publish'=>(boolean)$values['publish'],
								'only_own'=>(boolean)$values['only_own'],
    					));
    			}
    			else
    			{
                    	$obj = new Db_Object($this->_name);
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
    			}

			    if(!$obj->save())
			    	$errors = true;

    		}catch (Exception $e){
    			$errors = true;
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
    		$groupObj = new Db_Object('permissions',$objectId);
    	}catch(Exception $e){
    		$groupObj = new Db_Object('permissions');
    	}

    	$groupObj->module=$module;
		$groupObj->view=$view;
		$groupObj->edit=$edit;
		$groupObj->delete=$delete;
		$groupObj->publish=$publish;
		$groupObj->group_id=$group;
    	$groupObj->user_id=0;

    	return $groupObj->save(true);
    }

    /**
     * Remove group permissions
     * @param integer $groupId
     * @return boolean
     */
    public function removeGroup($groupId)
    {
    	$select = $this->_dbSlave->select()
    						->from($this->table(), 'id')
    						->where('`user_id`  IS NULL')
    						->where('`group_id` = ?', $groupId);

    	$groupIds = $this->_dbSlave->fetchCol($select);

		$store = $this->_getObjectsStore();

        if(!empty($groupIds) && !$store->deleteObjects($this->_name, $groupIds))
            return false;

    	/**
		 * Invalidate Cache
    	 */
    	if($this->_cache)
			$this->_cache->remove('group_permissions' . $groupId);

    	return  true;
    }
}