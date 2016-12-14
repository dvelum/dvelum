<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2016  Kirill A Egorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Orm;
use Dvelum\Config;
use Dvelum\Model;
use Psr\Log\LogLevel;
/**
 * Database Object class. ORM element.
 * @author Kirill Egorov 2011  DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @package Db_Object
 * @uses  Config , Model , Data_Filter , Zend_Db_Adapter , Utils
 */
class Object
{
    /**
     * Error log adapter
     * @var Log
     */
    static protected $log = false;

    protected $name;
    /**
     * @var Object\Config
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
     * @var Object\Acl
     */
    protected $acl = false;
    /**
     * System flag. Disable ACL create permissions check
     * @var bool
     */
    static protected $disableAclCheck = false;

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
    public function __construct($name, $id = false)
    {
        $this->name = strtolower($name);
        $this->id = $id;

        $this->config = Object\Config::factory($name);
        $this->primaryKey = $this->config->getPrimaryKey();
        $this->model = \Dvelum\Model::factory($name);
        $this->acl = $this->config->getAcl();

        if($this->id){
            $this->checkCanRead();
            $this->loadData();
        }else{
            if($this->acl && !static::$disableAclCheck) {
                $this->checkCanCreate();
            }
        }
    }

    /**
     * Load object data
     * @throws Exception
     */
    protected function loadData()
    {
        $data =  $this->model->getItem($this->id);

        if(empty($data))
            throw new Exception('Cannot find object '.$this->name.':'.$this->id);

        $links = $this->config->getLinks([Object\config::LINK_OBJECT_LIST]);

        if(!empty($links))
        {
            foreach($links as $object => $fields)
            {
                foreach($fields as $field=>$linkType)
                {
                    if($this->config->isManyToManyLink($field)){
                        $relationsObject = $this->config->getRelationsObject($field);
                        $relationsData = Model::factory($relationsObject)->getList(
                            ['sort'=>'order_no', 'dir' =>'ASC'],
                            ['sourceid'=>$this->id],
                            ['targetid']
                        );
                    }else{
                        $linkedObject = $this->config->getLinkedObject($field);
                        $linksObject = Model::factory($linkedObject)->getStore()->getLinksObjectName();
                        $linksModel = Model::factory($linksObject);
                        $relationsData = $linksModel->getList(
                            ['sort'=>'order','dir'=>'ASC'],
                            [
                                'src' => $this->name,
                                'srcid' => $this->id,
                                'src_field' =>$field,
                                'target' => $linkedObject
                            ],
                            ['targetid']
                        );
                    }
                    if(!empty($relationsData)){
                        $data[$field] = Utils::fetchCol('targetid',$relationsData);
                    }
                }
            }
        }

        $this->_setRawData($data);
    }

    /**
     * Set raw data from storage
     * @param array $data
     */
    protected function _setRawData(array $data)
    {
        unset($data[$this->primaryKey]);
        $iv = false;
        $ivField = false;
        if($this->config->hasEncrypted()){
            $ivField = $this->config->getIvField();
            if(isset($data[$ivField]) && !empty($data[$ivField]))
                $iv = base64_decode($data[$ivField]);
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
                if(!empty($iv)){
                    $value = $this->config->decrypt($value, $iv);
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
    public function getFields()
    {
        return array_keys($this->config->get('fields'));
    }

    /**
     * Get the object data, returns the associative array ‘field name’
     * @param boolean $withUpdates, optional default true
     * @return array
     */
    public function getData($withUpdates = true)
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get object identifier
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the full name of the database storing the object data (with prefix)
     * @return string
     */
    public function getTable()
    {
        return $this->model->table();
    }

    /**
     * Check if there are object property changes
     * not saved in the database
     * @return boolean
     */
    public function hasUpdates()
    {
        return !empty($this->updates);
    }

    /**
     * Get ORM configuration object (data structure helper)
     * @return Object\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get updated, but not saved object data
     * @return array
     */
    public function getUpdates()
    {
        if($this->acl)
            $this->checkCanRead();

        return $this->updates;
    }

    /**
     * Set the object identifier (existing DB ID)
     * @param integer $id
     */
    public function setId($id)
    {
        if($this->acl && !static::$disableAclCheck)
            $this->checkCanEdit();

        $this->id = (integer) $id;
    }

    /**
     * Commit the object data changes (without saving)
     * @return void
     */
    public function commitChanges()
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
     * @return boolean
     */
    public function fieldExists($name)
    {
        return $this->config->fieldExists($name);
    }

    /**
     * Get the related object name for the field
     * (available if the object field is a link to another object)
     * @param string $field - field name
     * @return string
     */
    public function getLinkedObject($field)
    {
        if(!$this->config->isLink($field))
            return false;

        $cfg = $this->config->getFieldConfig($field);
        return $cfg['linkconfig']['object'];
    }

    /**
     * Validate link field
     * @param string $name
     * @param mixed $value
     * @return boolean
     */
    protected  function _validateLink($name , $value)
    {
        $propConf = $this->config->getFieldConfig($name);
        switch ($propConf['linkconfig']['link_type'])
        {
            case 'object':
            case 'multi' :
                return self::objectExists($propConf['linkconfig']['object'], $value);
                break;
            case 'dictionary':
                $dictionary = Dictionary::factory($propConf['linkconfig']['object']);
                return $dictionary->isValidKey($value);
                break;
        }
        return false;
    }

    /**
     * Check if the listed objects exist
     * @param string $name
     * @param mixed integer/array $ids
     * @return boolean
     */
    static public function objectExists($name , $ids)
    {
        if(!Object\Config::configExists($name))
            return false;

        try {
            $cfg = Object\Config::factory($name);
        }catch (Exception $e){
            return false;
        }

        if(!is_array($ids))
            $ids = array($ids);

        $model = Model::factory($name);
        $data = $model->getItems($ids);

        if(empty($data))
            return false;

        $data = Utils::fetchCol($cfg->getPrimaryKey(), $data);

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
    public function setValues(array $values)
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
    public function set($name , $value)
    {
        if($this->acl)
            $this->checkCanEdit();

        $propConf = $this->config->getFieldConfig($name);
        $validator = $this->getConfig()->getValidator($name);

        $field = $this->getConfig()->getField($name);

        // Validate value using special validator
        // Skip validation if value is null and object field can be null
        if ($validator && (!$field->isNull() || !is_null($value)) && !call_user_func_array([$validator, 'validate'], [$value])){
            throw new Exception('Invalid value for field ' . $name);
        }

        /*
         * Validate value by fields type in config
         */
        if($field->isMultiLink())
        {
            if(is_array($value) && !empty($value[0])){
                if(!$this->_validateLink($name , $value))
                    throw new Exception('Invalid property value');

            } else {
                $value = [];
            }
        }
        elseif ($field->isDictionaryLink())
        {
            if($field->isRequired() && !strlen($value))
                throw new Exception('Field '. $name.' cannot be empty');

            if(strlen($value))
            {
                $fieldConfig = $this->config->getFieldConfig($name);
                $dictionary = Dictionary::factory($fieldConfig['linkconfig']['object']);

                if(!$dictionary->isValidKey($value))
                    throw new Exception('Invalid dictionary value ['.$name.']');
            }
        }
        elseif ($field->isLink())
        {
            if(is_object($value)){
                if($value instanceof Object)
                {
                    if($field->isObjectLink())
                    {
                        if(!$value->isInstanceOf($this->getLinkedObject($name))){
                            throw new Exception('Invalid value type for field '. $name.' expects ' . $this->getLinkedObject($name) . ', '.$value->getName().' passed');
                        }
                    }
                    $value = $value->getId();
                }else{
                    $value = $value->__toString();
                }
            }

            if(is_array($value))
                throw new Exception('Invalid value for field '. $name);

            if($field->isRequired() && !strlen($value))
                throw new Exception('Field '. $name.' cannot be empty');

            $value = intval($value);

            if($value != 0 && !$this->_validateLink($name, $value))
                throw new \Exception('Invalid value for field '. $name);

            if($value == 0)
                $value = null;

        }
        // mysql strict mode patch
        elseif($field->isBoolean())
        {
            $value = intval((boolean)$value);
        }
        elseif (is_null($value) && $field->isNull())
        {
            $value = null;
        }
        else
        {
            $value = Object\Field\Property::filter($propConf, $value);
        }

        if(isset($propConf['db_len']) && $propConf['db_len']){
            if(mb_strlen((string)$value ,'UTF-8') > $propConf['db_len'])
                throw new Exception('The field value exceeds the allowable length ['.$name.']');
            if($propConf['db_type'] == 'bit' && (strlen($value) > $propConf['db_len'] || strlen($value) < $propConf['db_len']))
                throw new Exception('Invalid length for bit value ['.$name.']');
        }

        if(isset($this->data[$name]))
        {
            if($this->getConfig()->isBoolean($name) && intval($this->data[$name]) === intval($value) )
            {
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
    public function __set($key , $value)
    {
        if($key===$this->primaryKey)
            $this->setId($value);
        else
            $this->set($key, $value);
    }

    public function __isset($key)
    {
        if($key === $this->primaryKey)
            return isset($this->id);

        if(!isset($this->data[$key]) && !isset($this->updates[$key]))
            return false;

        return true;
    }
    /**
     * @param string $key
     * @throws \Exception
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
     * @throws \Exception
     * @return mixed
     */
    public function get($name)
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
     * @throws \Exception
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
            }catch (\Exception $e){
                $this->errors[] = $e->getMessage();

                if(self::$log)
                    self::$log->log(LogLevel::ERROR , $e->getMessage());
                return false;
            }
        }

        $store  = $this->model->getStore();
        if(self::$log)
            $store->setLog(self::$log);

        if($this->config->isReadOnly())
        {
            $text = 'ORM :: cannot save readonly object '. $this->config->getName();
            $this->errors[] = $text;
            if(self::$log)
                self::$log->log(LogLevel::ERROR, $text);
            return false;
        }

        if($this->config->hasEncrypted()){
            $ivField = $this->config->getIvField();
            $ivData = $this->get($ivField);
            if(empty($ivData)){
                $this->set($ivField , base64_encode($this->config->createIv()));
            }
        }

        $emptyFields = $this->_hasRequired();
        if($emptyFields!==true)
        {
            $text = 'ORM :: Fields can not be empty. '.$this->getName().' ['.implode(',', $emptyFields).']';
            $this->errors[] = $text;
            if(self::$log)
                self::$log->log(LogLevel::ERROR, $text);
            return false;
        }

        $values = $this->validateUniqueValues();

        if(!empty($values))
        {
            foreach($values as $k => $v)
            {
                $text = 'The Field value should be unique '.$k . ':' . $v;
                $this->errors[] = $text;
            }

            if(self::$log)
                self::$log->log(LogLevel::ERROR, $this->getName() . ' ' . implode(', ' , $this->errors));

            return false;
        }

        try {
            if(!$this->getId()){

                if($this->config->isRevControl()){
                    $this->date_created = date('Y-m-d H:i:s');
                    $this->date_updated = date('Y-m-d H:i:s');
                    $this->authorid = User::getInstance()->id;
                }

                $id = $store->insert($this , $useTransaction);
                $this->setId($id);
                $this->commitChanges();
                return (integer) $id;
            } else {

                if($this->config->isRevControl()){
                    $this->date_updated = date('Y-m-d H:i:s');
                    $this->editorid = User::getInstance()->getId();
                }
                $id = (integer) $store->update($this , $useTransaction);
                $this->commitChanges();
                return $id;
            }
        }catch (Exception $e){
            $this->errors[] = $e->getMessage();
            if(self::$log)
                self::$log->log(LogLevel::ERROR, $e->getMessage());
            return false;
        }
    }

    /**
     * Deleting an object
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * If data update in your code is carried out within an external transaction
     * set the value to  false,
     * otherwise, the first update will lead to saving the changes
     * @return boolean - success
     */
    public function delete($useTransaction = true) : bool
    {
        if($this->acl){
            try{
                $this->checkCanDelete();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if(self::$log)
                    self::$log->log(LogLevel::ERROR, $e->getMessage());
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
    public function serializeLinks($data) : array
    {
        foreach ($data as $k=>$v)
        {
            if($this->config->getField($k)->isMultiLink($k)) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * Validate unique fields, object field groups
     * Returns array of errors  or null .
     * @property boolean $new
     * @return mixed array | null
     */
    public function validateUniqueValues()
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
        return $this->model->getTitle($this);
    }

    /**
     * Factory method of object creation is preferable to use, cf. method  __construct() description
     *
     * @param string $name
     * @param integer | integer[] | boolean $id, optional default false
     * @throws \Exception
     * @return Object | Object[]
     */
    static public function factory(string $name , $id = false)
    {
        if(!is_array($id))
            return new static($name , $id);

        $list = [];
        $model = Model::factory($name);
        $data = $model->getItems($id);

        static::$disableAclCheck = true;

        $config = Object\Config::factory($name);

        /*
         * Load links info
         */
        $links = $config->getLinks([Object\Config::LINK_OBJECT_LIST]);
        $linksData = [];

        if(!empty($links))
        {
            foreach($links as $object => $fields)
            {
                foreach($fields as $field=>$linkType)
                {
                    if($config->isManyToManyLink($field)){
                        $relationsObject = $config->getRelationsObject($field);
                        $relationsData = Model::factory($relationsObject)->getList(
                            ['sort'=>'order_no', 'dir' =>'ASC'],
                            ['sourceid'=>$id],
                            ['targetid','sourceid']
                        );
                    }else{
                        $linkedObject = $config->getLinkedObject($field);
                        $linksObject = Model::factory($linkedObject)->getStore()->getLinksObjectName();
                        $linksModel = Model::factory($linksObject);
                        $relationsData = $linksModel->getList(
                            ['sort'=>'order','dir'=>'ASC'],
                            [
                                'src' => $name,
                                'srcid' => $id,
                                'src_field' => $field,
                                'target' => $linkedObject
                            ],
                            ['targetid','sourceid'=>'srcid']
                        );
                    }
                    if(!empty($relationsData)){
                        $linksData[$field] = Utils::groupByKey('sourceid',$relationsData);
                    }
                }
            }
        }

        foreach ($data as $item)
        {
            $o = static::factory($name);
            /*
             * Apply links info
             */
            if(!empty($linksData)){
                foreach($linksData as $field => $source){
                    if(isset($source[$item[$o->primaryKey]])){
                        $item[$field] = Utils::fetchCol('targetid' , $source[$item[$o->primaryKey]]);
                    }
                }
            }

            $o->setId($item[$o->primaryKey]);
            $o->_setRawData($item);
            $list[$item[$o->primaryKey]] = $o;
        }
        static::$disableAclCheck = false;
        return $list;
    }

    /**
     * Enable error log. Set log adapter
     * @param \Log $log
     */
    static public function setLog(\Log $log)
    {
        self::$log = $log;
    }

    /**
     * Check for required fields
     * @return boolean|array
     */
    protected function _hasRequired()
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
    public function getErrors()
    {
        return $this->errors;
    }

    protected function checkCanRead()
    {
        if($this->acl && !$this->acl->canRead($this))
            throw new \Exception('You do not have permission to view data in this object ['.$this->getName().':'.$this->getId().'].');
    }

    protected function checkCanEdit()
    {
        if($this->acl && !$this->acl->canEdit($this))
            throw new \Exception('You do not have permission to edit data in this object ['.$this->getName().':'.$this->getId().'].');
    }

    protected function checkCanDelete()
    {
        if($this->acl && !$this->acl->canDelete($this))
            throw new \Exception('You do not have permission to delete this object ['.$this->getName().':'.$this->getId().'].');
    }

    protected function checkCanCreate()
    {
        if($this->acl && !$this->acl->canCreate($this))
            throw new \Exception('You do not have permission to create object ['.$this->getName().'].');
    }

    protected function checkCanPublish()
    {
        if($this->acl && !$this->acl->canPublish($this))
            throw new \Exception('You do not have permission to publish object ['.$this->getName().'].');
    }

    /**
     * Unpublish VC object
     * @param boolean $log  - log changes
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * @return boolean
     */
    public function unpublish($log = true , $useTransaction = true)
    {
        if($this->acl){
            try{
                $this->checkCanPublish();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if(self::$log)
                    self::$log->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }

        $store  = $this->model->getStore();
        if(self::$log)
            $store->setLog(self::$log);

        $this->publishedversion = 0;
        $this->published = false;
        $this->date_updated = date('Y-m-d H:i:s');
        /**
         * @todo refactor
         */
        $this->editorid = \Dvelum\App\Session\User::factory()->getId();

        return $store->unpublish($this , $log , $useTransaction);
    }

    /**
     * Publish VC object
     * @param bool|int $version - optional, default current version
     * @param boolean $log - log changes
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * @return bool
     * @throws Exception
     */
    public function publish($version = false , $log = true , $useTransaction = true)
    {
        if($this->acl){
            try{
                $this->checkCanPublish();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if(self::$log)
                    self::$log->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }

        $store  = $this->model->getStore();

        if(self::$log)
            $store->setLog(self::$log);

        if($version && $version !== $this->getVersion())
        {
            try{
                $this->loadVersion($version);
            }
            catch (Exception $e)
            {
                $this->errors[] = $e->getMessage();

                if(self::$log)
                    self::$log->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }

        $this->published = true;
        $this->date_updated = date('Y-m-d H:i:s');
        $this->editorid = \Dvelum\App\Session\User::factory()->getId();

        if(empty($this->date_published))
            $this->set('date_published' , date('Y-m-d H:i:s'));

        $this->publishedversion = $this->getVersion();
        return $store->publish($this , $log , $useTransaction);
    }

    /**
     * Get loaded version
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Load version
     * @param integer $vers
     * @return boolean
     * @throws Exception
     */
    public function loadVersion($vers)
    {
        $this->rejectChanges();
        $versionObject  = $this->model->getStore()->getVersionObjectName();

        $vc = Model::factory($versionObject);

        $data = $vc->getData($this->getName() , $this->getId() , $vers);

        $pKey = $this->config->getPrimaryKey();

        if(isset($data[$pKey]))
            unset($data[$pKey]);

        if(empty($data))
            throw new Exception('Cannot load version for ' . $this->getName() . ':' . $this->getId() . '. v:' . $vers);

        $iv = false;
        $ivField = false;
        if($this->config->hasEncrypted()){
            $ivField = $this->config->getIvField();
            if(isset($data[$ivField]) && !empty($data[$ivField]))
                $iv = base64_decode($data[$ivField]);
        }

        foreach($data as $k => $v)
        {
            if($this->fieldExists($k))
            {
                try{

                    if($this->config->isEncrypted($k)){
                        if(!empty($iv)){
                            $v = $this->config->decrypt($v, $iv);
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
    public function rejectChanges()
    {
        $this->updates = [];
    }

    /**
     * Save object as new version
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * @return boolean
     */
    public function saveVersion($useTransaction = true)
    {
        if(!$this->config->isRevControl()){
            return $this->save($useTransaction);
        }

        if($this->config->hasEncrypted()){
            $ivField = $this->config->getIvField();
            $ivData = $this->get($ivField);
            if(empty($ivData)){
                $this->set($ivField , base64_encode($this->config->createIv()));
            }
        }

        if($this->acl)
        {
            try{
                $this->checkCanEdit();
            }catch (Exception $e){
                $this->errors[] = $e->getMessage();

                if(self::$log)
                    self::$log->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
        }

        if(!$this->getId())
        {
            $this->published = false;
            $this->authorid = User::getInstance()->getId();

            if(!$this->save(true , $useTransaction))
                return false;
        }

        $this->date_updated = date('Y-m-d H:i:s');
        $this->editorid = User::getInstance()->getId();

        $store  = $this->model->getStore();

        if(self::$log)
            $store->setLog(self::$log);

        $vers = $store->addVersion($this , $useTransaction);

        if($vers){
            $this->version = $vers;
            $this->commitChanges();
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get Access control List
     * @return Object\Acl | false
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
     * @param $name
     * @return boolean
     */
    public function isInstanceOf($name)
    {
        $name = strtolower($name);
        return $name === $this->getName();
    }
}
