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

namespace Dvelum\Orm;

use Dvelum\Service;
use Psr\Log\LogLevel;
use Dvelum\App\Session\User;

/**
 * Database Object class. ORM element.
 * @author Kirill Egorov 2011-2017  DVelum project
 * @package Dvelum\Orm
 */
class Record implements RecordInterface
{
    /**
     * Error log adapter
     * @var \Psr\Log\LoggerInterface| bool
     */
    protected $logger = false;

    protected $name;

    /**
     * @var Record\Config
     */
    protected $config;

    protected $id;
    protected $primaryKey;
    protected $data = [];
    protected $updates = [];
    protected $errors = [];

    /**
     * Insert ID
     * @var integer
     */
    protected $insertId = false;

    /**
     * Access Control List Adapter
     * @var Record\Acl
     */
    protected $acl = false;
    /**
     * System flag. Disable ACL create permissions check
     * @var bool
     */
    protected $disableAclCheck = false;

    /**
     * @var Model
     */
    protected $model;

    /**
     * Loaded version of VC object
     * @var integer
     */
    protected $version = 0;

    /**
     * The object constructor takes its name and identifier,
     * (the parameter is not required), if absent,
     * there will be created a new object. If ORM lacks the object with the specified
     * identifier, an Exception will show up
     * Using this method is highly undesirable,
     * the factory method Db_Object::factory() is more advisable to use
     * @param string $name
     * @param bool|int $id - optional
     * @throws Exception
     */
    public function __construct(string $name, $id = false)
    {
        $this->name = strtolower($name);
        $this->id = $id;

        $this->config = Record\Config::factory($name);
        $this->primaryKey = $this->config->getPrimaryKey();
        $this->model = Model::factory($name);
        $this->acl = $this->config->getAcl();

        $this->logger = $this->model->getLogsAdapter();

        if($this->id){
            $this->checkCanRead();
            $this->loadData();
        }else{
            if($this->acl && !$this->disableAclCheck) {
                $this->checkCanCreate();
            }
        }
    }

    /**
     * Disable ACL create permissions check
     * @param $bool $bool
     */
    public function disableAcl(bool $bool) : void
    {
        $this->disableAclCheck = $bool;
    }

    /**
     * Load object data
     * @throws Exception
     * @return void
     */
    protected function loadData() : void
    {
        $data =  $this->model->getItem($this->id);

        if(empty($data))
            throw new Exception('Cannot find object '.$this->name.':'.$this->id);

        $links = $this->config->getLinks([Record\config::LINK_OBJECT_LIST]);

        if(!empty($links))
        {
            foreach($links as $object => $fields)
            {
                foreach($fields as $field=>$linkType)
                {
                    if($this->config->getField($field)->isManyToManyLink()){
                        $relationsObject = $this->config->getRelationsObject($field);
                        $relationsData = Model::factory($relationsObject)->getList(
                            ['sort'=>'order_no', 'dir' =>'ASC'],
                            ['source_id'=>$this->id],
                            ['target_id']
                        );
                    }else{
                        $linkedObject = $this->config->getField($field)->getLinkedObject();
                        $linksObject = Model::factory($linkedObject)->getStore()->getLinksObjectName();
                        $linksModel = Model::factory($linksObject);
                        $relationsData = $linksModel->query()
                                                    ->params(['sort'=>'order','dir'=>'ASC'])
                                                    ->filters([
                                                        'src' => $this->name,
                                                        'src_id' => $this->id,
                                                        'src_field' =>$field,
                                                        'target' => $linkedObject
                                                    ])
                                                    ->fields(['target_id'])
                                                    ->fetchAll();


                    }
                    if(!empty($relationsData)){
                        $data[$field] = \Utils::fetchCol('target_id',$relationsData);
                    }
                }
            }
        }

        $this->setRawData($data);
    }

    /**
     * Set raw data from storage
     * @param array $data
     * @return void
     */
    public function setRawData(array $data) : void
    {
        unset($data[$this->primaryKey]);
        $iv = false;

        if($this->config->hasEncrypted()){
            $ivField = $this->config->getIvField();
            if(isset($data[$ivField]) && !empty($data[$ivField])){
                $iv = $data[$ivField];
            }
        }

        foreach($data as $field => &$value)
        {
            $fieldObject = $this->getConfig()->getField($field);

            if($fieldObject->isBoolean()){
                if($value)
                    $value = true;
                else
                    $value = false;
            }

            if($fieldObject->isEncrypted()){
                $value = (string) $value;
                if(is_string($iv) && strlen($value) && strlen($iv)){
                    $value = $this->config->getCryptService()->decrypt($value, $iv);
                }
            }
        }
        unset($value);
        $this->data = $data;
    }

    /**
     * Get object fields
     * @return array
     */
    public function getFields() : array
    {
        return array_keys($this->config->get('fields'));
    }

    /**
     * Get the object data, returns the associative array ‘field name’
     * @param boolean $withUpdates, optional default true
     * @return array
     */
    public function getData($withUpdates = true) : array
    {
        if($this->acl)
            $this->checkCanRead();

        $data = $this->data;
        $data[$this->primaryKey] = $this->id;

        if($withUpdates)
            foreach ($this->updates as $k=>$v)
                $data[$k] = $v;

        return $data;
    }
    /**
     * Get object name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get object identifier
     * @return int|bool
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the full name of the database storing the object data (with prefix)
     * @return string
     */
    public function getTable() : string
    {
        return $this->model->table();
    }

    /**
     * Check if there are object property changes
     * not saved in the database
     * @return bool
     */
    public function hasUpdates() : bool
    {
        return !empty($this->updates);
    }

    /**
     * Get ORM configuration object (data structure helper)
     * @return Record\Config
     */
    public function getConfig() : Record\Config
    {
        return $this->config;
    }

    /**
     * Get updated, but not saved object data
     * @return array
     */
    public function getUpdates() : array
    {
        if($this->acl)
            $this->checkCanRead();

        return $this->updates;
    }

    /**
     * Set the object identifier (existing DB ID)
     * @param integer $id
     * @return void
     */
    public function setId($id) : void
    {
        if($this->acl && !$this->disableAclCheck)
            $this->checkCanEdit();

        $this->id = (int) $id;
    }

    /**
     * Commit the object data changes (without saving)
     * @return void
     */
    public function commitChanges() : void
    {
        if(empty($this->updates))
            return;

        foreach ($this->updates as $k=>$v)
            $this->data[$k] = $v;

        $this->updates = [];
    }

    /**
     * Check if the object field exists
     * @param string $name
     * @return bool
     */
    public function fieldExists(string $name) : bool
    {
        return $this->config->fieldExists($name);
    }

    /**
     * Get the related object name for the field
     * (available if the object field is a link to another object)
     * @param string $field - field name
     * @return string
     */
    public function getLinkedObject(string $field) : string
    {
        return $this->config->getField($field)->getLinkedObject();
    }

    /**
     * Check if the listed objects exist
     * @param string $name
     * @param mixed integer/array $ids
     * @return bool
     */
    static public function objectExists($name , $ids) : bool
    {
        if(!Record\Config::configExists($name))
            return false;

        try {
            $cfg = Record\Config::factory($name);
        }catch (Exception $e){
            return false;
        }

        if(!is_array($ids))
            $ids = array($ids);

        $model = Model::factory($name);
        $data = $model->getItems($ids);

        if(empty($data))
            return false;

        $data = \Utils::fetchCol($cfg->getPrimaryKey(), $data);

        foreach ($ids as $v)
            if(!in_array(intval($v) , $data , true))
                return false;
        return true;
    }

    /**
     * Set the object properties using the associative array of fields and values
     * @param array $values
     * @throws Exception
     * @return void
     */
    public function setValues(array $values) : void
    {
        if(!empty($values))
            foreach ($values as $k => $v)
                $this->set($k, $v);
    }

    /**
     * Set the object field val
     * @param string $name
     * @param mixed $value
     * @return bool
     * @throws Exception
     */
    public function set(string $name , $value) : bool
    {
        if($this->acl)
            $this->checkCanEdit();

        $propConf = $this->config->getFieldConfig($name);
        $validator = $this->getConfig()->getValidator($name);

        $field = $this->getConfig()->getField($name);

        // Validate value using special validator
        // Skip validation if value is null and object field can be null
        if ($validator && (!$field->isNull() || !is_null($value)) && !call_user_func_array([$validator, 'validate'], [$value])){
            throw new Exception('Invalid value for field ' . $name. ' ('.$this->getName().')');
        }

        $value = $field->filter($value);
        if(!$field->validate($value)){
            throw new Exception('Invalid value for field '. $name.'. '.$field->getValidationError(). ' ('.$this->getName().')');
        }

        if(isset($propConf['db_len']) && $propConf['db_len']){
            if($propConf['db_type'] == 'bit' && (strlen($value) > $propConf['db_len'] || strlen($value) < $propConf['db_len']))
                throw new Exception('Invalid length for bit value ['.$name.']  ('.$this->getName().')');
        }

        if(isset($this->data[$name]))
        {
            if($this->getConfig()->getField($name)->isBoolean() && intval($this->data[$name]) === intval($value) ) {
                unset($this->updates[$name]);
                return true;
            }

            if($this->data[$name] === $value) {
                unset($this->updates[$name]);
                return true;
            }
        }

        $this->updates[$name] = $value;
        return true;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws Exception
     * @return void
     */
    public function __set($key , $value) : void
    {
        if($key===$this->primaryKey)
            $this->setId($value);
        else
            $this->set($key, $value);
    }

    public function __isset($key) : bool
    {
        if($key === $this->primaryKey)
            return isset($this->id);

        if(!isset($this->data[$key]) && !isset($this->updates[$key]))
            return false;

        return true;
    }
    /**
     * @param string $key
     * @throws Exception
     * @return mixed
     */
    public function __get($key)
    {
        if($key===$this->primaryKey)
            return $this->getId();

        return $this->get($key);
    }

    /**
     * Get the object field value
     * If field value was updated method returns new value
     * otherwise returns old value
     * @param string $name - field name
     * @throws Exception
     * @return mixed
     */
    public function get(string $name)
    {
        if($this->acl)
            $this->checkCanRead();

        if($name === $this->primaryKey)
            return $this->getId();

        if(!$this->fieldExists($name))
            throw new Exception('Invalid property requested ['.$name.']');

        $value = null;

        if(isset($this->data[$name]))
            $value = $this->data[$name];

        if(isset($this->updates[$name]))
            $value = $this->updates[$name];

        return $value;
    }

    /**
     * Get the initial object field value (received from the database)
     * whether the field value was updated or not
     * @param string $name - field name
     * @throws Exception
     * @return mixed
     */
    public function getOld(string $name)
    {
        if($this->acl)
            $this->checkCanRead();

        if(!$this->fieldExists($name))
            throw new Exception('Invalid property requested ['.$name.']');

        return $this->data[$name];
    }

    /**
     * Save changes
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * If data update in your code is carried out within an external transaction
     * set the value to  false,
     * otherwise, the first update will lead to saving the changes
     * @return integer | boolean;
     */
    public function save($useTransaction = true)
    {
        if($this->acl){
            try{
                $this->checkCanEdit();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if($this->logger)
                    $this->logger->log(LogLevel::ERROR , $e->getMessage());
                return false;
            }
        }

        $store  = $this->model->getStore();

        if($this->logger)
            $store->setLog($this->logger);

        if($this->config->isReadOnly())
        {
            $text = 'ORM :: cannot save readonly object '. $this->config->getName();
            $this->errors[] = $text;
            if($this->logger)
                $this->logger->log(LogLevel::ERROR, $text);
            return false;
        }

        if($this->config->hasEncrypted()){
            $ivField = $this->config->getIvField();
            $ivData = $this->get($ivField);
            if(empty($ivData)){
                $this->set($ivField , $this->config->getCryptService()->createVector());
            }
        }

        if($this->config->isRevControl())
        {
            if(!$this->getId()){
                $this->set('date_created', date('Y-m-d H:i:s'));
                $this->set('date_updated', date('Y-m-d H:i:s'));
                $this->set('published' , false);
                $this->set('author_id',  User::getInstance()->getId());
            }else{
                $this->set('date_updated', date('Y-m-d H:i:s'));
                $this->set('editor_id',  User::getInstance()->getId());
            }
        }


        $emptyFields = $this->hasRequired();
        if($emptyFields!==true)
        {
            $text = 'ORM :: Fields can not be empty. '.$this->getName().' ['.implode(',', $emptyFields).']';
            $this->errors[] = $text;

            if($this->logger)
                $this->logger->log(LogLevel::ERROR, $text);

            return false;
        }

        $values = $this->validateUniqueValues();

        if(!empty($values))
        {
            foreach($values as $k => $v) {
                $text = 'The Field value should be unique '.$k . ':' . $v;
                $this->errors[] = $text;
            }

            if($this->logger){
                $this->logger->log(LogLevel::ERROR, $this->getName() . ' ' . implode(', ' , $this->errors));
            }

            return false;
        }

        try {
            if(!$this->getId()){
                $id = $store->insert($this , $useTransaction);
                $this->setId($id);
            } else {
                $id = (int) $store->update($this , $useTransaction);
            }

            $this->commitChanges();
            return $id;

        }catch (\Exception $e){
            $this->errors[] = $e->getMessage();
            if($this->logger){
                $this->logger->log(LogLevel::ERROR, $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Deleting an object
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * If data update in your code is carried out within an external transaction
     * set the value to  false,
     * otherwise, the first update will lead to saving the changes
     * @return bool - success flag
     */
    public function delete($useTransaction = true) : bool
    {
        if($this->acl){
            try{
                $this->checkCanDelete();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if($this->logger)
                    $this->logger->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }
        $store  = $this->model->getStore();
        return $store->delete($this, $useTransaction);
    }

    /**
     * Serialize Object List properties
     * @param array $data
     * @return array
     */
    public function serializeLinks(array $data) : array
    {
        foreach ($data as $k=>$v)
        {
            if($this->config->getField($k)->isMultiLink()) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * Validate unique fields, object field groups
     * Returns array of errors or null .
     * @return  array | null
     */
    public function validateUniqueValues(): ?array
    {
        $uniqGroups = [];

        foreach ($this->config->get('fields') as $k=>$v)
        {
            if($k===$this->primaryKey)
                continue;

            if(!$this->config->getField($k)->isUnique())
                continue;

            $value  = $this->get($k);
            if(is_array($value))
                $value = serialize($value);

            if(is_array($v['unique']))
            {
                foreach ($v['unique'] as $val)
                {
                    if(!isset($uniqGroups[$val]))
                        $uniqGroups[$val] = [];

                    $uniqGroups[$val][$k] = $value;
                }
            }
            else
            {
                $v['unique'] = strval($v['unique']);

                if(!isset($uniqGroups[$v['unique']]))
                    $uniqGroups[$v['unique']] = [];

                $uniqGroups[$v['unique']][$k] = $value;
            }
        }

        if(empty($uniqGroups))
            return null;

        $db = $this->model->getDbConnection();

        foreach ($uniqGroups as $group)
        {
            $sql = $db->select()
                ->from($this->model->table() , array('count'=>'COUNT(*)'));

            if($this->getId())
                $sql->where(' '.$db->quoteIdentifier($this->primaryKey).' != ?', $this->getId());

            foreach ($group as $k=>$v)
            {
                if($k===$this->primaryKey)
                    continue;

                $sql->where($db->quoteIdentifier($k) . ' =?' , $v);
            }

            $count = $db->fetchOne($sql);

            if($count > 0){
                return array_keys($group);
            }
        }
        return null;
    }

    /**
     * Convert object into string representation
     * @return string
     */
    public function __toString() : string
    {
        return strval($this->getId());
    }

    /**
     * Get object title
     */
    public function getTitle() : string
    {
        return (string)$this->model->getTitle($this);
    }

    /**
     * Factory method of object creation is preferable to use, cf. method  __construct() description
     * @param string $name
     * @param int|int[]|bool $id , optional default false
     * @throws Exception
     * @return Object|Object[]
     */
    static public function factory(string $name , $id = false)
    {
        /**
         * @var Orm $service
         */
        $service = Service::get('orm');
        return $service->object($name, $id);
    }

    /**
     * Check for required fields
     * @return bool | array
     */
    protected function hasRequired()
    {
        $emptyFields = [];
        $fields = $this->getFields();

        foreach ($fields as $name)
        {
            if(!$this->config->getField($name)->isRequired())
                continue;

            $val = $this->get($name);
            if(!strlen((string)$val))
                $emptyFields[]= $name;
        }

        if(empty($emptyFields))
            return true;
        else
            return $emptyFields;
    }

    /**
     * Get errors
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    protected function checkCanRead()
    {
        if($this->acl && !$this->acl->canRead($this))
            throw new Exception('You do not have permission to view data in this object ['.$this->getName().':'.$this->getId().'].');
    }

    protected function checkCanEdit()
    {
        if($this->acl && !$this->acl->canEdit($this))
            throw new Exception('You do not have permission to edit data in this object ['.$this->getName().':'.$this->getId().'].');
    }

    protected function checkCanDelete()
    {
        if($this->acl && !$this->acl->canDelete($this))
            throw new Exception('You do not have permission to delete this object ['.$this->getName().':'.$this->getId().'].');
    }

    protected function checkCanCreate()
    {
        if($this->acl && !$this->acl->canCreate($this))
            throw new Exception('You do not have permission to create object ['.$this->getName().'].');
    }

    protected function checkCanPublish()
    {
        if($this->acl && !$this->acl->canPublish($this))
            throw new Exception('You do not have permission to publish object ['.$this->getName().'].');
    }

    /**
     * Unpublish VC object
     * @param bool $log  - log changes
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * @return bool
     */
    public function unpublish($log = true , $useTransaction = true) : bool
    {
        if($this->acl){
            try{
                $this->checkCanPublish();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if($this->logger)
                    $this->logger->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }

        $store  = $this->model->getStore();
        if($this->logger)
            $store->setLog($this->logger);

        $this->published_version = 0;
        $this->published = false;
        $this->date_updated = date('Y-m-d H:i:s');
        /**
         * @todo refactor
         */
        $this->editor_id = User::factory()->getId();

        return $store->unpublish($this , $useTransaction);
    }

    /**
     * Publish VC object
     * @param bool|int $version - optional, default current version
     * @param bool $log - log changes
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * @return bool
     * @throws Exception
     */
    public function publish($version = false , $log = true , $useTransaction = true): bool
    {
        if($this->acl){
            try{
                $this->checkCanPublish();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if($this->logger)
                    $this->logger->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }

        $store  = $this->model->getStore();

        if($this->logger)
            $store->setLog($this->logger);

        if($version && $version !== $this->getVersion())
        {
            try{
                $this->loadVersion($version);
            }
            catch (Exception $e)
            {
                $this->errors[] = $e->getMessage();

                if($this->logger)
                    $this->logger->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }

        $this->published = true;
        $this->date_updated = date('Y-m-d H:i:s');
        $this->editor_id = User::factory()->getId();

        if(empty($this->date_published))
            $this->set('date_published' , date('Y-m-d H:i:s'));

        $this->published_version = $this->getVersion();
        return $store->publish($this , $useTransaction);
    }

    /**
     * Get loaded version
     * @return int
     */
    public function getVersion() : int
    {
        return $this->version;
    }

    /**
     * Load version
     * @param int $vers
     * @return void
     * @throws Exception
     */
    public function loadVersion(int $vers) :void
    {
        $this->rejectChanges();
        $versionObject = $this->model->getStore()->getVersionObjectName();

        /**
         * @var \Model_Vc $vc
         */
        $vc = Model::factory($versionObject);

        $data = $vc->getData($this->getName() , $this->getId() , $vers);

        $pKey = $this->config->getPrimaryKey();

        if(isset($data[$pKey]))
            unset($data[$pKey]);

        if(empty($data))
            throw new Exception('Cannot load version for ' . $this->getName() . ':' . $this->getId() . '. v:' . $vers);

        $iv = false;
        if($this->config->hasEncrypted()){
            $ivField = $this->config->getIvField();
            if(isset($data[$ivField]) && !empty($data[$ivField])){
                $iv = $data[$ivField];
            }
        }

        foreach($data as $k => $v)
        {
            if($this->fieldExists($k))
            {
                try{

                    if($this->config->getField($k)->isEncrypted()){
                        $v = (string) $v;
                        if(is_string($iv) && strlen($v) && strlen($iv)){
                            $v = $this->config->getCryptService()->decrypt($v, $iv);
                        }
                    }

                    if($k!== $this->config->getPrimaryKey() && !$this->config->isVcField($k))
                        $this->set($k , $v);

                }catch(Exception $e){
                    throw new Exception('Cannot load version data ' . $this->getName() . ':' . $this->getId() . '. v:' . $vers.'. This version contains incompatible data. ' . $e->getMessage());
                }
            }
        }
        $this->version = $vers;
    }

    /**
     * Reject changes
     */
    public function rejectChanges() : void
    {
        $this->updates = [];
    }

    /**
     * Save object as new version
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * @return bool
     */
    public function saveVersion(bool $useTransaction = true) : bool
    {
        if(!$this->config->isRevControl()){
            return $this->save($useTransaction);
        }

        if($this->config->hasEncrypted()){
            $ivField = $this->config->getIvField();
            $ivData = $this->get($ivField);
            if(empty($ivData)){
                $this->set($ivField , $this->config->getCryptService()->createVector());
            }
        }

        if($this->acl)
        {
            try{
                $this->checkCanEdit();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if($this->logger)
                    $this->logger->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }

        if(!$this->getId())
        {
            if(!$this->save($useTransaction))
                return false;
        }

        $this->set('date_updated', date('Y-m-d H:i:s'));
        $this->set('editor_id', User::getInstance()->getId());

        $store  = $this->model->getStore();

        if($this->logger){
            $store->setLog($this->logger);
        }

        $version = $store->addVersion($this , $useTransaction);

        if($version){
            $this->version = $version;
            $this->commitChanges();
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get Access control List
     * @return Record\Acl | false
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Set insert id for object (Should not exist in the database)
     * @param int $id
     */
    public function setInsertId($id)
    {
        $this->insertId = $id;
    }

    /**
     * Get insert ID
     * @return integer
     */
    public function getInsertId()
    {
        return $this->insertId;
    }

    /**
     * Check DB object class
     * @param string $name
     * @return bool
     */
    public function isInstanceOf(string $name):bool
    {
        $name = strtolower($name);
        return $name === $this->getName();
    }
}
