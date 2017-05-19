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

namespace Dvelum\Orm\Object\Builder;

use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Log;
use Dvelum\Config\ConfigInterface;


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
     * @var Orm\Object\Config
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

    abstract public function prepareColumnUpdates();
    abstract public function prepareIndexUpdates();
    abstract public function prepareKeysUpdate();

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
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
        $this->objectConfig = Orm\Object\Config::factory($this->objectName);
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
        $brokenFields = [];
        $links = $this->objectConfig->getLinks();

        if(empty($links)){
            return [];
        }

        $brokenFields = [];
        foreach($links as $o => $fieldList)
        {
            if(!Orm\Object\Config::configExists($o))
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
     *
     * @return \Zend\Db\Metadata\Object\TableObject
     */
    protected function getExistingColumns()
    {
        return $this->db->describeTable($this->model->table());
    }


    /**
     * Check if table exists
     * @param string $name - optional, table name,
     * @param boolean $addPrefix - optional append prefix, default false
     * @return boolean
     */
    protected function tableExists(string $name = '', bool $addPrefix = false) : bool
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
                if(!Orm\Object\Config::configExists($relationObjectName)) {
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
                $keyName = md5(implode(':' , $item));
                $keys[$keyName] = $item;
            }
        }
        return $keys;
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
        $brokenFields = array();
        if(!empty($links))
        {
            $brokenFields = array();
            foreach($links as $o => $fieldList)
                if(! Orm\Object\Config::configExists($o))
                    foreach($fieldList as $field => $cfg)
                        $brokenFields[$field] = $o;
        }
        if(empty($brokenFields))
            return false;
        else
            return $brokenFields;
    }
}
