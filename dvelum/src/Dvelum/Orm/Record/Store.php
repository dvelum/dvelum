<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2018  Kirill Yegorov
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

namespace Dvelum\Orm\Record;

use Dvelum\App\EventManager;
use Dvelum\App\Model\Historylog;
use Dvelum\App\Model\Links;
use Dvelum\Orm;
use Dvelum\Db;
use Dvelum\Orm\Model;
use \Exception as Exception;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Storage adapter for Db_Object
 * @package Db
 * @subpackage Db_Object
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2015 Kirill A Egorov,
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
 * @license General Public License version 3
 * @uses Model_Links
 */
class Store
{
    /**
     * @var Event\Manager | null (optional)
     */
    protected $eventManager = null;

    /**
     * @var LoggerInterface | false
     */
    protected $log = false;

    /**
     * @var array
     */
    protected $config = [
        'linksObject'=>  'Links',
        'historyObject' => 'Historylog',
        'versionObject' => 'Vc'
    ];

    /**
     * Store constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config =  array_merge($this->config , $config);
    }

    /**
     * Get links object name
     * @return string
     */
    public function getLinksObjectName() : string
    {
        return $this->config['linksObject'];
    }

    /**
     * Get history object name
     * @return string
     */
    public function getHistoryObjectName() : string
    {
        return $this->config['historyObject'];
    }

    /**
     * Get version object name
     * @return string
     */
    public function getVersionObjectName() : string
    {
        return $this->config['versionObject'];
    }

    /**
     * Set log Adapter
     * @param LoggerInterface $log
     * @return void
     */
    public function setLog(LoggerInterface $log) : void
    {
        $this->log = $log;
    }

    /**
     * Set event manager
     * @param EventManager $obj
     */
    public function setEventManager(EventManager $obj)
    {
        $this->eventManager = $obj;
    }

    /**
     * @param Orm\RecordInterface $object
     * @return Db\Adapter
     */
    protected function getDbConnection(Orm\RecordInterface $object) : Db\Adapter
    {
        $objectModel = Model::factory($object->getName());
        return $objectModel->getDbManager()->getDbConnection($objectModel->getDbConnectionName(), null, null);
    }

    /**
     * Update Db object
     * @param Orm\RecordInterface $object
     * @param boolean $transaction - optional, use transaction if available
     * @return bool
     */
    public function update(Orm\RecordInterface $object , $transaction = true)
    {
        if($object->getConfig()->isReadOnly())
        {
            if($this->log)
                $this->log->log(LogLevel::ERROR, 'ORM :: cannot update readonly object '. $object->getConfig()->getName());

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
            return true;

        /*
         * Fire "BEFORE_UPDATE" Event if event manager exists
         */
        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::BEFORE_UPDATE, $object);

        /*
         * Validate unique values
         *
         $values = $object->validateUniqueValues();

         if(!empty($values))
         {
           if($this->log)
           {
             $errors = array();
             foreach($values as $k => $v)
             {
               $errors[] = $k . ':' . $v;
             }
             $this->log->log($object->getName() . '::update ' . implode(', ' , $errors));
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
        $db = $this->getDbConnection($object);

        if($transact && $transaction)
            $db->beginTransaction();

        $success = $this->updateOperation($object);

        if(!$success)
        {
            if($transact && $transaction)
                $db->rollback();
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
        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::AFTER_UPDATE, $object);

        return true;
    }

    protected function updateOperation(Orm\RecordInterface $object)
    {
        try{
            if(!$this->updateRecord($object)){
                return false;
            }
            /*
             * Fire "AFTER_UPDATE_BEFORE_COMMIT" Event if event manager exists
             */
            if($this->eventManager)
                $this->eventManager->fireEvent(Event\Manager::AFTER_UPDATE_BEFORE_COMMIT, $object);

            $object->commitChanges();

            return true;

        }catch (Exception $e){

            if($this->log)
                $this->log->log(LogLevel::ERROR, $object->getName().'::updateOperation '.$e->getMessage());

            return false;
        }
    }

    /**
     * Unpublish Objects
     * @param Orm\RecordInterface $object
     * @param bool $transaction - optional, default false
     * @return bool
     */
    public function unpublish(Orm\RecordInterface $object , $transaction = true) : bool
    {
        if($object->getConfig()->isReadOnly())
        {
            if($this->log)
                $this->log->log(LogLevel::ERROR, 'ORM :: cannot unpublish readonly object '. $object->getConfig()->getName());

            return false;
        }

        /*
         * Check object id
         */
        if(!$object->getId())
            return false;

        if (!$object->getConfig()->isRevControl())
        {
            if($this->log){
                $msg = $object->getName().'::unpublish Cannot unpublish object is not under version control';
                $this->log->log(LogLevel::ERROR, $msg);
            }
            return false;
        }

        /*
         * Fire "BEFORE_UNPUBLISH" Event if event manager exists
         */
        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::BEFORE_UNPUBLISH, $object);

        /*
         * Check if DB table support transactions
         */
        $transact = $object->getConfig()->isTransact();
        /*
         * Get Database connector for object model;
        */
        $db = $this->getDbConnection($object);

        if($transact && $transaction)
            $db->beginTransaction();

        $success = $this->updateOperation($object);

        if(!$success)
        {
            if($transact && $transaction)
                $db->rollback();
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
        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::AFTER_UNPUBLISH, $object);

        return true;
    }

    /**
     * Publish Db_Object
     * @param Orm\RecordInterface $object
     * @param bool $transaction - optional, default true
     * @return bool
     */
    public function publish(Orm\RecordInterface $object, $transaction = true) : bool
    {
        if($object->getConfig()->isReadOnly())
        {
            if($this->log)
                $this->log->log(LogLevel::ERROR, 'ORM :: cannot publish readonly object '. $object->getConfig()->getName());

            return false;
        }
        /*
         * Check object id
         */
        if(!$object->getId())
            return false;

        if(!$object->getConfig()->isRevControl())
        {
            if($this->log){
                $msg = $object->getName().'::publish Cannot publish object is not under version control';
                $this->log->log(LogLevel::ERROR, $msg);
            }
            return false;
        }

        /*
         * Fire "BEFORE_UNPUBLISH" Event if event manager exists
        */
        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::BEFORE_PUBLISH, $object);

        /*
         * Check if DB table support transactions
        */
        $transact = $object->getConfig()->isTransact();
        /*
         * Get Database connector for object model;
        */
        $db = $this->getDbConnection($object);

        if($transact && $transaction)
            $db->beginTransaction();

        $success = $this->updateOperation($object);

        if(!$success)
        {
            if($transact && $transaction)
                $db->rollback();
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
        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::AFTER_PUBLISH, $object);

        return true;
    }

    protected function updateLinks(Orm\RecordInterface $object) : bool
    {
        $updates = $object->getUpdates();

        if(empty($updates))
            return true;

        foreach ($updates as $k=>$v)
        {
            $conf = $object->getConfig()->getFieldConfig($k);

            if($object->getConfig()->getField($k)->isMultiLink())
            {
                if(!$this->clearLinks($object, $k,$conf['link_config']['object']))
                    return false;

                if(!empty($v) && is_array($v))
                    if(!$this->createLinks($object , $k,$conf['link_config']['object'] , $v))
                        return false;
            }
        }
        return true;
    }

    /**
     * Remove object multi links
     * @param Orm\RecordInterface $object
     * @param string $objectField
     * @param string $targetObjectName
     * @return bool
     */
    protected function clearLinks(Orm\RecordInterface $object ,$objectField , $targetObjectName)
    {

        if($object->getConfig()->getField($objectField)->isManyToManyLink())
        {
            $relationsObject = $object->getConfig()->getRelationsObject($objectField);
            if(empty($relationsObject)){
                return false;
            }
            $linksObjModel = Model::factory((string) $relationsObject);
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
            if($this->log)
                $this->log->log(LogLevel::ERROR,$object->getName().'::clearLinks '.$e->getMessage());
            return false;
        }
    }
    /**
     * Create links to the object
     * @param Orm\RecordInterface $object
     * @param string $objectField
     * @param string $targetObjectName
     * @param array $links
     * @return bool
     */
    protected function createLinks(Orm\RecordInterface $object, $objectField , $targetObjectName , array $links) : bool
    {
        $order = 0;
        $data = [];

        if($object->getConfig()->getField($objectField)->isManyToManyLink())
        {
            $relationsObject = $object->getConfig()->getRelationsObject($objectField);
            if(empty($relationsObject)){
                return false;
            }

            $linksObjModel = Model::factory((string) $relationsObject);

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

        $insert = new Model\Insert($linksObjModel);
        if(!$insert->bulkInsert($data)){
            return false;
        }

        return true;
    }
    /**
     * Insert Db object
     * @param Orm\RecordInterface $object
     * @param boolean $transaction - optional , use transaction if available
     * @return int | false -  inserted id
     */
    public function insert(Orm\RecordInterface $object , $transaction = true)
    {
        if($object->getConfig()->isReadOnly())
        {
            if($this->log)
                $this->log->log(LogLevel::ERROR, 'ORM :: cannot insert readonly object '. $object->getConfig()->getName());

            return false;
        }

        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::BEFORE_ADD, $object);
        /*
         * Check if DB table support transactions
         */
        $transact = $object->getConfig()->isTransact();

        $db = $this->getDbConnection($object);

        if($transact && $transaction)
            $db->beginTransaction();

        $success = $this->insertOperation($object);

        if(!$success)
        {
            if($transact && $transaction)
                $db->rollback();
            return false;
        }
        else
        {
            if($transact && $transaction)
                $db->commit();
        }

        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::AFTER_ADD, $object);

        return $object->getId();
    }

    /**
     * Load record data
     * @param string $objectName
     * @param int $id
     * @return array
     */
    public function load($objectName, $id): array
    {
        return Model::factory($objectName)->getItem($id);
    }

    public function encryptData(Orm\RecordInterface $object , $data)
    {
        $objectConfig = $object->getConfig();
        $ivField = $objectConfig->getIvField();
        $encFields = $objectConfig->getEncryptedFields();

        $iv = (string) $object->get($ivField);
        $cryptService = $objectConfig->getCryptService();

        /*
         * Re encrypt all fields if IV changed
         */
        if(isset($data[$ivField]))
        {
            foreach ($encFields as $field){
                $data[$field] = $cryptService->encrypt($object->get($field), $iv);
            }
        }
        /*
         * Encrypt values
         */
        else
        {
            foreach ($data as $field => &$value){
                if(in_array($field , $encFields , true)){
                    $value = $cryptService->encrypt($value, $iv);
                }
            }unset($value);
        }
        return $data;
    }

    protected function insertOperation(Orm\RecordInterface $object) : bool
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
            if($this->log)
            {
                $errors = [];
                foreach($values as $k => $v) {
                    $errors[] = $k . ':' . $v;
                }
                $this->log->log(LogLevel::ERROR,$object->getName() . '::insert ' . implode(', ' , $errors));
            }
            return false;
        }

        $id = $this->insertRecord($object, $updates);

        if(!$id)
            return false;

        $object->setId($id);

        if(!$this->updateLinks($object))
            return false;

        try{
            /*
             * Fire "AFTER_UPDATE_BEFORE_COMMIT" Event if event manager exists
             */
            if($this->eventManager){
                $this->eventManager->fireEvent(Event\Manager::AFTER_INSERT_BEFORE_COMMIT, $object);
            }
        }catch (Exception $e){

            if($this->log)
                $this->log->log(LogLevel::ERROR, $object->getName().'::insertOperation '.$e->getMessage());

            return false;
        }

        $object->commitChanges();
        $object->setId($id);

        return true;
    }

    /**
     * Insert record
     * @param Orm\RecordInterface $object
     * @param array $data
     * @return mixed record id
     */
    protected function insertRecord(Orm\RecordInterface $object , array $data)
    {
        $db = $this->getDbConnection($object);
        $objectTable = Model::factory($object->getName())->table();

        try {
            $db->insert($objectTable, $object->serializeLinks($data));
        }catch (Exception $e) {
            if($this->log){
                $this->log->log(LogLevel::ERROR,$object->getName() . '::insert ' . $e->getMessage());
            }
            return false;
        }
        return $db->lastInsertId($objectTable , $object->getConfig()->getPrimaryKey());
    }

    /**
     * Delete record
     * @param Orm\RecordInterface $object
     * @return bool
     */
    protected function deleteRecord(Orm\RecordInterface $object ) : bool
    {
        $db = $this->getDbConnection($object);
        try{
            $db->delete(
                Model::factory($object->getName())->table(),
                $db->quoteIdentifier($object->getConfig()->getPrimaryKey()).' =' . $object->getId()
            );
            return true;
        }catch (Exception $e){
           if($this->log){
              $this->log->log(LogLevel::ERROR,$object->getName().'::delete '.$e->getMessage());
           }
           return false;
        }
    }

    /**
     * Update record
     * @param Orm\RecordInterface $object
     * @return bool
     */
    protected function updateRecord(Orm\RecordInterface $object ) : bool
    {
        $db = $this->getDbConnection($object);

        $updates = $object->getUpdates();

        if($object->getConfig()->hasEncrypted())
            $updates = $this->encryptData($object , $updates);

        $this->updateLinks($object);

        $updates = $object->serializeLinks($updates);

        if(!empty($updates)){
            try{
                $db->update(
                    Model::factory($object->getName())->table() ,
                    $updates,
                    $db->quoteIdentifier($object->getConfig()->getPrimaryKey()).' = '.$object->getId()
                );
            }catch (Exception $e){
                if($this->log){
                    $this->log->log(LogLevel::ERROR,$object->getName().'::update '.$e->getMessage());
                }
                return false;
            }
        }
        return true;
    }

    /**
     * Add new object version
     * @param Orm\RecordInterface $object
     * @param bool $useTransaction - optional , use transaction if available
     * @return int|false - version number
     */
    public function addVersion(Orm\RecordInterface $object , bool $useTransaction = true)
    {
        if($object->getConfig()->isReadOnly())
        {
            if($this->log){
                $msg = 'ORM :: cannot addVersion for readonly object '. $object->getConfig()->getName();
                $this->log->log(LogLevel::ERROR, $msg);
            }

            return false;
        }
        /*
         * Check object id
        */
        if(!$object->getId())
            return false;

        if(!$object->getConfig()->isRevControl())
        {
            if($this->log){
                $msg = $object->getName().'::publish Cannot addVersion. Object is not under version control';
                $this->log->log(LogLevel::ERROR, $msg);
            }

            return false;
        }

        /*
         * Fire "BEFORE_ADD_VERSION" Event if event manager exists
        */
        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::BEFORE_ADD_VERSION, $object);

        /**
         * Create new revision
         * @var \Dvelum\App\Model\Vc $versionModel
         */
         $versionModel = Model::factory($this->config['versionObject']);
         $versNum = $versionModel->newVersion($object);

        if(!$versNum)
            return false;

        try{
            /**
             * @var Orm\RecordInterface $oldObject
             */
            $oldObject = Orm\Record::factory($object->getName() , $object->getId());
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

            if(!$oldObject->save($useTransaction))
                throw new Exception('Cannot save object');

        }catch(Exception $e){
            if($this->log)
                $this->log->log(LogLevel::ERROR, 'Cannot update unpublished object data '. $e->getMessage());
            return false;
        }

        /*
         * Fire "AFTER_ADD_VERSION" Event if event manager exists
         */
        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::AFTER_ADD_VERSION, $object);

        return $versNum;
    }

    /**
     * Delete Orm\Record
     * @param Orm\RecordInterface $object
     * @param boolean $transaction - optional , use transaction if available
     * @return bool
     */
    public function delete(Orm\RecordInterface $object , $transaction = true) : bool
    {
        $objectConfig = $object->getConfig();

        if($objectConfig->isReadOnly()) {
            if($this->log){
                $this->log->log(LogLevel::ERROR, 'ORM :: cannot delete readonly object '. $object->getName());
            }
            return false;
        }

        if(!$object->getId())
            return false;

        if($this->eventManager)
            $this->eventManager->fireEvent(Event\Manager::BEFORE_DELETE, $object);

        $transact = $object->getConfig()->isTransact();

        $db = $this->getDbConnection($object);

        if($transact && $transaction)
            $db->beginTransaction();

        $fields = $objectConfig->getFieldsConfig();

        foreach ($fields as $field=>$conf) {
            if($objectConfig->getField($field)->isMultiLink()){
                $linkedObject = $objectConfig->getField($field)->getLinkedObject();
                if(empty($linkedObject)){
                    return false;
                }
                if(!$this->clearLinks($object, $field, $linkedObject)){
                    return false;
                }
            }
        }

        $success = $this->deleteRecord($object);

        try{
            /*
             * Fire "AFTER_UPDATE_BEFORE_COMMIT" Event if event manager exists
             */
            if($this->eventManager){
                $this->eventManager->fireEvent(Event\Manager::AFTER_DELETE_BEFORE_COMMIT, $object);
            }
        }catch (Exception $e){
            if($this->log){
                $this->log->log(LogLevel::ERROR,$object->getName().'::delete '.$e->getMessage());
            }
            $success = false;
        }

        if($transact && $transaction)
        {
            if($success){
                $db->commit();
            }else{
                $db->rollback();
            }
        }

        if($success && $this->eventManager){
            $this->eventManager->fireEvent(Event\Manager::AFTER_DELETE, $object);
        }

        return $success;
    }
    /**
     * Delete Orm\Record
     * @param string $objectName
     * @param array $ids
     * @return boolean
     */
    public function deleteObjects($objectName, array $ids) : bool
    {
        $objectConfig =  Orm\Record\Config::factory($objectName);

        if($objectConfig->isReadOnly())
        {
            if($this->log)
                $this->log->log(LogLevel::ERROR, 'ORM :: cannot delete readonly objects '. $objectConfig->getName());

            return false;
        }

        $objectModel = Model::factory($objectName);
        $tableName = $objectModel->table();

        if(empty($ids))
            return true;

        /**
         * @var  Orm\RecordInterface $specialCase
         */
        $specialCase = Orm\Record::factory($objectName);

        $db = $this->getDbConnection($specialCase);

        $where = '`id` IN('.$db->quoteValueList($ids).')';

        if($this->eventManager)
        {
            foreach ($ids as $id)
            {
                $specialCase->setId($id);
                $this->eventManager->fireEvent(Event\Manager::BEFORE_DELETE, $specialCase);
            }
        }

        try{
            $db->delete($tableName, $where);
        }catch (Exception $e){
            if($this->log){
                $this->log->log(LogLevel::ERROR, 'ORM :: cannot delete'. $objectConfig->getName().' '.$e->getMessage());
            }
            return false;
        }

        /**
         * Clear object links (links from object)
         * @var Links $linksModel
         */
        $linksModel = Model::factory($this->config['linksObject']);
        $linksModel->clearLinksFor($objectName , $ids);

        /**
         * @var Historylog $history
         */
        $history = Model::factory($this->config['historyObject']);
        $userId = \Dvelum\App\Session\User::factory()->getId();

        /*
         * Save history if required
         */
        if($objectConfig->hasHistory())
            foreach ($ids as $v)
                $history->log($userId, $v, Historylog::Delete , $tableName);

        if($this->eventManager)
        {
            /*
             * Fire "AFTER_DELETE" event for each deleted object
             */
            foreach ($ids as $id)
            {
                $specialCase->setId($id);
                $this->eventManager->fireEvent(Event\Manager::AFTER_DELETE, $specialCase);
            }
        }
        return true;
    }

    /**
     * Validate unique fields, object field groups
     * Returns array of errors or null .
     * @param string $objectName
     * @param mixed $recordId
     * @param array $groupsData
     * @return array|null
     */
    public function validateUniqueValues(string $objectName, $recordId, array $groupsData) : ?array
    {

        $model = Model::factory($objectName);
        $db = $model->getDbConnection();

        $primaryKey = $model->getPrimaryKey();

        foreach ($groupsData as $group)
        {
            $sql = $db->select()
                ->from($model->table() , ['count'=>'COUNT(*)']);

            if($recordId)
                $sql->where(' '.$db->quoteIdentifier($primaryKey).' != ?', $recordId);

            foreach ($group as $k=>$v)
            {
                if($k===$primaryKey)
                    continue;

                $sql->where($db->quoteIdentifier($k) . ' =?' , $v);
            }

            $count = $db->fetchOne($sql);

            if($count > 0){
                return $group;
            }
        }
        return null;
    }
}