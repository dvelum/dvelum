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
    const DEFAULT_CONNECTION = 'default';
    const RELATION_MANY_TO_MANY = 'many_to_many';

    /**
     * Path to configs
     * @var string
     */
    static protected $configPath = null;

    static protected $_instances = [];

    static protected $configs = [];

    /**
     * @var \Dvelum\Config\Config
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
     * @var \Dvelum\Config\Config
     */
    static protected $encConfig;

    /**
     * @var string $name
     * @return Orm\Object\Config
     */
    protected $name;

    /**
     * Translation config
     * @var \Dvelum\Config\Config
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
     * @var Db_Object_Acl
     */
    protected $_acl = false;

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
            throw new \Exception('Undefined object config '. $name);

        $this->config = \Dvelum\Config\Factory::storage()->get(self::$configs[$name], !$force , false);
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
    protected function _prepareTranslation()
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
        /*
         * System field init
         */
        $dataLink['fields'][$pKeyName] =  array(
            'title'=>'PRIMARY_KEY',
            'system'=>true,
            'db_type' => 'bigint',
            'db_isNull' => false,
            'db_unsigned'=>true,
            'db_auto_increment'=>true,
            'unique'=>'PRIMARY',
            'is_search' =>true,
            'lazyLang'=>true
        );
        /*
         * System index init
         */
        $dataLink['indexes']['PRIMARY'] = array(
            'columns'=>array($pKeyName),
            'fulltext'=>false,
            'unique'=>true,
            'primary'=>true
        );

        /*
        * Backward compatibility
        */
        if(!isset($dataLink['connection']))
            $dataLink['connection'] = self::DEFAULT_CONNECTION;
        if(!isset($dataLink['locked']))
            $dataLink['locked'] = false;
        if(!isset($dataLink['readonly']))
            $dataLink['readonly'] = false;
        if(!isset($dataLink['primary_key']))
            $dataLink['primary_key'] = $pKeyName;
        if(!isset($dataLink['use_db_prefix']))
            $dataLink['use_db_prefix'] = true;
        if(!isset($dataLink['acl']) || empty($dataLink['acl']))
            $dataLink['acl'] = false;
        if(!isset($dataLink['slave_connection']) || empty($dataLink['slave_connection']))
            $dataLink['slave_connection'] = $dataLink['connection'];

        foreach($dataLink['fields'] as &$field){
            if(isset($field['link_config']) && isset($field['link_config']['link_type']) && $field['link_config']['link_type'] == 'multy'){
                $field['link_config']['link_type'] = 'multi';
            }
        }
        
        /*
         * Load additional fields for object under revision control
         */
        if(isset($dataLink['rev_control']) && $dataLink['rev_control'])
            $dataLink['fields'] = array_merge($dataLink['fields'] , $this->_getVcFields());

        if($this->hasEncrypted())
            $dataLink['fields'] = array_merge($dataLink['fields'] , $this->_getEncryptionFields());

        /*
         * Init ACL adapter
         */
        if(!empty($dataLink['acl']))
            $this->_acl = Orm\Object\Acl::factory($dataLink['acl']);
    }

    protected function _getVcFields()
    {
        if(!isset(self::$vcFields))
            self::$vcFields = \Dvelum\Config\Factory::config(\Dvelum\Config\Factory::File_Array, self::$configPath.'vc/vc_fields.php')->__toArray();

        return self::$vcFields;
    }

    //encrypted
    protected function _getEncryptionFields()
    {
        if(!isset(self::$cryptFields))
            self::$cryptFields = \Dvelum\Config\Factory::config(\Dvelum\Config\Factory::File_Array, self::$configPath.'enc/fields.php')->__toArray();

        if(!isset(self::$encConfig))
            self::$encConfig = \Dvelum\Config\Factory::config(\Dvelum\Config\Factory::File_Array, self::$configPath.'enc/config.php')->__toArray();

        return self::$cryptFields;
    }

    /**
     * Get a list of fields to be used for search
     * @return array
     */
    public function getSearchFields()
    {
        $fields = [];
        $fieldsConfig = $this->get('fields');

        foreach ($fieldsConfig as $k=>$v)
            if($this->isSearch($k))
                $fields[] = $k;
        return $fields;
    }


    /**
     * Get a configuration element by key (system method)
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if($key === 'fields' || $key === 'title')
            $this->_prepareTranslation();

        return $this->config->get($key);
    }

    /**
     * Check if the object is using revision control
     */
    public function isRevControl()
    {
        return ($this->config->offsetExists('rev_control') && $this->config->get('rev_control'));
    }

    /**
     * Get a list of indices (from the configuration)
     * @param boolean $includeSystem -optional default = true
     * @return array
     */
    public function getIndexesConfig($includeSystem = true)
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
    public function getFieldConfig($field)
    {
        $this->_prepareTranslation();

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
    public function getIndexConfig($index)
    {
        $this->_prepareTranslation();

        if(!isset($this->config['indexes'][$index]))
            throw new Exception('indexes Index name: ' . $index);

        return $this->config['indexes'][$index];
    }

    /**
     * Get the configuration of all fields
     * @param boolean $includeSystem -optional default = true
     * @return array
     */
    public function getFieldsConfig($includeSystem = true)
    {
        $this->_prepareTranslation();

        if($includeSystem)
            return $this->config['fields'];

        $fields = $this->config['fields'];
        unset($fields[$this->getPrimaryKey()]);

        foreach($fields as $k=>$field){
            if(isset($field['system']) && $field['system']){
                unset($fields[$k]);
            }
        }
        return  $fields;
    }

    /**
     * Get the configuration of system fields
     * @return array
     */
    public function getSystemFieldsConfig()
    {
        $this->_prepareTranslation();
        $primaryKey = $this->getPrimaryKey();
        $fields = [];

        if($this->isRevControl())
            $fields = $this->_getVcFields();

        if($this->hasEncrypted())
            $fields = array_merge($fields , $this->_getEncryptionFields());

        $fields[$primaryKey] = $this->config['fields'][$primaryKey];

        return $fields;
    }

    /**
     * Check if a field is a object link
     * @param string $field
     * @throws Exception
     * @return boolean
     */
    public function isObjectLink($field)
    {
        $cfg = $this->getFieldConfig($field);

        if(isset($cfg['type']) && $cfg['type']==='link' && is_array($cfg['linkconfig']) && $cfg['linkconfig']['link_type']===self::LINK_OBJECT)
            return true;
        else
            return false;
    }

    /**
     * Check if a field is a multilink (a list of links to objects of the same type)
     * @param string $field
     * @throws Exception
     * @return boolean
     */
    public function isMultiLink($field)
    {
        $cfg = $this->getFieldConfig($field);

        if(isset($cfg['type']) && $cfg['type']==='link' && isset($cfg['linkconfig']) && is_array($cfg['linkconfig']) && $cfg['linkconfig']['link_type']===self::LINK_OBJECT_LIST)
            return true;
        else
            return false;
    }

    /**
     * Check if field is ManyToMany relation
     * @param $field
     * @return boolean
     */
    public function isManyToManyLink($field)
    {
        $cfg = $this->getFieldConfig($field);

        if(isset($cfg['type']) && $cfg['type']==='link'
            && is_array($cfg['linkconfig'])
            && $cfg['linkconfig']['link_type'] === self::LINK_OBJECT_LIST
            && isset($cfg['linkconfig']['relations_type'])
            && $cfg['linkconfig']['relations_type'] === self::RELATION_MANY_TO_MANY
        ){
            return true;
        }
        return false;
    }

    /**
     * Get the name of the object referenced by the field
     * @param string $field
     * @return string  or false on error
     */
    public function getLinkedObject($field)
    {
        if(!$this->isLink($field))
            return false;
        $cfg = $this->getFieldConfig($field);
        return 	$cfg['linkconfig']['object'];
    }

    /**
     * Get the name of the dictionary that is referenced by the field
     * @param string $field
     * @return string or false on error
     */
    public function getLinkedDictionary($field)
    {
        if(!$this->isDictionaryLink($field))
            return false;
        $cfg = $this->getFieldConfig($field);
        return 	$cfg['linkconfig']['object'];
    }

    /**
     * Check if the field is a link
     * @param string $field
     * @return boolean
     */
    public function isLink($field)
    {
        $cfg = $this->getFieldConfig($field);
        if(isset($cfg['type']) && $cfg['type']==='link')
            return true;
        else
            return false;
    }

    /**
     * Check if the field is a link to the dictionary
     * @param string $field
     * @return boolean
     */
    public function isDictionaryLink($field)
    {
        $cfg = $this->getFieldConfig($field);
        if(isset($cfg['type']) && $cfg['type']==='link' && is_array($cfg['linkconfig']) && $cfg['linkconfig']['link_type']==='dictionary')
            return true;
        else
            return false;
    }

    /**
     * Check if html is allowed
     * @param string $field
     * @return boolean
     */
    public function isHtml($field)
    {
        $cfg = $this->getFieldConfig($field);

        if(isset($cfg['allow_html']) && $cfg['allow_html'])
            return true;

        return false;
    }

    /**
     * Get the database type for the field
     * @param string $field
     * @return string
     */
    public function getDbType($field)
    {
        $cfg = $this->getFieldConfig($field);
        return $cfg['db_type'];
    }

    /**
     * Get a list of fields linking to external objects
     * @param array $linkTypes  - optional link type filter
     * @param boolean $groupByObject - group field by linked object, default true
     * @return array  [objectName=>[field => link_type]] | [field =>["object"=>objectName,"link_type"=>link_type]]
     */
    public function getLinks($linkTypes = array(Orm\Object\Config::LINK_OBJECT, Orm\Object\Config::LINK_OBJECT_LIST), $groupByObject = true)
    {
        $data = [];
        $fields = $this->getFieldsConfig(true);
        foreach ($fields as $name=>$cfg) {
            if(isset($cfg['type']) && $cfg['type']==='link'
                && isset($cfg['linkconfig']['link_type'])
                && in_array($cfg['linkconfig']['link_type'], $linkTypes , true)
                && isset($cfg['linkconfig']['object'])
            ){
                if($groupByObject)
                    $data[$cfg['linkconfig']['object']][$name] = $cfg['linkconfig']['link_type'];
                else
                    $data[$name] = ['object'=>$cfg['linkconfig']['object'],'link_type'=>$cfg['linkconfig']['link_type']];
            }
        }
        return $data;
    }

    /**
     * Check whether the field should be unique
     * @param string $name
     * @return boolean
     */
    public function isUnique($name)
    {
        $fields = $this->config->get('fields');
        if(!isset($fields[$name]['unique']))
            return false;

        if(is_string($fields[$name]['unique']) && strlen($fields[$name]['unique']))
            return true;

        return (boolean) $fields[$name]['unique'];
    }
    /**
     * Check if the object uses history log
     * @return boolean
     */
    public function hasHistory()
    {
        if($this->config->offsetExists('save_history') && $this->config->get('save_history'))
            return true;
        else
            return false;
    }

    /**
     * Check if the object uses extended history log
     * @return boolean
     */
    public function hasExtendedHistory()
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
     * @return boolean
     */
    public function hasDbPrefix()
    {
        return $this->config->get('use_db_prefix');
    }

    /**
     * Check whether the field is a boolean field
     * @param string $field
     * @return boolean
     */
    public function isBoolean($field)
    {
        return (isset($this->config['fields'][$field]['db_type']) &&  $this->config['fields'][$field]['db_type'] === 'boolean');
    }

    /**
     * Check whether the field is a numeric field
     * @param string $field
     * @return boolean
     */
    public function isNumeric($field)
    {
        return (isset($this->config['fields'][$field]['db_type']) && in_array($this->config['fields'][$field]['db_type'] ,Orm\Object\Builder::$numTypes , true));
    }

    /**
     * Check whether the field is a integer field
     * @param string $field
     * @return boolean
     */
    public function isInteger($field)
    {
        return (isset($this->config['fields'][$field]['db_type']) && in_array($this->config['fields'][$field]['db_type'] , Orm\Object\Builder::$intTypes , true));
    }

    /**
     * Check whether the field is a float field
     * @param string $field
     * @return boolean
     */
    public function isFloat($field)
    {
        return (isset($this->config['fields'][$field]['db_type']) && in_array($this->config['fields'][$field]['db_type'] , Orm\Object\Builder::$floatTypes , true));
    }

    /**
     * Check whether the field is a text field
     * @param boolean $field
     * @param boolean $charTypes optional
     */
    public function isText($field , $charTypes = false)
    {
        if(!isset($this->config['fields'][$field]['db_type']))
            return false;

        $isText =  (in_array($this->config['fields'][$field]['db_type'] , Orm\Object\Builder::$textTypes , true));

        if($charTypes && !$isText)
            $isText =  (in_array($this->config['fields'][$field]['db_type'] , Orm\Object\Builder::$charTypes, true));

        return $isText;
    }

    /**
     * Check whether the field is a date field
     * @param boolean $field
     */
    public function isDateField($field)
    {
        return (isset($this->config['fields'][$field]['db_type']) && in_array($this->config['fields'][$field]['db_type'] , Orm\Object\Builder::$dateTypes, true));
    }

    /**
     * Check if the field value is required
     * @param string $field
     * @return boolean
     */
    public function isRequired($field)
    {
        if(isset($this->config['fields'][$field]['required']) &&  $this->config['fields'][$field]['required'])
            return true;
        else
            return false;
    }

    /**
     * Check if field can be used for search
     * @param string $field
     * @return boolean
     */
    public function isSearch($field)
    {
        if(isset($this->config['fields'][$field]['is_search']) && $this->config['fields'][$field]['is_search'])
            return true;
        else
            return false;
    }

    /**
     * Check if field is encrypted
     * @param string $field
     */
    public function isEncrypted($field)
    {
        if(isset($this->config['fields'][$field]['type']) && $this->config['fields'][$field]['type']==='encrypted')
            return true;
        else
            return false;
    }

    /**
     * Check if the field is present in the description
     * @param string $field
     */
    public function fieldExists($field)
    {
        return isset($this->config['fields'][$field]);
    }

    /**
     * Check if Index exists
     * @param string $index
     * @return boolean
     */
    public function indexExists($index)
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
     * Conver into array
     * @return array
     */
    public function __toArray()
    {
        $this->_prepareTranslation();
        return $this->config->__toArray();
    }

    /**
     * Get the title for the object
     * @return string
     */
    public function getTitle()
    {
        $this->_prepareTranslation();
        return $this->config['title'];
    }

    /**
     * Set object title
     * @param string $title
     */
    public function setObjectTitle($title)
    {
        $this->_prepareTranslation();
        $this->config['title'] = $title;
    }

    /**
     * Get the name of the field linking to this object and used as a text representation
     * @return string
     */
    public function getLinkTitle()
    {
        $this->_prepareTranslation();

        if(isset($this->config['link_title']) && !empty($this->config['link_title']))
            return $this->config['link_title'];
        else
            return $this->getPrimaryKey();
    }
    /**
     * Check if object is readonly
     * @return boolean
     */
    public function isReadOnly()
    {
        return $this->config->get('readonly');
    }

    /**
     * Check if object structure is locked
     * @return boolean
     */
    public function isLocked()
    {
        return $this->config->get('locked');
    }

    /**
     * Check if there are transactions available for this type of objects
     * @return boolean
     */
    public function isTransact()
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

        if($translation instanceof \Dvelum\Config\Config){
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

        if($translation instanceof \Dvelum\Config\Config && !$translation->save())
            return false;

        $this->fixConfig();
        return $config->save();
    }

    /**
     * Replace configuration data with an array
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->config->setData($data);
    }

    /**
     * Get configuration as array
     * @return array
     */
    public function getData()
    {
        return $this->config->__toArray();
    }

    /**
     * Configure the field
     * @param string $field
     * @param array $config
     */
    public function setFieldConfig($field , array $config)
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
     * @return boolean
     */
    public function setFieldLink($field , $linkedObject)
    {
        if(!$this->isLink($field))
            return false;

        $cfg = & $this->config->dataLink();
        $cfg['fields'][$field]['linkconfig']['object'] = $linkedObject;
        return true;
    }

    /**
     * Configure the index
     * @param string $index
     * @param array $config
     */
    public function setIndexConfig($index , array $config)
    {
        $indexes = $this->getIndexesConfig();
        $indexes[$index] = $config;
        $this->config->set('indexes', $indexes);
    }

    /**
     * Rename field and rebuild the database table
     * @param string $oldName
     * @param string $newName
     * @return boolean
     */
    public function renameField($oldName , $newName)
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
        $builder = new Orm\Object\Builder($this->getName() , false);
        return $builder->renameField($oldName , $newName);
    }

    /**
     * Remove field
     * @param string $name
     */
    public function removeField($name)
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
     */
    public function removeIndex($name)
    {
        $indexes = $this->getIndexesConfig();
        if(!isset($indexes[$name]))
            return;
        unset($indexes[$name]);
        $this->config->set('indexes' , $indexes);
    }

    /**
     * Get Config object
     * @return Config_Abstract
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Check if object is system defined
     * @return boolean
     */
    public function isSystem()
    {
        if($this->config->offsetExists('system') && $this->config['system'])
            return true;
        else
            return false;
    }

    /**
     * Check system field
     * @param string $field
     * @return boolean
     */
    public function isSystemField($field)
    {
        if($field === $this->getPrimaryKey())
            return true;

        $sFields = $this->_getVcFields();

        if(isset($sFields[$field]))
            return true;

        $encFields = $this->_getEncryptionFields();
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
    public function getForeignKeys()
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
            $oConfig = Db_Objectconfig::getInstance($object);
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

                if($this->isRequired($name))
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
     * @return boolean
     */
    public function canUseForeignKeys()
    {
        if($this->config->offsetExists('disable_keys') && $this->config->get('disable_keys'))
            return false;

        if(!$this->isTransact())
            return false;

        return true;
    }

    /*
     * service stub
     */
    public function getPrimaryKey()
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
     */
    static public function setTranslator(Config\Translator $translator)
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
     * Get field default value. Note! Method return false if value not specified
     * @return string | false
     */
    public function getDefault($field)
    {
        $field = $this->getFieldConfig($field);
        if(isset($field['db_default']))
            return $field['db_default'];
        else
            return false;
    }

    /**
     * Check if field has default value
     * @return boolean
     */
    public function hasDefault($field)
    {
        $field = $this->getFieldConfig($field);
        if(isset($field['db_default']) && $field['db_default']!==false)
            return true;
        else
            return false;
    }

    /**
     * Check if field is numeric and unsigned
     * @param string $field
     * @return boolean
     */
    public function isUnsigned($field)
    {
        if(!$this->isNumeric($field))
            return false;

        $field = $this->getFieldConfig($field);
        if(isset($field['db_unsigned']) && $field['db_unsigned'])
            return true;
        else
            return false;
    }

    /**
     * Check if field  can be null
     * @param string $field
     * @return boolean
     */
    public function isNull($field)
    {
        $field = $this->getFieldConfig($field);
        if(isset($field['db_isNull']) && $field['db_isNull'])
            return true;
        else
            return false;
    }

    /**
     * Check and fix config file, update old config structure
     */
    public function fixConfig()
    {
        $fields = array_keys($this->getFieldsConfig(false));
        $cfg = & $this->config->dataLink();
        foreach ($fields as $name)
        {
            /*
             * config validation for links
             */
            if($this->isLink($name))
            {
                $v = & $cfg['fields'][$name];
                $isRequired = $this->isRequired($name);
                if($this->isDictionaryLink($name)){
                    $v['db_isNull'] = false;
                    if($isRequired){
                        $v['db_default'] = false;
                    }else{
                        $v['db_default'] = '';
                    }
                }elseif ($this->isObjectLink($name)){
                    $v['db_isNull'] = (boolean) !$isRequired;
                    $v['db_type'] ='bigint';
                    $v['db_default'] = false;
                    $v['db_unsigned'] = true;
                }elseif ($this->isMultiLink($name)){
                    $v['db_type'] = 'longtext';
                    $v['db_isNull'] = false;
                    $v['db_default'] = '';
                }
            }
        }
        unset($v);
        unset($cfg);
    }
    /**
     * Get Access Controll_Adapter
     * @return Db_Object_Acl | boolean false
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Check for encoded fields
     * @return boolean
     */
    public function hasEncrypted()
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
    public function getEncryptedFields()
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
     * @return bool|null
     */
    public function getIvField()
    {
        if(!isset(self::$encConfig))
            return false;

        return self::$encConfig['iv_field'];
    }

    /**
     * Decrypt value
     * @param $value
     * @param $iv - public key
     * @return string
     */
    public function decrypt($value , $iv)
    {
        return Utils_String::decrypt($value , self::$encConfig['key'] , $iv);
    }

    /**
     * Encrypt value
     * @param $value
     * @param $iv  - public key
     * @return string
     */
    public function encrypt($value, $iv)
    {
        return Utils_String::encrypt($value , self::$encConfig['key'] , $iv);
    }

    /**
     * Create public key
     * @return string
     */
    public function createIv()
    {
        return Utils_String::createEncryptIv();
    }

    /**
     * Check if object has ManyToMany relations
     * @return bool
     */
    public function hasManyToMany()
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
     */
    public function getManyToMany()
    {
        $result = [];
        $fieldConfigs = $this->getFieldsConfig();
        foreach($fieldConfigs as $field=>$cfg)
        {
            if(isset($cfg['type']) && $cfg['type']==='link'
                && isset($cfg['linkconfig']['link_type'])
                && $cfg['linkconfig']['link_type'] == Self::LINK_OBJECT_LIST
                && isset($cfg['linkconfig']['object'])
                && isset($cfg['linkconfig']['relations_type'])
                && $cfg['linkconfig']['relations_type'] == self::RELATION_MANY_TO_MANY
            ){
                $result[$cfg['linkconfig']['object']][$field] = self::RELATION_MANY_TO_MANY;
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
            && isset($cfg['linkconfig']['link_type'])
            && $cfg['linkconfig']['link_type'] == self::LINK_OBJECT_LIST
            && isset($cfg['linkconfig']['object'])
            && isset($cfg['linkconfig']['relations_type'])
            && $cfg['linkconfig']['relations_type'] == self::RELATION_MANY_TO_MANY
        ){
            return $this->getName().'_'.$field.'_to_'.$cfg['linkconfig']['object'];
        }
        return false;
    }

    /**
     * Check if field is virtual (no database representation)
     * @param $field
     * @return bool
     */
    public function isVirtual($field)
    {
        return $this->isMultiLink($field);
    }

    /**
     * Check if field is system field of version control
     * @param string $field - field name
     * @return boolean
     */
    public function isVcField($field)
    {
        $vcFields = $this->_getVcFields();
        return isset($vcFields[$field]);
    }

    /**
     * Check if object is relations object
     * @return  boolean
     */
    public function isRelationsObject()
    {
        if($this->isSystem() && $this->config->offsetExists('parent_object') && !empty($this->config->get('parent_object'))){
            return true;
        }else{
            return false;
        }
    }
}
