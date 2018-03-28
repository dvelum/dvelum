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

namespace Dvelum\Orm\Record\Builder;

use Dvelum\Orm;
use Dvelum\Orm\Record\Config;
use Dvelum\Orm\Model;
use Dvelum\Log;
use Dvelum\Config\ConfigInterface;
use Zend\Db\Sql\Ddl;


abstract class AbstractAdapter implements BuilderInterface
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var \Dvelum\Db\Adapter
     */
    protected $db;

    /**
     * @var string $objectName
     */
    protected $objectName;

    /**
     * @var Orm\Record\Config
     */
    protected $objectConfig;

    /**
     * @var string
     */
    protected $dbPrefix;

    /**
     * @var Log\File
     */
    protected $log = false;

    /**
     * @var bool
     */
    protected $useForeignKeys = false;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string $configPath
     */
    protected $configPath;

    abstract public function prepareColumnUpdates();
    abstract public function prepareIndexUpdates();
    abstract public function prepareKeysUpdate();

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->configPath = $config->get('configPath');
        $this->objectName = $config->get('objectName');

        if($config->offsetExists('log') && $config->get('log') instanceof Log\File){
            $this->log = $config->get('log');
        }

        if($config->offsetExists('useForeignKeys')){
            $this->useForeignKeys = $config->get('useForeignKeys');
        }

        $this->model = Model::factory($this->objectName);
        $this->db = $this->model->getDbConnection();
        $this->dbPrefix = $this->model->getDbPrefix();
        $this->objectConfig = Orm\Record\Config::factory($this->objectName);
    }

    /**
     * Get error messages
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check for broken object links
     * @return array
     */
    public function getBrokenLinks() : array
    {
        $links = $this->objectConfig->getLinks();

        if(empty($links)){
            return [];
        }

        $brokenFields = [];
        foreach($links as $o => $fieldList)
        {
            if(!Config::configExists($o))
            {
                foreach($fieldList as $field => $cfg)
                {
                    $brokenFields[$field] = $o;
                }
            }
        }
        return $brokenFields;
    }

    /**
     * Check if DB table has correct structure
     * @return bool
     */
     public function validate() : bool
     {
         if(!$this->tableExists() || !$this->checkRelations()){
             return false;
         }
         // Check columns
         $updateColumns = $this->prepareColumnUpdates();
         // Column changes
         if(!empty($updateColumns)){
             return false;
         }

         // Check indexes
         $updateIndexes = $this->prepareIndexUpdates();
         // Index changes
         if(!empty($updateIndexes)){
             return false;
         }

         $updateKeys = [];
         if($this->useForeignKeys){
             $updateKeys = $this->prepareKeysUpdate();
         }

         if(!empty($updateColumns) || !empty($updateIndexes) || !empty($updateKeys))
             return false;
         else
             return true;
     }

    /**
     * Get Existing Columns
     * @return \Zend\Db\Metadata\Object\TableObject
     */
    protected function getExistingColumns()
    {
        return $this->db->getMeta()->getAdapter()->getColumns($this->model->table());
    }


    /**
     * Check if table exists
     * @param string $name - optional, table name,
     * @param boolean $addPrefix - optional append prefix, default false
     * @return boolean
     */
    public function tableExists(string $name = '', bool $addPrefix = false) : bool
    {
        if(empty($name))
            $name = $this->model->table();

        if($addPrefix)
            $name = $this->model->getDbPrefix() . $name;

        try{
            $tables = $this->db->listTables();
        }catch(\Exception $e){
            return false;
        }

        return in_array($name , $tables , true);
    }

    /**
     * Check relation objects
     */
    protected function checkRelations()
    {
        $list = $this->objectConfig->getManyToMany();

        if(empty($list)){
            return true;
        }

        foreach($list as $objectName=>$fields)
        {
            if(empty($fields)) {
                continue;
            }

            foreach($fields as $fieldName=>$linkType)
            {
                $relationObjectName = $this->objectConfig->getRelationsObject($fieldName);
                if(!Config::configExists($relationObjectName)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Log queries
     * @param string $sql
     * @return bool
     */
    protected function logSql(string $sql) : bool
    {
        if(!$this->log)
            return true;

        try{
            $this->log->info('--');
            $this->log->info('--' . date('Y-m-d H:i:s'));
            $this->log->info('--');
            $this->log->info($sql);
        }catch (\Error $e){
            $this->errors[] = 'Cant write to log file '.$this->log->getFileName();
            return false;
        }

        return true;
    }

    /**
     * Get object foreign keys
     * @return array
     */
    public function getOrmForeignKeys() : array
    {
        if(!$this->useForeignKeys)
            return [];

        $data = $this->objectConfig->getForeignKeys();
        $keys = [];

        if(!empty($data))
        {
            foreach($data as $item)
            {
                $keyName = $this->createForeignKeyName($item);
                $keys[$keyName] = $item;
            }
        }
        return $keys;
    }

    /**
     * Generate index name  for constraint key
     * Mysql limits with 64 chars
     * @param  array $item
     * @return string
     */
    public function createForeignKeyName(array $item): string
    {
        $key = $item['curDb'].'.'.$item['curTable'].'.'.$item['curField'] .
               '-' .
                $item['toDb'].'.'.$item['toTable'].'.'.$item['toField'];

        if(mb_strlen($key,'utf-8') > 64){
            $key = md5($key);
        }
        return $key;
    }

    /**
     * Get updates information
     * @return array
     */
    public function getRelationUpdates() : array
    {
        $updates = [];
        $list = $this->objectConfig->getManyToMany();
        foreach($list as $objectName=>$fields)
        {
            if(!empty($fields)){
                foreach($fields as $fieldName=>$linkType){
                    $relationObjectName = $this->objectConfig->getRelationsObject($fieldName);
                    if(!Config::configExists($relationObjectName)){
                        $updates[$fieldName] = ['name' => $relationObjectName, 'action'=>'add'];
                    }
                }
            }
        }
        return $updates;
    }

    /**
     * Check for broken object links
     * return array | boolean false
     */
    public function hasBrokenLinks()
    {
        $links = $this->objectConfig->getLinks();
        $brokenFields = [];

        if(!empty($links))
        {
            $brokenFields = [];
            foreach($links as $o => $fieldList){
                if(!Config::configExists($o)){
                    foreach($fieldList as $field => $cfg)
                        $brokenFields[$field] = $o;
                }

            }
        }

        if(empty($brokenFields))
            return false;
        else
            return $brokenFields;
    }

    /**
     * Remove object
     * @return bool
     */
    public function remove() : bool
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
        catch(\Throwable $e)
        {
            $this->errors[] = $e->getMessage() . ' <br>SQL: ' . $sql;
            return false;
        }
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

        $fieldConfig = $this->objectConfig->getField($newName);

        $sql = ' ALTER TABLE ' . $this->model->table() . ' CHANGE `' . $oldName . '` ' . $this->getPropertySql($newName , $fieldConfig);

        try
        {
            $this->db->query($sql);
            $this->logSql($sql);
            return true;
        }
        catch(\Throwable $e)
        {
            echo $e->getMessage();
            $this->errors[] = $e->getMessage() . ' <br>SQL: ' . $sql;
            return false;
        }
    }

   /**
     * Get property SQL query
     * @param string $name
     * @param Orm\Record\Config\Field $field
     * @return string
     */
    abstract protected function getPropertySql(string $name , Orm\Record\Config\Field $field) : string;
}
