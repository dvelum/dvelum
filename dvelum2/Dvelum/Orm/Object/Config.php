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

namespace Dvelum\Orm\Object;

use Dvelum\Orm;
use Dvelum\Config as Cfg;
use Dvelum\Orm\Model;
use Dvelum\Orm\Object\Config\Field;
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

    /**
     * Path to configs
     * @var string
     */
    static protected $configPath = null;

    static protected $_instances = [];

    static protected $configs = [];

    /**
     * @var Cfg\Adapter
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
     * @var Cfg\Adapter
     */
    static protected $encConfig;

    /**
     * @var string $name
     * @return Orm\Object\Config
     */
    protected $name;

    /**
     * Translation config
     * @var Cfg\Adapter
     */
    static protected $translation = null;

    /**
     * Translation adapter
     * @var Orm\Object\Config\Translator
     */
    static protected $translator = false;

    /**
     * Translation flag
     * @var boolean
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
     * @var Acl
     */
    protected $acl = false;

    /**
     * Instantiate data structure for the objects named $name
     * @param string $name - object name
     * @param boolean $force - reload config
     * @return Orm\Object\Config
     * @deprecated
     * @throws Exception
     */
    static public function getInstance($name , $force = false)
    {
        return self::factory($name, $force);
    }

    /**
     * Instantiate data structure for the objects named $name
     * @param string $name - object name
     * @param boolean $force - reload config
     * @return Orm\Object\Config
     * @throws Exception
     */
    static public function factory(string $name , bool $force = false) : Orm\Object\Config
    {
        $name = strtolower($name);

        if($force || !isset(self::$_instances[$name])) {
            self::$_instances[$name] = new static($name , $force);
        }

        return self::$_instances[$name];
    }

    /**
     * Reload object Properties
     */
    public function reloadProperties()
    {
        $this->localCache = [];
        $this->loadProperties();
    }

    final private function __clone(){}

    final private function __construct($name , $force = false)
    {
        $this->name = strtolower($name);

        if(!self::configExists($name))
            throw new Exception('Undefined object config '. $name);

        $this->config = Cfg\Factory::storage()->get(self::$configs[$name], !$force , false);
        $this->loadProperties();
    }

    /**
     * Register external objects
     * @param array $data
     */
    static public function registerConfigs(array $data)
    {
        foreach ($data  as $object=>$configPath)
            self::$configs[strtolower($object)] = $configPath;
    }

    /**
     * Object config existence check
     * @param string $name
     * @return boolean
     */
    static public function configExists(string $name) : bool
    {
        $name = strtolower($name);

        if(isset(self::$configs[$name]))
            return true;

        if(\Dvelum\Config\Factory::storage()->exists(self::$configPath . $name .'.php'))
        {
            self::$configs[$name] = self::$configPath . $name .'.php';
            return true;
        }

        return false;
    }

    /**
     * Set config files path
     * @param string $path
     * @return void
     */
    static public function setConfigPath($path)
    {
        self::$configPath = $path;
    }

    /**
     * Get config files path
     * @return string
     */
    static public function getConfigPath()
    {
        return self::$configPath;
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
        self::$translator->translate($this->name , $dataLink);

        $this->translated = true;
    }

    /**
     * Prepare config, load system properties
     * @throws Exception
     */
    protected function loadProperties()
    {
        $dataLink = & $this->config->dataLink();
        $pKeyName = $this->getPrimaryKey();

        $dataLink['fields'][$pKeyName] = Cfg::storage()->get(
            self::$configPath.'system/pk_field.php'
        )->__toArray();

        /*
         * System index init
         */
        $dataLink['indexes']['PRIMARY'] = array(
            'columns'=>[$pKeyName],
            'fulltext'=>false,
            'unique'=>true,
            'primary'=>true
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

        /*
         * Init ACL adapter
         */
        if(!empty($dataLink['acl']))
            $this->acl = Orm\Object\Acl::factory($dataLink['acl']);

    }

    /**
     * Get Version control fields
     * @return array
     */
    protected function getVcFields() : array
    {
        if(!isset(self::$vcFields))
            self::$vcFields = Cfg\Factory::storage()->get(self::$configPath.'vc/vc_fields.php')->__toArray();

        return self::$vcFields;
    }

    /**
     * Get encryption fields
     * @return array
     */
    protected function getEncryptionFields() : array
    {
        if(!isset(self::$cryptFields))
            self::$cryptFields = Cfg\Factory::storage()->get(self::$configPath.'enc/fields.php')->__toArray();

        if(!isset(self::$encConfig))
            self::$encConfig = Cfg\Factory::storage()->get(self::$configPath.'enc/config.php')->__toArray();

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
        if($this->config->offsetExists('indexes'))
        {
            if($includeSystem)
                return $this->config->get('indexes');

            $indexes = $this->config->get('indexes');
            if(isset($indexes['PRIMARY']))
                unset($indexes['PRIMARY']);
            return $indexes;
        }
        else
        {
            return [];
        }
    }

    /**
     * Get the field configuration
     * @param string $field
     * @throws Exception
     * @return array
     */
    public function getFieldConfig($field) : array
    {
        $this->prepareTranslation();

        if(!isset($this->config['fields'][$field]))
            throw new Exception('Invalid field name: ' . $field);

        return $this->config['fields'][$field]->__toArray();
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
    public function getLinks($linkTypes = [Orm\Object\Config::LINK_OBJECT, Orm\Object\Config::LINK_OBJECT_LIST], $groupByObject = true) : array
    {
        $data = [];
        $fields = $this->getFieldsConfig(true);
        foreach ($fields as $name=>$cfg)
        {
            $cfg = $cfg->__toArray();

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
    public function save()
    {
        $fields = $this->getFieldsConfig(false);
        $indexes = $this->getIndexesConfig(false);
        $translationsData = false;

        $config = clone $this->config;

        $translation = self::$translator->getTranslation();

        if($translation instanceof \Dvelum\Config\Adapter){
            $translationsData = & $translation->dataLink();
            $translationsData[$this->name]['title'] = $this->config->get('title');
        }

        foreach ($fields as $field =>& $cfg)
        {
            if($translationsData !==false)
                $translationsData[$this->name]['fields'][$field] = $cfg['title'];
            unset($cfg['title']);
        }

        $config->set('fields', $fields);
        $config->set('indexes' , $indexes);
        $config->offsetUnset('title');

        if($translation instanceof \Dvelum\Config\Adapter && !$translation->save())
            return false;

        return $config->save();
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
        $title = '';
        if(isset($config['title']))
            $title = $config['title'];

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
        if(!$this->isLink($field))
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
        $builder = Orm\Object\Builder::factory($this->getName() , false);
        return $builder->renameField($oldName , $newName);
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
     * @return Cfg\Adapter
     */
    public function getConfig() : Cfg\Adapter
    {
        return $this->config;
    }

    /**
     * Check if object is system defined
     * @return bool
     */
    public function isSystem() : bool
    {
        if($this->config->offsetExists('system') && $this->config['system'])
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

        $links = $this->getLinks([Orm\Object\Config::LINK_OBJECT]);

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
    static public function setTranslator(Config\Translator $translator) :void
    {
        self::$translator = $translator;
    }

    /**
     * Get Translation adapter
     * @return Config\Translator
     */
    static public function getTranslator() : Config\Translator
    {
        return self::$translator;
    }

    /**
     * Get Access Control Adapter
     * @return Orm\Object\Acl | bool false
     */
    public function getAcl() : Orm\Object\Acl
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
     * @return string|null
     */
    public function getIvField() : ?string
    {
        if(!isset(self::$encConfig))
            return null;

        return self::$encConfig['iv_field'];
    }

    /**
     * Decrypt value
     * @param $value
     * @param $iv - public key
     * @return string
     */
    public function decrypt($value , $iv) : string
    {
        return \Utils_String::decrypt($value , self::$encConfig['key'] , $iv);
    }

    /**
     * Encrypt value
     * @param $value
     * @param $iv  - public key
     * @return string
     */
    public function encrypt($value, $iv) : string
    {
        return \Utils_String::encrypt($value , self::$encConfig['key'] , $iv);
    }

    /**
     * Create public key
     * @return string
     */
    public function createIv()
    {
        return \Utils_String::createEncryptIv();
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
                && $cfg['link_config']['link_type'] == Self::LINK_OBJECT_LIST
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
     * @param $field
     * @return mixed  false || string
     */
    public function getRelationsObject($field)
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
     * Get object field
     * @param string $name
     * @return Config\Field
     */
    public function getField(string $name) : Config\Field
    {
        $fields = $this->config->get('fields');
        $config = $fields[$name];

        $config['name'] = $name;
        $fieldClass = 'Field';

        //detect field type
        $dbType = $config['db_type'];

        if(isset($config['type']) && $config['type']==='link'  && isset($config['link_config']) && isset($config['link_config']['link_type'])){
            switch ($config['link_config']['link_type']){
                case Orm\Object\Config::LINK_OBJECT;
                    $fieldClass = 'Object';
                    break;
                case Orm\Object\Config::LINK_OBJECT_LIST;
                    $fieldClass = 'ObjectList';
                    break;
                case 'dictionary';
                    $fieldClass = 'Dictionary';
                    break;
            }
        }else{
            if(in_array($dbType,Orm\Object\Builder::$intTypes,true)){
                $fieldClass = 'Integer';
            }elseif(in_array($dbType,Orm\Object\Builder::$charTypes,true)){
                $fieldClass = 'Varchar';
            }elseif (in_array($dbType,Orm\Object\Builder::$textTypes,true)){
                $fieldClass = 'Text';
            }elseif (in_array($dbType,Orm\Object\Builder::$floatTypes,true)){
                $fieldClass = 'Floating';
            }else{
                $fieldClass = $dbType;
            }
        }
        $fieldClass = 'Dvelum\\Orm\\Object\\Config\\Field\\' . ucfirst($fieldClass);

        if(class_exists($fieldClass)){
            $field = new $fieldClass($config);
        }else{
            $field = new Config\Field($config);
        }

        return $field;
    }
}