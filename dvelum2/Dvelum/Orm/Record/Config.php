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

use Dvelum\Orm;
use Dvelum\Security\CryptServiceInterface;
use Dvelum\Service;
use Dvelum\Config as Cfg;
use Dvelum\Orm\Model;
use Dvelum\Orm\Exception;


/**
 * Orm Object structure config
 * @package Db
 * @subpackage Db_Object
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2016  Kirill A Egorov,
 * @license General Public License version 3
 */
class Config
{
    const LINK_OBJECT = 'object';
    const LINK_OBJECT_LIST = 'multi';
    const LINK_DICTIONARY = 'dictionary';

    const RELATION_MANY_TO_MANY = 'many_to_many';

    const SHARDING_TYPE_GLOABAL_ID = 'global_id';
    const SHARDING_TYPE_KEY = 'sharding_key';
    const SHARDING_TYPE_KEY_NO_INDEX = 'sharding_key_no_index';
    const SHARDING_TYPE_VIRTUAL_BUCKET = 'virtual_bucket';
    /**
     * @var Cfg\ConfigInterface $settings
     */
    protected $settings;

    /**
     * @var Cfg\ConfigInterface
     */
    protected $config;

    /**
     * Additional fields config for objects under rev. control
     * @var array
     */
    static protected $vcFields;

    /**
     * List of system fields used for encryption
     * @var array
     */
    static protected $cryptFields;

    /**
     * List of system fields used for sharding
     * @var array
     */
    protected $distributedFields;

    /**
     * @var string $name
     * @return Orm\Record\Config
     */
    protected $name;

    /**
     * Translation adapter
     * @var Orm\Record\Config\Translator | bool
     */
    protected $translator = false;

    /**
     * Translation flag
     * @var bool
     */
    protected $translated = false;

    /**
     * Database table prefix
     * @var string
     */
    protected $dbPrefix;

    protected $localCache = [];

    /**
     * Access Control List
     * @var Acl | bool
     */
    protected $acl = false;

    /**
     * @var CryptServiceInterface
     */
    private $cryptService = null;
    /**
     * @var callable|null $cryptServiceLoader
     */
    protected $cryptServiceLoader = null;

    /**
     * Instantiate data structure for the objects named $name
     * @param string $name - object name
     * @param boolean $force - reload config
     * @return Orm\Record\Config
     * @deprecated
     * @throws Exception
     */
    static public function getInstance($name , $force = false)
    {
        /**
         * @var \Dvelum\Orm\Service $service
         */
        $service = Service::get('orm');
        return $service->config($name, $force);
    }

    /**
     * Instantiate data structure for the objects named $name
     * Backward compatibility
     * @param string $name - object name
     * @param boolean $force - reload config
     * @return Orm\Record\Config
     * @throws Exception
     */
    static public function factory(string $name , bool $force = false) : Orm\Record\Config
    {
        /**
         * Runtime call optimization
         * @var \Dvelum\Orm\Service $service
         */
        static $service = false;
        if(empty($service)){
            $service = Service::get('orm');
        }
        return $service->config($name, $force);
    }

    /**
     * Reload object Properties
     */
    public function reloadProperties()
    {
        $this->localCache = [];
        $this->loadProperties();
    }


    public function __construct($name , Cfg\ConfigInterface $settings, $force = false)
    {
        $this->settings = $settings;
        $this->name = strtolower($name);

        if(!self::configExists($name))
            throw new Exception('Undefined object config '. $name);

        $path = $this->settings->get('configPath') . $name . '.php';

        $this->config = Cfg\Factory::storage()->get($path, !$force , false);
        $this->loadProperties();
    }


    /**
     * Object config existence check
     * @param string $name
     * @return boolean
     */
    static public function configExists(string $name) : bool
    {
        /**
         * @var \Dvelum\Orm\Service $service
         */
        $service = Service::get('orm');
        return $service->configExists($name);
    }


    /**
     * Get config files path
     * @return string
     */
    public function getConfigPath() : string
    {
        return $this->settings->get('configPath');
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
     * Get the full name of the database table that stores this object data (with prefix)
     * @param boolean  $withPrefix
     * @return string
     * @deprecated since 0.9.1
     */
    public function getTable($withPrefix = true)
    {
        return $withPrefix ? Model::factory($this->name)->table() : $this->config->get('table');
    }

    /**
     * Lazy loading for items translation
     */
    protected function prepareTranslation()
    {
        if($this->translated)
            return;

        $dataLink = & $this->config->dataLink();
        $translator = $this->getTranslator();
        $translator->translate($this->name , $dataLink);
        $this->translated = true;
    }

    /**
     * Prepare config, load system properties
     */
    protected function loadProperties()
    {
        $dataLink = & $this->config->dataLink();
        $pKeyName = $this->getPrimaryKey();

        if(!isset($dataLink['distributed']))
            $dataLink['distributed'] = false;


        $keyConfig = 'system/pk_field.php';

        if($this->isDistributed()){
            $shardingType = $this->getShardingType();
            switch ($shardingType)
            {
                case self::SHARDING_TYPE_KEY_NO_INDEX:
                    break;
                case self::SHARDING_TYPE_VIRTUAL_BUCKET:
                    // not using auto increment
                    if($this->getBucketMapperKey() == $pKeyName){
                        $keyConfig = 'distributed/pk_field.php';
                    }
                    break;
                default:
                    // not using autoincrement
                    $keyConfig = 'distributed/pk_field.php';
                    break;
            }
        }
        $dataLink['fields'][$pKeyName] = Cfg::storage()->get(
            $this->settings->get('configPath') . $keyConfig
        )->__toArray();

        /*
         * System index init
         */
        $dataLink['indexes']['PRIMARY'] = array(
            'columns'=>[$pKeyName],
            'fulltext'=>false,
            'unique'=>true,
            'primary'=>true,
            'system'=> true,
            // distributed objects does not use auto increment index
            'db_auto_increment'=>$dataLink['fields'][$pKeyName]['db_auto_increment'],
            'is_search' =>true,
            'lazyLang'=>true
        );

        /*
         * Set readonly connection
         */
        if(!isset($dataLink['slave_connection']) || empty($dataLink['slave_connection']))
            $dataLink['slave_connection'] = $dataLink['connection'];

        /*
         * Load additional fields for object under revision control
         */
        if(isset($dataLink['rev_control']) && $dataLink['rev_control'])
            $dataLink['fields'] = array_merge($dataLink['fields'] , $this->getVcFields());

        /**
         * Load additional encryption fields
         */
        if($this->hasEncrypted())
            $dataLink['fields'] = array_merge($dataLink['fields'] , $this->getEncryptionFields());


        if((isset($dataLink['distributed']) && $dataLink['distributed']) || $this->isIndexObject()){
            $dataLink['fields'] = array_merge($dataLink['fields'], $this->getDistributedFields());
        }

        if($this->isIndexObject()){
            $dataLink['indexes'] = $this->initIndexIndexes();
        }

        /*
         * Init ACL adapter
         */
        if(!empty($dataLink['acl']))
            $this->acl = Orm\Record\Acl::factory($dataLink['acl']);

    }

    /**
     * Get Version control fields
     * @return array
     */
    protected function getVcFields() : array
    {
        if(!isset(self::$vcFields))
            self::$vcFields = Cfg\Factory::storage()->get($this->settings->get('configPath').'vc/vc_fields.php')->__toArray();

        return self::$vcFields;
    }

    /**
     * Get encryption fields
     * @return array
     */
    protected function getEncryptionFields() : array
    {
        if(!isset(self::$cryptFields))
            self::$cryptFields = Cfg\Factory::storage()->get($this->settings->get('configPath').'enc/fields.php')->__toArray();

        return self::$cryptFields;
    }

    /**
     * Get a list of fields to be used for search
     * @return array
     */
    public function getSearchFields() : array
    {
        $fields = [];
        $fieldsConfig = $this->get('fields');

        foreach ($fieldsConfig as $k=>$v)
            if($this->getField($k)->isSearch())
                $fields[] = $k;
        return $fields;
    }

    /**
     * Get a configuration element by key (system method)
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if($key === 'fields' || $key === 'title')
            $this->prepareTranslation();

        return $this->config->get($key);
    }

    /**
     * Check if the object is using revision control
     * @return bool
     */
    public function isRevControl() : bool
    {
        return ($this->config->offsetExists('rev_control') && $this->config->get('rev_control'));
    }

    /**
     * Get a list of indices (from the configuration)
     * @param boolean $includeSystem -optional default = true
     * @return array
     */
    public function getIndexesConfig($includeSystem = true) : array
    {
        $list = [];
        if($this->config->offsetExists('indexes')) {
            foreach ($this->config->get('indexes') as $k=>$v){
                if(!$includeSystem && isset($v['system']) && $v['system']){
                    continue;
                }
                $list[$k] = $v;
            }
        }
        return $list;
    }

    /**
     * Get the field configuration
     * @param string $field
     * @throws Exception
     * @return array
     */
    public function getFieldConfig(string $field) : array
    {
        $this->prepareTranslation();

        if(!isset($this->config['fields'][$field]))
            throw new Exception('Invalid field name: ' . $field);

        return $this->config['fields'][$field];
    }

    /**
     * Get index config
     * @param string $index
     * @throws Exception
     * @return array
     */
    public function getIndexConfig($index) : array
    {
        $this->prepareTranslation();

        if(!isset($this->config['indexes'][$index]))
            throw new Exception('indexes Index name: ' . $index);

        return $this->config['indexes'][$index];
    }

    /**
     * Get the configuration of all fields
     * @param boolean $includeSystem -optional default = true
     * @return array
     */
    public function getFieldsConfig($includeSystem = true) : array
    {
        $this->prepareTranslation();

        if($includeSystem)
            return $this->config['fields'];

        $fields = $this->config['fields'];
        unset($fields[$this->getPrimaryKey()]);

        foreach($fields as $k=>$field){
            if(isset($field['system']) && $field['system']){
                unset($fields[$k]);
            }
        }
        return $fields;
    }

    /**
     * Get object fields
     * @return Config\Field[]
     */
    public function getFields() : array
    {
        $result = [];
        $config = $this->getFieldsConfig();
        foreach ($config as $name=>$cfg){
            $result[$name] = $this->getField($name);
        }
        return $result;
    }

    /**
     * Get the configuration of system fields
     * @return array
     */
    public function getSystemFieldsConfig() : array
    {
        $this->prepareTranslation();
        $primaryKey = $this->getPrimaryKey();
        $fields = [];

        if($this->isRevControl())
            $fields = $this->getVcFields();

        if($this->hasEncrypted())
            $fields = array_merge($fields , $this->getEncryptionFields());

        $fields[$primaryKey] = $this->config['fields'][$primaryKey];

        return $fields;
    }

    /**
     * Get a list of fields linking to external objects
     * @param array $linkTypes  - optional link type filter
     * @param boolean $groupByObject - group field by linked object, default true
     * @return array  [objectName=>[field => link_type]] | [field =>["object"=>objectName,"link_type"=>link_type]]
     */
    public function getLinks($linkTypes = [Orm\Record\Config::LINK_OBJECT, Orm\Record\Config::LINK_OBJECT_LIST], $groupByObject = true) : array
    {
        $data = [];
        $fields = $this->getFieldsConfig(true);
        foreach ($fields as $name=>$cfg)
        {
            if(isset($cfg['type']) && $cfg['type']==='link'
                && isset($cfg['link_config']['link_type'])
                && in_array($cfg['link_config']['link_type'], $linkTypes , true)
                && isset($cfg['link_config']['object'])
            ){


                if($groupByObject)
                    $data[$cfg['link_config']['object']][$name] = $cfg['link_config']['link_type'];
                else
                    $data[$name] = ['object'=>$cfg['link_config']['object'],'link_type'=>$cfg['link_config']['link_type']];
            }
        }
        return $data;
    }


    /**
     * Check if the object uses history log
     * @return bool
     */
    public function hasHistory() : bool
    {
        if($this->config->offsetExists('save_history') && $this->config->get('save_history'))
            return true;
        else
            return false;
    }

    /**
     * Check if the object uses extended history log
     * @return bool
     */
    public function hasExtendedHistory() : bool
    {

        if (
            $this->config->offsetExists('save_history')
            &&
            $this->config->get('save_history')
            &&
            $this->config->offsetExists('log_detalization')
            &&
            $this->config->get('log_detalization') === 'extended'
        ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Check if object has db prefix
     * @return bool
     */
    public function hasDbPrefix() : bool
    {
        return $this->config->get('use_db_prefix');
    }


    /**
     * Check if the field is present in the description
     * @param string $field
     * @return bool
     */
    public function fieldExists(string $field) : bool
    {
        return isset($this->config['fields'][$field]);
    }

    /**
     * Check if Index exists
     * @param string $index
     * @return bool
     */
    public function indexExists($index) : bool
    {
        return isset($this->config['indexes'][$index]);
    }

    /**
     * Get the name of the class, which is the field validator
     * @param string  $field
     * @throws Exception
     * @return mixed  string class name / boolean false
     */
    public function getValidator($field)
    {
        if(!$this->fieldExists($field))
            throw new Exception('Invalid property name');

        if(isset($this->config['fields'][$field]['validator']) && !empty($this->config['fields'][$field]['validator']))
            return $this->config['fields'][$field]['validator'];
        else
            return false;
    }

    /**
     * Convert into array
     * @return array
     */
    public function __toArray() : array
    {
        $this->prepareTranslation();
        return $this->config->__toArray();
    }

    /**
     * Get the title for the object
     * @return string
     */
    public function getTitle() : string
    {
        $this->prepareTranslation();
        return $this->config['title'];
    }

    /**
     * Set object title
     * @param string $title
     * @return void
     */
    public function setObjectTitle($title) : void
    {
        $this->prepareTranslation();
        $this->config['title'] = $title;
    }

    /**
     * Get the name of the field linking to this object and used as a text representation
     * @return string
     */
    public function getLinkTitle() : string
    {
        $this->prepareTranslation();

        if(isset($this->config['link_title']) && !empty($this->config['link_title']))
            return $this->config['link_title'];
        else
            return $this->getPrimaryKey();
    }
    /**
     * Check if object is readonly
     * @return bool
     */
    public function isReadOnly() : bool
    {
        return $this->config->get('readonly');
    }

    /**
     * Check if object structure is locked
     * @return bool
     */
    public function isLocked() : bool
    {
        return $this->config->get('locked');
    }

    /**
     * Check if there are transactions available for this type of objects
     * @return bool
     */
    public function isTransact() : bool
    {
        if(strtolower($this->config->get('engine'))=='innodb')
            return true;
        else
            return false;
    }

    /**
     * Save the object configuration
     */
    public function save() : bool
    {
        $fields = $this->getFieldsConfig(false);
        $indexes = $this->getIndexesConfig(false);

        $config = clone $this->config;
        $translator = $this->getTranslator();

        $translation = $translator->getTranslation($this->getName(), true);
        $translation['title'] = $this->config->get('title');

        foreach ($fields as $field =>& $cfg)
        {
            $translation['fields'][$field] = $cfg['title'];
            unset($cfg['title']);
        } unset($cfg);

        $config->set('fields', $fields);
        $config->set('indexes' , $indexes);
        $config->offsetUnset('title');

        if($this->isDistributed()){
            $config->set('distributed_indexes',  $this->getDistributedIndexesConfig(false));
        }

        try{
            $translator->save($this->getName() , $translation);
        }catch (\Exception $e){
            return false;
        }
        return Cfg::storage()->save($config);
    }

    /**
     * Replace configuration data with an array
     * @param array $data
     * @return void
     */
    public function setData(array $data) : void
    {
        $this->config->setData($data);
    }

    /**
     * Get configuration as array
     * @return array
     */
    public function getData() : array
    {
        return $this->config->__toArray();
    }

    /**
     * Configure the field
     * @param string $field
     * @param array $config
     */
    public function setFieldConfig(string $field , array $config) : void
    {
        $cfg = & $this->config->dataLink();
        $cfg['fields'][$field] = $config;
    }

    /**
     * Update field link, set linked object name
     * @param string $field
     * @param string $linkedObject
     * @return bool
     */
    public function setFieldLink(string $field , string $linkedObject) : bool
    {
        if(!$this->getField($field)->isLink())
            return false;

        $cfg = & $this->config->dataLink();
        $cfg['fields'][$field]['link_config']['object'] = $linkedObject;
        return true;
    }

    /**
     * Configure the index
     * @param string $index
     * @param array $config
     * @return void
     */
    public function setIndexConfig($index , array $config) : void
    {
        $indexes = $this->getIndexesConfig();
        $indexes[$index] = $config;
        $this->config->set('indexes', $indexes);
    }

    /**
     * Configure distributed index
     * @param string $index
     * @param array $config
     */
    public function setDistributedIndexConfig(string $index, array $config)
    {
        $indexes = $this->getDistributedIndexesConfig();
        $indexes[$index] = $config;
        $this->config->set('distributed_indexes', $indexes);
    }

    /**
     * Init indexes for distributed index object
     * @return array
     */
    protected function initIndexIndexes() : array
    {
        $list = $this->config->get('indexes');
        $shardingField = Cfg::storage()->get('sharding.php')->get('shard_field');

        $list[$shardingField] = [
            'columns'=>[$shardingField],
            'fulltext'=>false,
            'unique'=>false,
            'primary'=>false,
            'db_auto_increment'=> false,
            'is_search' =>false,
            'lazyLang'=>false,
            'system'=>true
        ];

        $dataObject = Config::factory($this->getDataObject());
        $dataIndexes = $dataObject->getIndexesConfig();
        $currentFields = $this->getFields();

        foreach ($currentFields as $field)
        {
            $fieldName = $field->getName();
            if(isset($list[$fieldName]) || $fieldName ==$this->getPrimaryKey()){
                continue;
            }
            if(isset($dataIndexes[$fieldName]) && count($dataIndexes[$fieldName]['columns']) == 1 && $dataIndexes[$fieldName]['columns'][0]==$fieldName){
                $list[$fieldName] = $dataIndexes[$fieldName];
            }else{
                $list[$fieldName] =  [
                    'columns'=>[$fieldName],
                    'fulltext'=>false,
                    'unique'=>false,
                    'primary'=>false,
                    'db_auto_increment'=> false,
                    'is_search' =>true,
                    'lazyLang'=>false,
                    'system'=>true
                ];
            }
            $list[$fieldName]['system'] = true;
        }
        return $list;
    }
    /**
     * Get list of distributed indexes
     * @param bool $includeSystem
     * @return array
     */
    public function getDistributedIndexesConfig(bool $includeSystem = true) : array
    {
        if(!$this->isDistributed()) {
            return [];
        }

        $list = [];

        if($this->config->offsetExists('distributed_indexes')){
            $list = $this->config->get('distributed_indexes');
        }

        // Set Required Indexes
        if($includeSystem)
        {
            $shardingField = Cfg::storage()->get('sharding.php')->get('shard_field');
            $primaryKey = $this->getPrimaryKey();
            $list[$primaryKey] = [
                'field'=> $primaryKey,
                'is_system'=> true,
            ];
            $list[$shardingField] = ['field'=>$shardingField,'is_system'=>true];
            $distributedKey = $this->getShardingKey();
            if(!empty($distributedKey) && $distributedKey!== $primaryKey){
                $unique = false;
                $type = $this->getShardingType();
                if($type === self::SHARDING_TYPE_KEY_NO_INDEX || $type === self::SHARDING_TYPE_VIRTUAL_BUCKET){
                    $unique = true;
                }
                $list[$distributedKey] = ['field'=>$distributedKey,'is_system'=>true,'unique'=>$unique];
            }
        }
        return $list;
    }

    /**
     * Rename field and rebuild the database table
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    public function renameField(string $oldName , string $newName) : bool
    {
        $fields = $this->getFieldsConfig();
        $fields[$newName] = $fields[$oldName];
        unset($fields[$oldName]);

        $this->config->set('fields', $fields);
        $indexes = $this->getIndexesConfig();
        /**
         * Check for indexes for field
         */
        foreach ($indexes as $index => &$config)
        {
            if(isset($config['columns']) && !empty($config['columns']))
            {
                /*
                 * Rename index link
                 */
                foreach ($config['columns'] as $id => &$value){
                    if($value === $oldName){
                        $value = $newName;
                    }
                }unset($value);
            }
        }
        $this->config->set('indexes', $indexes);
        $builder = Orm\Record\Builder::factory($this->getName() , false);
        if(!$builder->renameField($oldName , $newName)){
            return false;
        }
        return $this->save();
    }

    /**
     * Remove field
     * @param string $name
     */
    public function removeField(string $name) : void
    {
        $fields = $this->getFieldsConfig();

        if(!isset($fields[$name]))
            return;

        unset($fields[$name]);
        $this->config->set('fields' , $fields);

        $indexes = $this->getIndexesConfig();
        /**
         * Check for indexes for field
         */
        foreach ($indexes as $index => &$config)
        {
            if(isset($config['columns']) && !empty($config['columns']))
            {
                /*
                 * Remove field from index
                 */
                foreach ($config['columns'] as $id=>$value){
                    if($value === $name)
                        unset($config['columns'][$id]);
                }
                /*
                 * Remove empty index
                 */
                if(empty($config['columns']))
                    unset($indexes[$index]);

            }
        }
        $this->config->set('indexes', $indexes);
    }

    /**
     * Delete index
     * @param string $name
     * @return void
     */
    public function removeIndex(string $name) : void
    {
        $indexes = $this->getIndexesConfig();
        if(!isset($indexes[$name]))
            return;
        unset($indexes[$name]);
        $this->config->set('indexes' , $indexes);
    }

    /**
     * Get Config object
     * @return Cfg\ConfigInterface
     */
    public function getConfig() : Cfg\ConfigInterface
    {
        return $this->config;
    }

    /**
     * Check if object is system defined
     * @return bool
     */
    public function isSystem() : bool
    {
        $link = & $this->config->dataLink();
        if(isset($link['system']) && $this->config['system'])
            return true;
        else
            return false;
    }

    /**
     * Check system field
     * @param string $field
     * @return bool
     */
    public function isSystemField(string $field) : bool
    {
        if($field === $this->getPrimaryKey())
            return true;

        $sFields = $this->getVcFields();

        if(isset($sFields[$field]))
            return true;

        $encFields = $this->getEncryptionFields();
        if(isset($encFields[$field]))
            return true;

        $distributed = $this->getDistributedFields();
        if(isset($distributed[$field]))
            return true;

        return false;
    }

    /**
     * Get list of foreign keys
     * @return array
     * array(
     * 	array(
     *      'curDb' => string,
     * 		'curObject' => string,
     * 		'curTable' => string,
     *		'curField'=> string,
     *		'isNull'=> boolean,
     *		'toDb'=> string,
     *		'toObject'=> string,
     *		'toTable'=> string,
     *		'toField'=> string,
     *      'onUpdate'=> string
     *      'onDelete'=> string
     *   ),
     *  ...
     *  )
     */
    public function getForeignKeys() : array
    {
        if(!$this->canUseForeignKeys())
            return [];

        $curModel = Model::factory($this->getName());
        $curDb = $curModel->getDbConnection();
        $curDbCfg = $curDb->getConfig();

        $links = $this->getLinks([Orm\Record\Config::LINK_OBJECT]);

        if(empty($links))
            return [];

        $keys = [];
        foreach ($links as $object=>$fields)
        {
            $oConfig = static::factory($object);
            /*
             *  Only InnoDb implements Foreign Keys
             */
            if(!$oConfig->isTransact())
                continue;

            $oModel = Model::factory($object);
            /*
             * Foreign keys are only available for objects with the same database connection
             */
            if($curDb !== $oModel->getDbConnection())
                continue;

            foreach ($fields as $name=>$linkType)
            {
                $field  = $this->getField($name);

                if($field->isRequired())
                    $onDelete = 'RESTRICT';
                else
                    $onDelete = 'SET NULL';

                $keys[] = array(
                    'curDb' => $curDbCfg['dbname'],
                    'curObject' => $this->getName(),
                    'curTable' => $curModel->table(),
                    'curField'=>$name,
                    'toObject'=>$object,
                    'toTable'=>$oModel->table(),
                    'toField'=>$oConfig->getPrimaryKey(),
                    'toDb' => $curDbCfg['dbname'],
                    'onUpdate' => 'CASCADE',
                    'onDelete' => $onDelete
                );
            }
        }
        return $keys;
    }
    /**
     * Check if Foreign keys can be used
     * @return bool
     */
    public function canUseForeignKeys() : bool
    {
        if($this->config->offsetExists('disable_keys') && $this->config->get('disable_keys'))
            return false;

        if(!$this->isTransact())
            return false;

        return true;
    }

    /**
     * service stub
     * @return string
     */
    public function getPrimaryKey() : string
    {
        if(isset($this->localCache['primary_key']))
            return $this->localCache['primary_key'];

        $key = 'id';

        if($this->config->offsetExists('primary_key'))
        {
            $cfgKey = $this->config->get('primary_key');
            if(!empty($cfgKey))
                $key = $cfgKey;
        }

        $this->localCache['primary_key'] = $key;

        return $key;
    }

    /**
     * Inject translation adapter
     * @param Config\Translator $translator
     * @return void
     */
    public function setTranslator(Config\Translator $translator) :void
    {
        $this->translator = $translator;
    }

    /**
     * Get Translation adapter
     * @return Config\Translator
     */
    public function getTranslator() : Config\Translator
    {
        if(empty($this->translator)){
            $this->translator = $this->settings->get('translatorLoader')();
        }

        return $this->translator;
    }

    /**
     * Get Access Control Adapter
     * @return Orm\Record\Acl | bool false
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Check for encoded fields
     * @return bool
     */
    public function hasEncrypted() : bool
    {
        foreach ($this->config['fields'] as $config){
            if(isset($config['type']) && $config['type']=='encrypted')
                return true;
        }
        return false;
    }
    /**
     * Get names of encrypted fields
     * @return array
     */
    public function getEncryptedFields() : array
    {
        $fields = [];
        $fieldsConfig = $this->get('fields');

        foreach ($fieldsConfig as $k=>$v)
            if(isset($v['type']) && $v['type']==='encrypted')
                $fields[] = $k;

        return $fields;
    }


    /**
     * Get public key field
     * @return string
     */
    public function getIvField() : string
    {
        return $this->settings->get('ivField');
    }

    /**
     * Check if object has ManyToMany relations
     * @return bool
     */
    public function hasManyToMany() : bool
    {
        $relations = $this->getManyToMany();
        if(!empty($relations)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get manyToMany relations
     * @return array
     */
    public function getManyToMany() : array
    {
        $result = [];
        $fieldConfigs = $this->getFieldsConfig();
        foreach($fieldConfigs as $field=>$cfg)
        {
            if(isset($cfg['type']) && $cfg['type']==='link'
                && isset($cfg['link_config']['link_type'])
                && $cfg['link_config']['link_type'] == self::LINK_OBJECT_LIST
                && isset($cfg['link_config']['object'])
                && isset($cfg['link_config']['relations_type'])
                && $cfg['link_config']['relations_type'] == self::RELATION_MANY_TO_MANY
            ){
                $result[$cfg['link_config']['object']][$field] = self::RELATION_MANY_TO_MANY;
            }
        }
        return $result;
    }

    /**
     * Get name of relations Db_Object
     * @param string $field
     * @return bool|string
     * @throws Exception
     */
    public function getRelationsObject(string $field)
    {
        $cfg = $this->getFieldConfig($field);

        if(isset($cfg['type']) && $cfg['type']==='link'
            && isset($cfg['link_config']['link_type'])
            && $cfg['link_config']['link_type'] == self::LINK_OBJECT_LIST
            && isset($cfg['link_config']['object'])
            && isset($cfg['link_config']['relations_type'])
            && $cfg['link_config']['relations_type'] == self::RELATION_MANY_TO_MANY
        ){
            return $this->getName().'_'.$field.'_to_'.$cfg['link_config']['object'];
        }
        return false;
    }

    /**
     * Check if field is system field of version control
     * @param string $field - field name
     * @return bool
     */
    public function isVcField($field) : bool
    {
        $vcFields = $this->getVcFields();
        return isset($vcFields[$field]);
    }

    /**
     * Check if object is relations object
     * @return  bool
     */
    public function isRelationsObject() : bool
    {
        if($this->isSystem() && $this->config->offsetExists('parent_object') && !empty($this->config->get('parent_object'))){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Check if object is sharding index
     */
    public function isIndexObject()
    {
        $link = & $this->config->dataLink();
        if(
            isset($link['system'])
            &&
            $link['system']
            &&
            isset($link['data_object'])
            &&
            !empty($link['data_object'])
            &&
            Config::factory($link['data_object'])->isDistributed()
        ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get Data object for index
     * @throws Exception
     * @return string
     */
    public function getDataObject() : string
    {
        if(!$this->isIndexObject()){
            throw new Exception('Cannot get data object. '.$this->getName().' is not index object');
        }
        return $this->config->get('data_object');
    }

    /**
     * Get object field
     * @param string $name
     * @return Config\Field
     */
    public function getField($name) : Config\Field
    {
        $name = (string) $name;
        $fields = $this->config->get('fields');
        $config = $fields[$name];

        $config['name'] = $name;
        $fieldClass = 'Field';

        //detect field type
        $dbType = $config['db_type'];

        if(isset($config['type']) && $config['type']==='link'  && isset($config['link_config']) && isset($config['link_config']['link_type'])){
            switch ($config['link_config']['link_type']){
                case Orm\Record\Config::LINK_OBJECT;
                    $fieldClass = 'ObjectItem';
                    break;
                case Orm\Record\Config::LINK_OBJECT_LIST;
                    $fieldClass = 'ObjectList';
                    break;
                case 'dictionary';
                    $fieldClass = 'Dictionary';
                    break;
            }
        }else{
            if(in_array($dbType,Orm\Record\Builder::$intTypes,true)){
                $fieldClass = 'Integer';
            }elseif(in_array($dbType,Orm\Record\Builder::$charTypes,true)){
                $fieldClass = 'Varchar';
            }elseif (in_array($dbType,Orm\Record\Builder::$textTypes,true)){
                $fieldClass = 'Text';
            }elseif (in_array($dbType,Orm\Record\Builder::$floatTypes,true)){
                $fieldClass = 'Floating';
            }else{
                $fieldClass = $dbType;
            }
        }
        $fieldClass = 'Dvelum\\Orm\\Record\\Config\\Field\\' . ucfirst((string)$fieldClass);

        if(class_exists($fieldClass)){
            $field = new $fieldClass($config);
        }else{
            $field = new Config\Field($config);
        }

        return $field;
    }
    public function setCryptServiceLoader(callable $loader)
    {
        $this->cryptServiceLoader = $loader;
    }
    /**
     * Set encryption service adapter
     * @param CryptServiceInterface $service
     */
    public function setCryptService(CryptServiceInterface $service) : void
    {
        $this->cryptService = $service;
    }

    /**
     * Get encryption service adapter
     * @return CryptServiceInterface
     */
    public function getCryptService() : CryptServiceInterface
    {
        if(empty($this->cryptService)){
            $service = $this->cryptServiceLoader;
            $this->cryptService = $service();
        }
        return $this->cryptService;
    }

    /**
     * Check if object uses sharding strategy
     * @return bool
     */
    public function isDistributed() : bool
    {
        $link = & $this->config->dataLink();

        if(isset($link['distributed']) && $link['distributed']){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Chek if object loader requires Shard
     * @return bool
     */
    public function isShardRequired() : bool
    {
        if(!$this->isDistributed()){
            return false;
        }
        switch ($this->getShardingType()){
            case self::SHARDING_TYPE_VIRTUAL_BUCKET:
            case self::SHARDING_TYPE_KEY_NO_INDEX:
                return true;
            default :
                return false;
        }
    }
    /**
     * Delete distributed index
     * @param string $name
     * @return bool
     */
    public function removeDistributedIndex(string $name) : bool
    {
        $indexes = $this->getDistributedIndexesConfig();

        if(!isset($indexes[$name]) || $indexes[$name]['is_system'])
            return false;

        unset($indexes[$name]);

        $this->config->set('distributed_indexes' , $indexes);

        return true;
    }

    /**
     * Get object for storing distributed id for current object
     * @return string
     * @throws Exception
     */
    public function getDistributedIndexObject()
    {
        if($this->isDistributed()){
            return $this->getName() . Cfg::storage()->get('sharding.php')->get('dist_index_postfix');
        }else{
            throw new Exception('Object has no distribution');
        }
    }

    /**
     * CHeck if object has global distributed index
     */
    public function hasDistributedIndexRecord()
    {
        if($this->isDistributed()){
            $sharding = $this->getShardingType();
            if(in_array($sharding,[self::SHARDING_TYPE_GLOABAL_ID,self::SHARDING_TYPE_KEY])){
                return true;
            }
        }
        return false;
    }

    /**
     * Get system sharding fields
     * @return array
     */
    public function getDistributedFields() : array
    {
        if(!isset($this->distributedFields)){
            $this->distributedFields = Cfg::storage()->get($this->settings->get('configPath') . 'distributed/fields.php')->__toArray();
        }

        $type = $this->getShardingType();
        if($type == self::SHARDING_TYPE_KEY_NO_INDEX || $type===self::SHARDING_TYPE_KEY){
            $key = $this->getShardingKey();
            if(!empty($key)){
                $this->distributedFields[$key] = $this->getField($key)->getConfig();
                if($this->isIndexObject()){
                    $this->distributedFields[$key]['system'] = true;
                }
            }
        }

        if($type == self::SHARDING_TYPE_VIRTUAL_BUCKET || ($this->isIndexObject() && self::factory($this->getDataObject())->getShardingType() == self::SHARDING_TYPE_VIRTUAL_BUCKET)){
            $bucketFields = Cfg::storage()->get($this->settings->get('configPath') . 'distributed/bucket_fields.php')->__toArray();
            foreach ($bucketFields as $k=>$v){
                $this->distributedFields[$k] = $v;
            }
        }
        return $this->distributedFields;
    }

    /**
     * Get sharding type for distributed object
     * @return null|string
     */
    public function getShardingType() : ?string
    {
        if(!$this->config->offsetExists('sharding_type')){
            return null;
        }
        return $this->config->get('sharding_type');
    }
    /**
     * Get distributed key field
     * @return null|string
     * @throws \Exception
     */
    public function getShardingKey() : ?string
    {
        $type = $this->getShardingType();

        if(!$this->isDistributed() || empty($type)){
            return null;
        }

        $key = null;
        switch ($type){
            case self::SHARDING_TYPE_GLOABAL_ID:
                $key = $this->getPrimaryKey();
                break;
            case self::SHARDING_TYPE_KEY:
            case self::SHARDING_TYPE_KEY_NO_INDEX :
                if($this->config->offsetExists('sharding_key')){
                    $key = $this->config->get('sharding_key');
                }
                break;
            case self::SHARDING_TYPE_VIRTUAL_BUCKET:
                $key = Orm\Distributed::factory()->getBucketField();
                break;
        }
        return $key;
    }

    /**
     * Get key used for mapping object to virtual bucket.
     * Only for Virtual Bucket sharding
     * @return string|null
     */
    public function getBucketMapperKey() :?string
    {
        $type = $this->getShardingType();

        if(!$this->isDistributed() || empty($type) || $type!=self::SHARDING_TYPE_VIRTUAL_BUCKET){
            return null;
        }

        $key = null;
        if($this->config->offsetExists('sharding_key')){
            $key = $this->config->get('sharding_key');
        }

        return $key;
    }
}