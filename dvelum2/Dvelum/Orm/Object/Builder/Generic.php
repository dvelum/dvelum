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
use Dvelum\Orm\Exception;
use Dvelum\Orm\Object\Builder;

use Zend\Db\Metadata;

abstract class Generic extends AbstractAdapter
{
    protected $types = [];

    const TYPE_BIGINTEGER = 'biginteger';
    const TYPE_BLOB 	= 'blob';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_CHAR = 'char';
    const TYPE_DATE = 'date';
    const TYPE_DECIMAL  = 'decimal';
    const TYPE_FLOATING  = 'floating';
    const TYPE_INTEGER 	= 'integer';
    const TYPE_TEXT  =  'text';
    const TYPE_TIME	= 'time';
    const TYPE_VARCHAR = 'varchar';

    public function prepareColumnUpdates()
    {
        $config = $this->objectConfig->__toArray();
        $updates = [];

        if(!$this->tableExists())
            $fields = [];
        else
            $fields = $this->getExistingColumns()->getColumns();

        /**
         * @var \Zend\Db\Metadata\Object\ColumnObject $column
         */
        $columns = [];
        foreach ($fields as $column){
            $columns[$column->getName()] = $column;
        }

        // except virtual fields
        foreach($config['fields'] as $field=>$cfg){
            if($this->objectConfig->getField($field)->isVirtual()){
                unset($config['fields'][$field]);
            }
        }

        /*
         * Remove deprecated fields
         */
        foreach($columns as $name=>$column)
        {
            if(!isset($config['fields'][$name]))
            {
                $updates[] = array(
                    'name' => $name ,
                    'action' => 'drop' ,
                    'type' => 'field'
                );
            }
        }

        foreach($config['fields'] as $name => $v)
        {
            /*
             * Add new field
             */
            if(!isset($columns[$name]))
            {
                $updates[] = array(
                    'name' => $name ,
                    'action' => 'add'
                );
                continue;
            }

            $column = $columns[$name];

            $objectField = $this->objectConfig->getField($name);
            // MultiLink field has no DB representation
            if($objectField->isMultiLink()){
                continue;
            }

            $dataTypes = $this->getDataTypes($column,  $objectField);

            $dataType = $dataTypes[0];
            $objectDataType = $dataTypes[1];
            /*
             * Field type compare flag
             */
            $typeCmp = false;
            /*
             * Field length compare flag
             */
            $lenCmp = false;
            /*
             * IsNull compare flag
             */
            $nullCmp = false;
            /*
             * Default value compare flag
             */
            $defaultCmp = false;

            /**
             * AUTO_INCREMENT compare flag
             * @var bool
             */
            $incrementCmp = false;

            /*
             * Different data types
             */
            $typeCmp = $this->compareTypes($column, $objectField, $dataTypes);


            if(in_array($dataType, Builder::$floatTypes , true))
            {
                /*
                 * @note ZF3 has inverted scale and precision values
                 */
                if((int) $v['db_scale'] != (int) $column->getNumericPrecision() || (int) $v['db_precision'] != (int) $column->getNumericScale()){
                    $lenCmp = true;
                }

            }
            elseif(in_array($dataType , Builder::$numTypes , true) && isset(Orm\Object\Field\Property::$numberLength[$v['db_type']]))
            {

                // $lenCmp = (int) Orm\Object\Field\Property::$numberLength[$v['db_type']] != (int) $column->getNumericPrecision();
            }
            elseif($objectField->isBoolean()) {

            }
            else
            {
                if(isset($v['db_len']))
                    $lenCmp = (int) $v['db_len'] != (int) $column->getCharacterMaximumLength();
            }

            /*
              Auto set default '' for NOT NULL string properties
              if(in_array($v['db_type'] , self::$charTypes , true) && (! isset($v['db_isNull']) || ! $v['db_isNull']) && (! isset($v['db_default']) || $v['db_default'] === false))
              {
                $v['db_default'] = '';
              }
            */

            if(in_array($dataType , Builder::$textTypes , true))
            {
                if(isset($v['required']) && $v['required'])
                    $v['db_isNull'] = false;
                else
                    $v['db_isNull'] = true;
            }

            $nullCmp = (boolean) $v['db_isNull'] !==  $column->isNullable();


            if(!((boolean) $v['db_isNull']) && ! in_array($dataType, Builder::$dateTypes , true) && ! in_array($dataType , Builder::$textTypes , true))
            {
                if((!isset($v['db_default']) || $v['db_default'] === false) && !is_null($column->getColumnDefault())){
                    $defaultCmp = true;
                }
                if(isset($v['db_default']))
                {
                    if((is_null($column->getColumnDefault()) && $v['db_default'] !== false) || (! is_null($column->getColumnDefault()) && $v['db_default'] === false))
                        $defaultCmp = true;
                    else
                        $defaultCmp = (string) $v['db_default'] != (string) $column->getColumnDefault();
                }
            }

            /**
             * @todo migrate identity
             */
            //            if($fields[$name]['IDENTITY'] && $name != $this->objectConfig->getPrimaryKey())
            //                $incrementCmp = true;
            //
            //            if($name == $this->objectConfig->getPrimaryKey() && ! $fields[$name]['IDENTITY'])
            //                $incrementCmp = true;



            /*
             * If not passed at least one comparison then rebuild the the field
             */
            if($typeCmp || $lenCmp || $nullCmp || $defaultCmp  || $incrementCmp)
            {
                $updates[] = array(
                    'name' => $name ,
                    'action' => 'change',
                    'info' => [
                        'object' => $this->objectName,
                        'cmp_flags' =>[
                            'type' => (boolean) $typeCmp,
                            'length' => (boolean) $lenCmp,
                            'null' => (boolean) $nullCmp,
                            'default' => (boolean) $defaultCmp,
                            'increment' => (boolean) $incrementCmp
                        ]
                    ]
                );
            }
        }

        return $updates;
    }

    /**
     * Compare data types. If types are different, then return true
     * @param Metadata\Object\ColumnObject $column
     * @param Orm\Object\Config\Field $objectField
     * @param array $dataTypes
     * @return bool
     */
    protected function compareTypes(Metadata\Object\ColumnObject $column, Orm\Object\Config\Field $objectField, $dataTypes) : bool
    {
        /*
         * Different data types
         */
        if($dataTypes[0] !== $dataTypes[1]){
            return true;
        }
        return false;
    }

    /**
     * Get local representation of column data type
     * @param Metadata\Object\ColumnObject $column
     * @param Orm\Object\Config\Field $objectField
     * @return array  [$dataType, $objectDataType]
     * @throws \Exception
     */
    protected function getDataTypes(Metadata\Object\ColumnObject $column, Orm\Object\Config\Field $objectField) : array
    {
        $dataType = strtolower($column->getDataType());

        $type = $objectField->getType();

        if(!isset($this->types[$dataType])){
            throw new \Exception('Undefined data type: '.$dataType);
        }

        $dataType = $this->types[$dataType];

        if(empty($type))
        {
            $objectDataType = $this->types[$objectField->getDbType()];
        }
        else
        {
            if($objectField->isDictionaryLink()){
                $objectDataType = 'varchar';
            }elseif ($objectField->isObjectLink()){
                $objectDataType = 'biginteger';
            }elseif($objectField->isEncrypted()){
                $objectDataType = 'text';
            }else{
                $objectDataType = $this->types[$objectField->getDbType()];
            }
        }

        return [$dataType, $objectDataType];
    }

    /**
     * Prepare list of Foreign Keys to be updated
     * @param bool $dropOnly
     * @return array
     */
    public function prepareKeysUpdate($dropOnly = false) : array
    {
        $updates = [];

        /**
         * @var Metadata\Object\ConstraintObject[] $indexes
         */

        /**
         * @var Metadata\Object\ConstraintObject[] $realKeys
         */

        /*
        * Get foreign keys form database table
        */
        $indexes = $this->getExistingColumns()->getConstraints();
        $dbList = [];
        $updatedList = [];

        foreach($indexes as $k => $v)
        {
            /**
             * @var Metadata\Object\ConstraintObject $v
             */
            if($v->isForeignKey()){
                $keyName = $v->getName();
                $dbList[$keyName] = $v;
            }
        }

        /*
         * Get foreign keys form ORM
         */
        $configForeignKeys = $this->getOrmForeignKeys();

        if(!empty($configForeignKeys))
        {
            foreach($configForeignKeys as $keyName => $item)
            {
                $updatedList[] = $keyName;
                if(!isset($dbList[$keyName]) && ! $dropOnly)
                {
                    $updates[] = array(
                        'name' => $keyName ,
                        'action' => 'add' ,
                        'config' => $item
                    );
                }
            }
        }

        if(!empty($dbList))
        {
            foreach($dbList as $name => $config)
            {
                if (!in_array($name, $updatedList, true)) {
                    $updates[] = array(
                        'name' => $name,
                        'action' => 'drop'
                    );
                }
            }
        }

        return $updates;
    }

    /**
     * Get index configuration
     * @param Metadata\Object\ConstraintObject $object
     * @return array
     */
    protected function getIndexConfig(Metadata\Object\ConstraintObject $object) : array
    {
        return [
            'columns' => $object->getColumns(),
            'unique' => $object->isUnique(),
            'primary' => $object->isPrimaryKey()
        ];
    }

    /**
     * Prepare list of indexes to be updated
     * @return array (
     *         'name'=>'indexName',
     *         'action'=>[drop/add],
     *         )
     */
    public function prepareIndexUpdates() : array
    {
        $updates = [];

        /*
         * Get indexes from object config
         */
        $configKeys = $this->objectConfig->getIndexesConfig();
        /*
         * Get indexes for Foreign Keys
         */
        $foreignKeys = $this->getOrmForeignKeys();
        $objectKeys = [];
        if(!empty($configKeys)){
            foreach ($configKeys as $item){
                $hash = implode('_', $item['columns']);
                $objectKeys[$hash] = $item;
            }
        }

        /**
         * @var Metadata\Object\ConstraintObject[] $indexes
         */
        $indexes = $this->getExistingColumns()->getConstraints();


        $realIndexes = [];

        /**
         * @todo add foreign key check
         */
        if(empty($indexes) && empty($objectKeys)){
            return [];
        }

        foreach($indexes as $k => $v)
        {
            /**
             * @var Metadata\Object\ConstraintObject $v
             */

            if($v->isForeignKey()){

                $keyName = $v->getName();
                $realIndexes[$keyName] = [
                    'foreignKey' => true
                ];
            }
            else
            {
                $keyName = $this->getIndexId($v->getColumns());
                if(!isset($realIndexes[$keyName])) {
                    $realIndexes[$keyName] = $this->getIndexConfig($v);
                }
            }
        }

        /*
         * Get indexes from object config
         */
        $configIndexes = [];
        $configIndexesData = $this->objectConfig->getIndexesConfig();
        if(!empty($configIndexesData))
        {
            foreach ($configIndexesData  as $item)
            {
                $configIndexes[$this->getIndexId($item['columns'])] = $item;
            }
        }
        $cmd = [];

        /*
         * Drop invalid indexes
         */
        foreach($realIndexes as $index => $conf)
        {
            if(!isset($configIndexes[$index]) && !isset($foreignKeys[$index]))
            {
                $updates[] = [
                    'name' => $index ,
                    'action' => 'drop'
                ];
            }
        }

        /*
         * Compare DB and Config indexes, create if not exist, drop and create if
         * invalid
         */
        if(!empty($configIndexes))
        {
            foreach($configIndexes as $index => $config)
            {
                if(!array_key_exists((string) $index , $realIndexes))
                {
                    $updates[] = array(
                        'name' => $index ,
                        'action' => 'add'
                    );
                }
                else
                {
                    if(!$this->isSameIndexes($config , $realIndexes[$index]))
                    {
                        $updates[] = array(
                            'name' => $index ,
                            'action' => 'drop'
                        );

                        if(!isset($realIndexes[$index]['foreignKey'])){
                            $updates[] = array(
                                'name' => $index ,
                                'action' => 'add'
                            );
                        }
                    }
                }
            }
        }
        return $updates;
    }

    /**
     * Generate unique id for Foreign Key
     * @param string $fromDb
     * @param string $fromTable
     * @param string $fromField
     * @param string $toDb
     * @param string $toTable
     * @param string $toField
     * @return string
     */
    protected function getForeignKeyId(string $fromDb, string $fromTable, string $fromField, string$toDb, string $toTable, string $toField)
    {
        return md5($fromDb.'.'.$fromTable.'.'.$fromField.'-'.$toDb.'.'.$toTable.'.'.$toField);
    }

    /**
     * Generate unique id for Index
     * @param array $columns
     * @return string
     */
    protected function getIndexId(array $columns) : string
    {
        return implode('_', $columns);
    }

    /**
     * Compare existed index and its system config
     * @param array $cfg1
     * @param array $cfg2
     * @return boolean
     */
    protected function isSameIndexes(array $cfg1 , array $cfg2)
    {
        $colDiff = array_diff($cfg1['columns'] , $cfg2['columns']);
        $colDiffReverse = array_diff($cfg2['columns'] , $cfg1['columns']);

        if(is_string($cfg1['unique'])){
            $cfg1['unique'] = (bool) strlen($cfg1['unique']);
        }

        if(is_string($cfg2['unique'])){
            $cfg2['unique'] = (bool) strlen($cfg2['unique']);
        }

        if((bool) $cfg1['unique'] !== (bool) $cfg2['unique'] || ! empty($colDiff) || !empty($colDiffReverse))
            return false;

        return true;
    }
}