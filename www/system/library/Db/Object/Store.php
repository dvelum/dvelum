<?php
/**
 * Storage adapter for Db_Object
 * @package Db
 * @subpackage Db_Object
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2012  Kirill A Egorov,
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 * @uses Model_Links
 */
class Db_Object_Store
{
    /**
     * @var Db_Object_Event_Manager (optional)
     */
    protected $_eventManager = null;
    protected $_log = false;
    protected $_linksObject = 'Links';
    protected $_historyObject = 'Historylog';
    protected $_versionObject = 'Vc';

    /**
     * Set object name for storing relations
     * @param string $name
     */
    public function setLinksObjectName($name)
    {
    	$this->_linksObject = $name;
    }
    /**
     * Get links object name
     * @return string
     */
    public function getLinksObjectName()
    {
    	return $this->_linksObject;
    }
    /**
     * Get history object name
     * @return string
     */
    public function getHistoryObjectName()
    {
    	return $this->_historyObject;
    }
    /**
     * Get version object name
     * @return string
     */
    public function getVersionObjectName()
    {
    	return $this->_versionObject;
    }
    /**
     * Set log Adapter
     * @param Log $log
     */
    public function setLog(Log $log)
    {
    	$this->_log = $log;
    }
    /**
     * Set object name for storing history
     * @param string $name
     */
    public function setHistoryObject($name)
    {
    	$this->_historyObject = $name;
    }
    /**
     * Set object name for storing versions
     * @param string $name
     */
    public function setVersionObject($name)
    {
    	$this->_versionObject = $name;
    }
    /**
     * Set event nanager
     * @param Db_Object_Event_Manager $obj
     */
    public function setEventManager(Db_Object_Event_Manager $obj)
    {
    	$this->_eventManager = $obj;
    }

    protected function _getDbConnection(Db_Object $object)
    {
    	return Model::factory($object->getName())->getDbConnection();
    }
    /**
     * Update Db object
     * @param Db_Object $object
     * @param boolean $log - optional, log changes
     * @param boolean $transaction - optional, use transaction if available
     * @return boolean
     */
    public function update(Db_Object $object , $log = true ,$transaction = true)
    {
        if($object->getConfig()->isReadOnly())
        {
            if($this->_log)
                $this->_log->log('ORM :: cannot update readonly object '. $object->getConfig()->getName());

            return false;
        }

    	 /*
    	  * Check object id
    	  */
    	  if(!$object->getId())
            return false;

        /*
         * Check for updates
         */
        if(!$object->hasUpdates())
            return $object->getId();

        /*
         * Fire "BEFORE_UPDATE" Event if event manager exists
         */
        if($this->_eventManager)
            $this->_eventManager->fireEvent(Db_Object_Event_Manager::BEFORE_UPDATE, $object);

       /*
        * Validate unique values
        *
        $values = $object->validateUniqueValues();

        if(!empty($values))
        {
          if($this->_log)
          {
            $errors = array();
            foreach($values as $k => $v)
            {
              $errors[] = $k . ':' . $v;
            }
            $this->_log->log($object->getName() . '::update ' . implode(', ' , $errors));
          }
          return false;
        }
        */

	      /*
	       * Check if DB table support transactions
	       */
         $transact = $object->getConfig()->isTransact();
         /*
          * Get Database connector for object model;
          */
         $db = $this->_getDbConnection($object);

	     if($transact && $transaction)
	    	 $db->beginTransaction();

	     $success = $this->_updateOperation($object);

	     if(!$success)
	     {
	     	if($transact && $transaction)
	        	$db->rollBack();
	        return false;
	     }
	     else
	     {
	     	if($transact && $transaction)
        		$db->commit();
	     }

         /*
          * Save history if required
          * @todo удалить жесткую связанность
          */
	     if($log && $object->getConfig()->get('save_history'))
	     {
                Model::factory($this->_historyObject)->log(
                	User::getInstance()->id ,
                	$object->getId() ,
                	Model_Historylog::Update ,
                	$object->getTable()
                );
	     }

         /*
          * Fire "AFTER_UPDATE" Event if event manager exists
          */
         if($this->_eventManager)
            $this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_UPDATE, $object);

	     return $object->getId();
    }

    protected function _updateOperation(Db_Object $object)
    {
    	try{
    		$db = $this->_getDbConnection($object);
	        $db->update($object->getTable() , $object->serializeLinks($object->getUpdates()) , $db->quoteIdentifier($object->getConfig()->getPrimaryKey()).' = '.$object->getId());
	        $this->_updateLinks($object);
	        /*
	         * Fire "AFTER_UPDATE_BEFORE_COMMIT" Event if event manager exists
	         */
	        if($this->_eventManager)
	        	$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_UPDATE_BEFORE_COMMIT, $object);
	        $object->commitChanges();
	        return true;
    	}catch (Exception $e){
    		if($this->_log)
    			$this->_log->log($object->getName().'::_updateOperation '.$e->getMessage());
    		return false;
    	}
    }

    /**
     * Unpublish Db_Objects
     * @param Db_Object $object
     * @param boolean $log - optional, log changes
     * @param string $transaction
     */
    public function unpublish(Db_Object $object , $log , $transaction = true)
    {
    	if($object->getConfig()->isReadOnly())
    	{
    		if($this->_log)
    			$this->_log->log('ORM :: cannot unpublish readonly object '. $object->getConfig()->getName());

    		return false;
    	}

       /*
    	* Check object id
    	*/
    	if(!$object->getId())
    		return false;

    	if (!$object->getConfig()->isRevControl())
    	{
    		if($this->_log){
    			$this->_log->log($object->getName().'::unpublish Cannot unpublish object is not under version control');
    		}
    		return false;
    	}

       /*
        * Fire "BEFORE_UNPUBLISH" Event if event manager exists
    	*/
    	if($this->_eventManager)
    		$this->_eventManager->fireEvent(Db_Object_Event_Manager::BEFORE_UNPUBLISH, $object);

       /*
    	* Check if DB table support transactions
    	*/
    	$transact = $object->getConfig()->isTransact();
    	/*
    	 * Get Database connector for object model;
    	*/
    	$db = $this->_getDbConnection($object);

    	if($transact && $transaction)
    		$db->beginTransaction();

    	$success = $this->_updateOperation($object);

    	if(!$success)
    	{
    		if($transact && $transaction)
    			$db->rollBack();
    		return false;
    	}
    	else
    	{
    		if($transact && $transaction)
    			$db->commit();
    	}

    	/*
    	 * Save history if required
    	 * @todo удалить жесткую связанность
    	 */
    	if($log && $object->getConfig()->get('save_history'))
    	{
    		Model::factory($this->_historyObject)->log(
	    		User::getInstance()->getId() ,
	    		$object->getId() ,
	    		Model_Historylog::Unpublish ,
	    		$object->getTable()
    		);
    	}
    	/*
    	 * Fire "AFTER_UPDATE" Event if event manager exists
    	*/
    	if($this->_eventManager)
    		$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_UNPUBLISH, $object);

    	return true;
    }

   /**
    * Publish Db_Object
    * @param Db_Object $object
    * @param boolean $log - optional, log changes
    * @param string $transaction
    */
    public function publish(Db_Object $object  , $log , $transaction = true)
    {
    	if($object->getConfig()->isReadOnly())
    	{
    		if($this->_log)
    			$this->_log->log('ORM :: cannot publish readonly object '. $object->getConfig()->getName());

    		return false;
    	}
       /*
    	* Check object id
    	*/
    	if(!$object->getId())
    		return false;

    	if(!$object->getConfig()->isRevControl())
    	{
    		if($this->_log){
    			$this->_log->log($object->getName().'::publish Cannot publish object is not under version control');
    		}
    		return false;
    	}

    	/*
    	 * Fire "BEFORE_UNPUBLISH" Event if event manager exists
    	*/
    	if($this->_eventManager)
    		$this->_eventManager->fireEvent(Db_Object_Event_Manager::BEFORE_PUBLISH, $object);

    	/*
    	 * Check if DB table support transactions
    	*/
    	$transact = $object->getConfig()->isTransact();
    	/*
    	 * Get Database connector for object model;
    	*/
    	$db = $this->_getDbConnection($object);

    	if($transact && $transaction)
    		$db->beginTransaction();

    	$success = $this->_updateOperation($object);

    	if(!$success)
    	{
    		if($transact && $transaction)
    			$db->rollBack();
    		return false;
    	}
    	else
    	{
    		if($transact && $transaction)
    			$db->commit();
    	}

       /*
    	* Save history if required
    	* @todo удалить жесткую связанность
    	*/
    	if($log && $object->getConfig()->get('save_history'))
    	{
    		Model::factory($this->_historyObject)->log(
	    		User::getInstance()->getId() ,
	    		$object->getId() ,
	    		Model_Historylog::Publish ,
	    		$object->getTable()
    		);
    	}

    	/*
    	 * Fire "AFTER_UPDATE" Event if event manager exists
    	 */
    	if($this->_eventManager)
    		$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_PUBLISH, $object);

    	return true;
    }

    protected function _updateLinks(Db_Object $object)
    {
        $updates = $object->getUpdates();

        if(empty($updates))
        	return true;

        foreach ($updates as $k=>$v)
        {
        	$conf = $object->getConfig()->getFieldConfig($k);
            if($object->getConfig()->isMultiLink($k))
            {
            	if(!$this->_clearLinks($object, $k,$conf['link_config']['object']))
                    return false;

                if(!empty($v) && is_array($v))
                    if(!$this->_createLinks($object , $k,$conf['link_config']['object'] , $v))
                       return false;
            }
        }
        return true;
    }
    /**
     * Remove object multy links
     * @param Db_Object $object
     * @param string $objectField
     * @param string $targetObjectName
     */
    protected function _clearLinks(Db_Object $object ,$objectField , $targetObjectName)
    {
    	$linksObj  = new Db_Object($this->_linksObject);

    	$db = $this->_getDbConnection($linksObj);

        $where = 'src = '.$db->quote($object->getName()).'
        		AND
        		 src_id = '.intval($object->getId()).'
        		AND
        		 src_field = '.$db->quote($objectField).'
                AND
                 target = '.$db->quote($targetObjectName);


        try{
            $db->delete($linksObj->getTable() , $where);
            return true;
        } catch (Exception $e){
        	if($this->_log)
        		$this->_log->log($object->getName().'::_clearLinks '.$e->getMessage());
            return false;
        }
    }
    /**
     * Create links to the object
     * @param Db_Object $object
     * @param string $objectField
     * @param string $targetObjectName
     * @param array $links
     */
    protected function _createLinks(Db_Object $object, $objectField , $targetObjectName , array $links)
    {
        $order = 0;
        $links = array_keys($links);
        $linksObj  = new Db_Object($this->_linksObject);
        $db = $this->_getDbConnection($linksObj);

        foreach ($links as $k=>$v)
        {
            $data = array(
                'src'=>$object->getName(),
                'src_id'=>$object->getId(),
                'src_field'=>$objectField,
                'target'=>$targetObjectName,
                'target_id'=>$v,
                'order'=>$order
            );

            if(!$db->insert($linksObj->getTable(), $data))
                return false;

            $order++;
        }
        return true;
    }
    /**
     * Insert Db object
     * @param Db_Object $object
     * @param boolean $log - optional, log changes
     * @param boolean $transaction - optional , use transaction if available
     * @return integer -  inserted id
     */
    public function insert(Db_Object $object , $log = true , $transaction = true)
    {
        if($object->getConfig()->isReadOnly())
        {
            if($this->_log)
                $this->_log->log('ORM :: cannot insert readonly object '. $object->getConfig()->getName());

            return false;
        }

    	if($this->_eventManager)
            $this->_eventManager->fireEvent(Db_Object_Event_Manager::BEFORE_ADD, $object);
       /*
	    * Check if DB table support transactions
	    */
    	$transact = $object->getConfig()->isTransact();

    	$db = $this->_getDbConnection($object);

    	if($transact && $transaction)
    		$db->beginTransaction();

    	$success = $this->_insertOperation($object);

        if(!$success)
        {
        	if($transact && $transaction)
        		$db->rollBack();
        	return false;
        }
        else
        {
        	if($transact && $transaction)
        		$db->commit();
        }

        if($log &&  $object->getConfig()->get('save_history'))
        {
        	/**
        	 * @todo   убрать жесткую связанность
        	 */
            Model::factory($this->_historyObject)->log(
            	User::getInstance()->id ,
            	$object->getId() ,
            	Model_Historylog::Create ,
            	$object->getTable()
            );
        }

        if($this->_eventManager)
        	$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_ADD, $object);

        return $object->getId();
    }

    protected function _insertOperation(Db_Object $object)
    {
    	$updates =  $object->getUpdates();

        if(empty($updates))
            return false;
        /*
         * Validate unique values
         */
        $values = $object->validateUniqueValues();

        if(!empty($values))
        {
            if($this->_log)
            {
                $errors = array();
                foreach($values as $k => $v)
                {
                    $errors[] = $k . ':' . $v;
                }
                $this->_log->log($object->getName() . '::insert ' . implode(', ' , $errors));
            }
            return false;
        }

        $db = $this->_getDbConnection($object);

        $objectTable = $object->getTable();

    	if(!$db->insert($objectTable, $object->serializeLinks($updates)))
             return false;

        $id = $db->lastInsertId($objectTable , $object->getConfig()->getPrimaryKey());

        if(!$id)
           return false;

        $object->setId($id);

        if(!$this->_updateLinks($object))
           return false;

        $object->commitChanges();
        $object->setId($id);

	    return true;
    }

	/**
	 * Add new object version
	 * @param Db_Object $object
     * @param boolean $log - optional, log changes
     * @param boolean $transaction - optional , use transaction if available
	 * @return boolean|integer - vers number
	 */
    public function addVersion(Db_Object $object , $log = true , $useTransaction = true)
    {

    	if($object->getConfig()->isReadOnly())
    	{
    		if($this->_log)
    			$this->_log->log('ORM :: cannot addVersion for readonly object '. $object->getConfig()->getName());

    		return false;
    	}
    	/*
    	 * Check object id
    	*/
    	if(!$object->getId())
    		return false;

    	if(!$object->getConfig()->isRevControl())
    	{
    		if($this->_log)
    			$this->_log->log($object->getName().'::publish Cannot addVersion. Object is not under version control');

    		return false;
    	}

    	/*
    	 * Fire "BEFORE_ADD_VERSION" Event if event manager exists
    	*/
    	if($this->_eventManager)
    		$this->_eventManager->fireEvent(Db_Object_Event_Manager::BEFORE_ADD_VERSION, $object);

       /*
    	* Create new revision
    	*/
    	$versNum = Model::factory($this->_versionObject)->newVersion($object);
    	if(!$versNum)
    		return false;

    	try{
    		$oldObject = new Db_Object($object->getName() , $object->getId());
    		/**
    		 * Update object if not published
    		 */
    		if(!$oldObject->get('published')){
    			$data = $object->getData();

    			foreach($data as $k => $v)
    				if(!is_null($v))
    					$oldObject->set($k , $v);

    			if(!$oldObject->save(false , $useTransaction))
    				return false;
    		}
    	}catch(Exception $e){
    		return false;
    	}

    	/*
    	 * Save history if required
    	 * @todo удалить жесткую связанность
    	 */
    	if($log && $object->getConfig()->get('save_history'))
    	{
    		Model::factory($this->_historyObject)->log(
	    		User::getInstance()->getId() ,
	    		$object->getId() ,
	    		Model_Historylog::NewVersion ,
	    		$object->getTable()
    		);
    	}

    	/*
    	 * Fire "AFTER_ADD_VERSION" Event if event manager exists
    	*/
    	if($this->_eventManager)
    		$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_ADD_VERSION, $object);

    	return  $versNum;
    }

    /**
     * Delete Db object
     * @param Db_Object $object
     * @param boolean $log - optional, log changes
     * @param boolean $transaction - optional , use transaction if available
     * @return boolean
     */
    public function delete(Db_Object $object , $log = true ,$transaction = true)
    {

        if($object->getConfig()->isReadOnly())
        {
            if($this->_log)
                $this->_log->log('ORM :: cannot delete readonly object '. $object->getName());

            return false;
        }

        if(!$object->getId())
            return false;

        if($this->_eventManager)
        	$this->_eventManager->fireEvent(Db_Object_Event_Manager::BEFORE_DELETE, $object);

        $transact = $object->getConfig()->isTransact();

        $db = $this->_getDbConnection($object);

    	if($transact && $transaction)
    		$db->beginTransaction();

        Model::factory($this->_linksObject)->clearObjectLinks($object);

        if($db->delete($object->getTable(), $db->quoteIdentifier($object->getConfig()->getPrimaryKey()).' =' . $object->getId()))
        {
        	/**
        	 * @todo убрать жесткую связанность
        	 */
        	if($log && $object->getConfig()->hasHistory()){
             	Model::factory($this->_historyObject)->log(
             		User::getInstance()->id ,
             		$object->getId() ,
             		Model_Historylog::Delete ,
             		$object->getTable()
             	);
        	}
        	$success= true;
        } else{
            $success = false;
        }

        if($transact && $transaction)
        {
            if($success)
                $db->commit();
            else
                $db->rollBack();
        }

        if($this->_eventManager)
        	$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_DELETE, $object);

        return $success;
    }
    /**
     * Delete Db object
     * @param string $object
     * @param array $ids
     * @return boolean
     */
    public function deleteObjects($objectName, array $ids)
    {
        $objectConfig =  Db_Object_Config::getInstance($objectName);

        if($objectConfig->isReadOnly())
        {
            if($this->_log)
                $this->_log->log('ORM :: cannot delete readonly objects '. $objectConfig->getName());

            return false;
        }

        $objectModel = Model::factory($objectName);
        $tableName = $objectModel->table();

    	if(empty($ids))
    		return true;

    	$specialCase = Db_Object::factory($objectName);

    	$db = $this->_getDbConnection($specialCase);

	    $where = $db->quoteInto('`id` IN(?)', $ids);

	    if($this->_eventManager)
	    {
	       	foreach ($ids as $id)
	       	{
	       		$specialCase->setId($id);
	       		$this->_eventManager->fireEvent(Db_Object_Event_Manager::BEFORE_DELETE, $specialCase);
	       	}
	    }

	    if(!$db->delete($tableName, $where))
	    	return false;

	    /*
	     * Clear object liks (links from object)
	     */
	    Model::factory($this->_linksObject)->clearLinksFor($objectName , $ids);

        $history = Model::factory($this->_historyObject);
        $userId = User::getInstance()->id;

        /*
         * Save history if required
         */
        if($objectConfig->hasHistory())
         	foreach ($ids as $v)
        		$history->log($userId, $v, Model_Historylog::Delete , $tableName);

        if($this->_eventManager)
        {
        	/*
        	 * Fire "AFTER_DELETE" event for each deleted object
        	 */
	        foreach ($ids as $id)
	        {
	        	$specialCase->setId($id);
	        	$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_DELETE, $specialCase);
	        }
        }
        return true;
    }
}