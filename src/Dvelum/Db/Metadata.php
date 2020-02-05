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

namespace Dvelum\Db;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Object\ColumnObject;
use Zend\Db\Metadata\Object\ConstraintObject;
use Dvelum\Db\Metadata\Factory;

class Metadata
{
    /**
     * @var MetadataInterface $metadata
     */
    protected $metadata;

    public function __construct(AdapterInterface $db)
    {
        $this->metadata = Factory::createSourceFromAdapter($db);
    }

    public function findPrimaryKey(string $tableName) : ?string
    {
        foreach ($this->metadata->getConstraints($tableName) as $constraint) {
            /**
             * @var Db\Metadata\Object\ConstraintObject $constraint
             */
            if (!$constraint->hasColumns()) {
                continue;
            }
            if ($constraint->isPrimaryKey()) {
                return $constraint->getColumns()[0];
            }
        }
        return null;
    }

    public function getAdapter() : MetadataInterface
    {
        return $this->metadata;
    }

    /**
     * @return string[]
     */
    public function getTableNames(): array
    {
        return $this->metadata->getTableNames();
    }

    /**
     * @param string $tableName
     * @return array
     */
    public function getColumns(string $tableName) : array
    {
        $data = [];
        foreach ($this->metadata->getColumns($tableName) as $column){
            /**
             * @var Db\Metadata\Object\ColumnObject $column
             */
            $name = $column->getName();
            $data[$name] = $column;
        }
        return $data;
    }

    /**
     * @param string $tableName
     * @return ConstraintObject[]
     */
    public function getConstraints(string $tableName) : array
    {
        return $this->metadata->getConstraints($tableName);
    }

    public function getColumnsAsArray(string $tableName) : array
    {
        $data = [];
        foreach ($this->metadata->getColumns($tableName) as $column){
            /**
             * @var Db\Metadata\Object\ColumnObject $column
             */
            $name = $column->getName();
            $data[$name] = [
              'name' => $name,
              'data_type'=> $column->getDataType(),
              'erratas' =>$column->getErratas(),
              'max_len'  => $column->getCharacterMaximumLength(),
              'octet_length'  => $column->getCharacterOctetLength(),
              'default' => $column->getColumnDefault(),
              'null' => $column->getIsNullable(),
              'unsigned' => $column->getNumericUnsigned(),
              'scale'=> $column->getNumericScale(),
              'precision'=> $column->getNumericPrecision()
            ];
        }
        return $data;
    }

    public function indexHashByColumns(array $columns) : string
    {
        return implode('_', $columns);
    }
}