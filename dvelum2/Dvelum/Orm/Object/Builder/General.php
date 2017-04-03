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

abstract class General extends AbstractAdapter
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

            $dataType = strtolower($column->getDataType());

            if(!isset($this->types[$dataType])){
                throw new Exception('Undefined data type: '.$dataType);
            }

            $objectField = $this->objectConfig->getField($name);
            $dataType = $this->types[$dataType];


            if(!isset($v['type']) || empty($v['type']))
            {
                $objectDataType = $this->types[$v['db_type']];
            }
            else
            {
                if($objectField->isDictionaryLink()){
                    $objectDataType = 'varchar';
                }elseif($objectField->isMultiLink()){
                    continue;
                }elseif ($objectField->isObjectLink()){
                    $objectDataType = 'biginteger';
                }elseif($objectField->isEncrypted()){
                    $objectDataType = 'text';
                }else{
                    $objectDataType = $v['type'];
                }
            }

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
            if($dataType !== $objectDataType){
                $typeCmp = true;
            }

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

    public function prepareKeysUpdate(){}
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


        if(empty($indexes) && empty($objectKeys)){
            return [];
        }


        foreach($indexes as $k => $v)
        {
            /**
             * @var Metadata\Object\ConstraintObject $v
             */
            $keyName = implode('_', $v->getColumns());

            if(!isset($realIndexes[$keyName]))
            {
                $realIndexes[$keyName] = [
                    'columns' => $v->getColumns(),
                    'unique' => $v->isUnique()
                ];
            }
        }

        $cmd = [];

        /*
         * Get indexes for Foreign Keys
         */
        $foreignKeys = $this->getOrmForeignKeys();
        /*
         * Drop invalid indexes
         */
        foreach($realIndexes as $index => $conf)
            if(!isset($configIndexes[$index]) && ! isset($foreignKeys[$index]))
                $updates[] = array(
                    'name' => $index ,
                    'action' => 'drop'
                );

        /*
       * Compare DB and Config indexes, create if not exist, drop and create if
       * invalid
       */
        if(!empty($configIndexes))
        {
            foreach($configIndexes as $index => $config)
            {
                if(! array_key_exists((string) $index , $realIndexes))
                {
                    $updates[] = array(
                        'name' => $index ,
                        'action' => 'add'
                    );
                }
                else
                {
                    if(!$this->_isSameIndexes($config , $realIndexes[$index]))
                    {
                        $updates[] = array(
                            'name' => $index ,
                            'action' => 'drop'
                        );
                        $updates[] = array(
                            'name' => $index ,
                            'action' => 'add'
                        );
                    }
                }
            }
        }
        return $updates;
    }
}