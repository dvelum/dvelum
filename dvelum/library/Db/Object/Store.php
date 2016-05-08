<?php
/**
 * Storage adapter for Db_Object
 * @package Db
 * @subpackage Db_Object
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2015 Kirill A Egorov,
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
    /**
     * @var Log
     */
    protected $_log = false;
    /**
     * @var array
     */
    protected $config = [
        'linksObject'=>  'Links',
        'historyObject' => 'Historylog',
        'versionObject' => 'Vc'
    ];

    public function __construct(array $config = array())
    {
       if(empty($options))
           return;

       $this->config =  array_merge($this->config , $config);
    }
    /**
     * Get links object name
     * @return string
     */
    public function getLinksObjectName()
    {
    	return $this->config['linksObject'];
    }
    /**
     * Get history object name
     * @return string
     */
    public function getHistoryObjectName()
    {
    	return $this->config['historyObject'];
    }
    /**
     * Get version object name
     * @return string
     */
    public function getVersionObjectName()
    {
    	return $this->config['versionObject'];
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
     * Set event manager
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
     * @param boolean $transaction - optional, use transaction if available
     * @return boolean
     */
    public function update(Db_Object $object , $transaction = true)
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
            $updates = $object->getUpdates();

            if($object->getConfig()->hasEncrypted())
                $updates = $this->encryptData($object , $updates);

            $this->_updateLinks($object);

            $updates = $object->serializeLinks($updates);
            
            if(!empty($updates))
                $db->update($object->getTable() , $updates, $db->quoteIdentifier($object->getConfig()->getPrimaryKey()).' = '.$object->getId());

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
     * @param boolean $transaction - optional, default false
     * @return bool
     */
    public function unpublish(Db_Object $object , $transaction = true)
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
    	 * Fire "AFTER_UPDATE" Event if event manager exists
    	*/
    	if($this->_eventManager)
    		$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_UNPUBLISH, $object);

    	return true;
    }

   /**
    * Publish Db_Object
    * @param Db_Object $object
    * @param boolean $transaction - optional, default true
    * @return boolean
    */
    public function publish(Db_Object $object, $transaction = true)
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
     * Remove object multi links
     * @param Db_Object $object
     * @param string $objectField
     * @param string $targetObjectName
     * @return bool
     */
    protected function _clearLinks(Db_Object $object ,$objectField , $targetObjectName)
    {

        if($object->getConfig()->isManyToManyLink($objectField))
        {
            $linksObjModel = Model::factory($object->getConfig()->getRelationsObject($objectField));
            $where = ' `source_id` = '.intval($object->getId());
        }
        else
        {
            $linksObjModel  = Model::factory($this->config['linksObject']);

            $db = $linksObjModel->getDbConnection();

            $where = 'src = '.$db->quote($object->getName()).'
        		AND
        		 src_id = '.intval($object->getId()).'
        		AND
        		 src_field = '.$db->quote($objectField).'
                AND
                 target = '.$db->quote($targetObjectName);
        }
        $db = $linksObjModel->getDbConnection();

        try{
            $db->delete($linksObjModel->table() , $where);
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
     * @return boolean
     */
    protected function _createLinks(Db_Object $object, $objectField , $targetObjectName , array $links)
    {
        $order = 0;
        $data = [];

        if($object->getConfig()->isManyToManyLink($objectField))
        {
            $linksObjModel = Model::factory($object->getConfig()->getRelationsObject($objectField));

            foreach ($links as $k=>$v)
            {
                $data[] = array(
                    'source_id'=>$object->getId(),
                    'target_id'=>$v,
                    'order_no'=>$order
                );
                $order++;
            }
        }
        else
        {
            $linksObjModel  = Model::factory($this->config['linksObject']);
            foreach ($links as $k=>$v)
            {
                $data[] = array(
                    'src'=>$object->getName(),
                    'src_id'=>$object->getId(),
                    'src_field'=>$objectField,
                    'target'=>$targetObjectName,
                    'target_id'=>$v,
                    'order'=>$order
                );
                $order++;
            }
        }
        if(!$linksObjModel->multiInsert($data))
            return false;

        return true;
    }
    /**
     * Insert Db object
     * @param Db_Object $object
     * @param boolean $transaction - optional , use transaction if available
     * @return integer -  inserted id
     */
    public function insert(Db_Object $object , $transaction = true)
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

        if($this->_eventManager)
        	$this->_eventManager->fireEvent(Db_Object_Event_Manager::AFTER_ADD, $object);

        return $object->getId();
    }

    public function encryptData(Db_Object $object , $data)
    {
        $objectConfig = $object->getConfig();
        $ivField = $objectConfig->getIvField();
        $encFields = $objectConfig->getEncryptedFields();

        $iv = base64_decode($object->get($ivField));

        /*
         * Re encode all fields if IV changed
         */
        if(isset($data[$ivField]))
        {
            foreach ($encFields as $field){
                $data[$field] = $objectConfig->encrypt($object->get($field), $iv);
            }
        }
        /*
         * Encode values
         */
        else
        {
            foreach ($data as $field => &$value){
                if(in_array($field , $encFields , true)){
                    $value = $objectConfig->encrypt($value, $iv);
                }
            }unset($value);
        }
        return $data;
    }

    protected function _insertOperation(Db_Object $object)
    {
    	$insertId = $object->getInsertId();

    	if($insertId){
    		$updates = array_merge($object->getData() , $object->getUpdates());
    		$updates[$object->getConfig()->getPrimaryKey()] = $insertId;
    	}else{
    		$updates =  $object->getUpdates();
    	}

        if($object->getConfig()->hasEncrypted())
            $updates = $this->encryptData($object , $updates);

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
     * @param boolean $useTransaction - optional , use transaction if available
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
    	$versNum = Model::factory($this->config['versionObject'])->newVersion($object);

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

            }

            $oldObject->set('date_updated' , $object->get('date_updated'));
            $oldObject->set('editor_id' , $object->get('editor_id'));
            $oldObject->set('last_version', $versNum);

            if(!$oldObject->save(false , $useTransaction))
                throw new Exception('Cannot save object');

        }catch(Exception $e){
            if($this->_log)
                $this->_log->log('Cannot update unpublished object data '. $e->getMessage());
    		return false;
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
     * @param boolean $transaction - optional , use transaction if available
     * @return boolean
     */
    public function delete(Db_Object $object , $transaction = true)
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

        Model::factory($this->config['linksObject'])->clearObjectLinks($object);

        if($db->delete($object->getTable(), $db->quoteIdentifier($object->getConfig()->getPrimaryKey()).' =' . $object->getId()))
        {
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
     * @param string $objectName
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
	    Model::factory($this->config['linksObject'])->clearLinksFor($objectName , $ids);

        $history = Model::factory($this->config['historyObject']);
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