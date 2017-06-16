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

namespace Dvelum\Orm\Object;

use Dvelum\Config as Cfg;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Zend\Db\Sql\Ddl;

/**
 * Builder for Orm\Object
 * @package Orm
 * @subpackage Orm\Object
 * @author Kirill Ygorov
 * @license General Public License version 3
 *
 * @todo replace Exceptions, create error messages
 */
class Builder
{
    protected static $writeLog = false;
    protected static $logPrefix = '0.1';
    protected static $logsPath = './logs/';
    protected static $foreignKeys = false;

    /**
     * @param string $objectName
     * @param bool $forceConfig
     * @return Builder\AbstractAdapter
     */
    static public function factory(string $objectName, bool $forceConfig = true) : Builder\AbstractAdapter
    {
        $objectConfig = Config::factory($objectName);

        $adapter = 'Builder_Generic';

        $config = Cfg::factory(\Dvelum\Config\Factory::Simple, $adapter);

        $log = false;
        if(static::$writeLog){
            $log = new \Dvelum\Log\File\Sql(static::$logsPath . $objectConfig->get('connection') . '-' . static::$logPrefix . '-build.log');
        }

        $ormConfig = Cfg::storage()->get('orm.php');

        $config->setData([
           'objectName' => $objectName,
           'configPath' =>  $ormConfig->get('object_configs'),
           'log' => $log,
           'useForeignKeys' => static::$foreignKeys
        ]);

        $model = Model::factory($objectName);
        $platform = $model->getDbConnection()->getAdapter()->getPlatform();

        $platform = $platform->getName();
        $builderAdapter = static::class . '\\' . $platform;

        if(class_exists($builderAdapter)){
            return new Builder\MySQL($config, $forceConfig);
        }

        $builderAdapter = static::class . '\\Generic\\' . $platform;

        if(class_exists($builderAdapter)){
            return new Builder\MySQL($config, $forceConfig);
        }

        throw new Orm\Exception('Undefined Platform');
    }

    public static $booleanTypes = [
      'bool',
      'boolean'
    ];

    public static $numTypes = [
        'tinyint' ,
        'smallint' ,
        'mediumint' ,
        'int' ,
        'integer',
        'bigint' ,
        'float' ,
        'double' ,
        'decimal' ,
        'bit',
        'biginteger'
    ];

    public static $intTypes = [
        'tinyint' ,
        'smallint' ,
        'mediumint' ,
        'int' ,
        'integer',
        'bigint' ,
        'bit',
        'biginteger'
    ];

    public static $floatTypes = [
        'decimal' ,
        'float' ,
        'double'
    ];

    public static $charTypes = [
        'char' ,
        'varchar'
    ];

    public static $textTypes = [
        'tinytext' ,
        'text' ,
        'mediumtext' ,
        'longtext'
    ];

    public static $dateTypes = [
        'date' ,
        'datetime' ,
        'time' ,
        'timestamp'
    ];

    public static $blobTypes = [
        'tinyblob' ,
        'blob' ,
        'mediumblob' ,
        'longblob'
    ];

    /**
     * Write SQL log
     * @param boolean $flag
     * @return void
     */
    static public function writeLog($flag) : void
    {
        self::$writeLog = (boolean) $flag;
    }

    /**
     * Set query log file prefix
     * @param string $string
     * @return void
     */
    static public function setLogPrefix(string $string) : void
    {
        self::$logPrefix = strval($string);
    }

    /**
     * Set logs path
     * @param string $string
     * @return void
     */
    static public function setLogsPath(string $string) : void
    {
        self::$logsPath = $string;
    }

    /**
     * Use foreign keys
     * @param bool $flag
     * @return void
     */
    static public function useForeignKeys($flag) : void
    {
        self::$foreignKeys = (bool) $flag;
    }

    /**
     * Check if foreign keys is used
     * @return bool
     */
    static public function foreignKeys() : bool
    {
        return self::$foreignKeys;
    }

    /**
     * Rename object field
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    public function renameField($oldName , $newName) : bool
    {
        if($this->objectConfig->isLocked() || $this->objectConfig->isReadOnly())
        {
            $this->errors[] = 'Can not build locked object ' . $this->objectConfig->getName();
            return false;
        }

        $fieldConfig = $this->objectConfig->getFieldConfig($newName);

        $sql = ' ALTER TABLE ' . $this->model->table() . ' CHANGE `' . $oldName . '` ' . $this->_proppertySql($newName , $fieldConfig);

        try
        {
            $this->db->query($sql);
            $this->logSql($sql);
            return true;
        }
        catch(\Exception $e)
        {
            $this->errors[] = $e->getMessage() . ' <br>SQL: ' . $sql;
            return false;
        }
    }



    /**
     * Build indexes for "create" query
     *
     * @return array - sql parts
     */
    protected function _createIndexes()
    {
        $cmd = array();
        $configIndexes = $this->objectConfig->getIndexesConfig();

        foreach($configIndexes as $index => $config)
            $cmd[] = $this->_prepareIndex($index , $config , true);

        return $cmd;
    }

    public function prepareKeysUpdate($dropOnly = false)
    {
        $updates = array();
        $curTable = $this->model->table();

        /*
         * Get foreign keys form ORM
         */
        $configForeignKeys = $this->getOrmForeignKeys();

        /*
         * Get foreign keys form database table
         */
        $realKeys = $this->getForeignKeys($this->model->table());
        $realKeysNames = array();

        if(!empty($realKeys))
            $realKeys = \Utils::rekey('CONSTRAINT_NAME' , $realKeys);

        if(!empty($configForeignKeys))
        {
            foreach($configForeignKeys as $keyName => $item)
            {
                $realKeysNames[] = $keyName;
                if(! isset($realKeys[$keyName]) && ! $dropOnly)
                    $updates[] = array(
                        'name' => $keyName ,
                        'action' => 'add' ,
                        'config' => $item
                    );
            }
        }

        if(!empty($realKeys))
            foreach($realKeys as $name => $config)
                if(! in_array($name , $realKeysNames , true))
                    $updates[] = array(
                        'name' => $name ,
                        'action' => 'drop'
                    );

        return $updates;
    }

    /**
     * Rename database table
     * @param string $newName - new table name (without prefix)
     * @return boolean
     */
    public function renameTable(string $newName) : bool
    {
        if($this->objectConfig->isLocked() || $this->objectConfig->isReadOnly()) {
            $this->errors[] = 'Can not build locked object ' . $this->objectConfig->getName();
            return false;
        }

        $sql = 'RENAME TABLE `' . $this->model->table() . '` TO `' . $this->model->getDbPrefix() . $newName . '` ;';

        try
        {
            $this->db->query($sql);
            $this->logSql($sql);
            $this->objectConfig->getConfig()->set('table' , $newName);
            $this->model->refreshTableInfo();
            return true;
        }
        catch(\Exception $e)
        {
            $this->errors[] = $e->getMessage() . ' <br>SQL: ' . $sql;
            return false;
        }
    }

    /**
     * Remove object
     * @return boolean
     */
    public function remove()
    {
        if($this->objectConfig->isLocked() || $this->objectConfig->isReadOnly()){
            $this->errors[] = 'Can not remove locked object table ' . $this->objectConfig->getName();
            return false;
        }

        try
        {
            $model = Model::factory($this->objectName);

            if(!$this->tableExists())
                return true;

            $db = $model->getDbConnection();

            $ddl = new Ddl\DropTable($model->table());
            $sql = $db->sql()->buildSqlString($ddl);
            $db->query($sql);
            $this->logSql($sql);
            return true;
        }
        catch(\Exception $e)
        {
            $this->errors[] = $e->getMessage() . ' <br>SQL: ' . $sql;
            return false;
        }
    }

    /**
     * Create Db_Object`s for relations
     * @throw Exception
     * @param $list
     * @return bool
     */
    protected function updateRelations($list) : bool
    {
        $lang = \Lang::lang();
        $usePrefix = true;
        $connection = $this->objectConfig->get('connection');

        $objectModel = Model::factory($this->objectName);
        $db = $objectModel->getDbConnection();
        $tablePrefix = $objectModel->getDbPrefix();

        $oConfigPath = $this->objectConfig->getConfigPath();
        $configDir  = \Dvelum\Config::storage()->getWrite() . $oConfigPath;

        $fieldList = \Dvelum\Config::storage()->get('objects/relations/fields.php');
        $indexesList = \Dvelum\Config::storage()->get('objects/relations/indexes.php');

        if(empty($fieldList)){
            $this->errors[] = 'Cannot get relation fields: ' . 'objects/relations/fields.php';
            return false;
        }

        if(empty($indexesList)){
            $this->errors[] = 'Cannot get relation indexes: ' . 'objects/relations/indexes.php';
            return false;
        }

        $fieldList= $fieldList->__toArray();
        $indexesList = $indexesList->__toArray();

        $fieldList['source_id']['link_config']['object'] = $this->objectName;


        foreach($list as $fieldName=>$info)
        {
            $newObjectName = $info['name'];
            $tableName = $newObjectName;

            $linkedObject = $this->objectConfig->getField($fieldName)->getLinkedObject();

            $fieldList['target_id']['link_config']['object'] = $linkedObject;

            $objectData = [
                'parent_object' => $this->objectName,
                'connection'=>$connection,
                'use_db_prefix'=>$usePrefix,
                'disable_keys' => false,
                'locked' => false,
                'readonly' => false,
                'primary_key' => 'id',
                'table' => $newObjectName,
                'engine' => 'InnoDB',
                'rev_control' => false,
                'link_title' => 'id',
                'save_history' => false,
                'system' => true,
                'fields' => $fieldList,
                'indexes' => $indexesList,
            ];

            $tables = $db->listTables();

            if($usePrefix){
                $tableName = $tablePrefix . $tableName;
            }

            if(in_array($tableName, $tables ,true)){
                $this->errors[] = $lang->get('INVALID_VALUE').' Table Name: '.$tableName .' '.$lang->get('SB_UNIQUE');
                return false;
            }

            if(file_exists($configDir . strtolower($newObjectName).'.php')){
                $this->errors[] = $lang->get('INVALID_VALUE').' Object Name: '.$newObjectName .' '.$lang->get('SB_UNIQUE');
                return false;
            }

            if(!is_dir($configDir) && !@mkdir($configDir, 0755, true)){
                $this->errors[] = $lang->get('CANT_WRITE_FS').' '.$configDir;
                return false;
            }

            /*
             * Write object config
             */
            if(!\Dvelum\Config\File\AsArray::create($configDir. $newObjectName . '.php')){
                $this->errors[] = $lang->get('CANT_WRITE_FS') . ' ' . $configDir . $newObjectName . '.php';
                return false;
            }

            $cfg = \Dvelum\Config::storage()->get($oConfigPath. strtolower($newObjectName).'.php' , false , false);

            if(!$cfg){
                $this->errors[] = 'Undefined config file '.$oConfigPath. strtolower($newObjectName).'.php';
                return false;
            }
            /**
             * @var \Dvelum\Config\File $cfg
             */
            $cfg->setData($objectData);
            $cfg->save();

            $objectConfig = Config::factory($newObjectName);
            $objectConfig->setObjectTitle($lang->get('RELATIONSHIP_MANY_TO_MANY').' '.$this->objectName.' & '.$linkedObject);

            if(!$objectConfig->save()){
                $this->errors[] = $lang->get('CANT_WRITE_FS');
                return false;
            }
            /*
             * Build database
            */
            $builder = new Builder($newObjectName);
            $builder->build();
        }
    }
}
