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

namespace Dvelum\Db\Metadata;

use Zend\Db\Metadata\Source\MysqlMetadata;
use Zend\Db\Adapter\Adapter;

class Mysql extends MysqlMetadata
{
    protected function loadColumnData($table, $schema)
    {
        if (isset($this->data['columns'][$schema][$table])) {
            return;
        }
        $this->prepareDataHierarchy('columns', $schema, $table);
        $p = $this->adapter->getPlatform();

        $isColumns = [
            ['C', 'ORDINAL_POSITION'],
            ['C', 'COLUMN_DEFAULT'],
            ['C', 'IS_NULLABLE'],
            ['C', 'DATA_TYPE'],
            ['C', 'CHARACTER_MAXIMUM_LENGTH'],
            ['C', 'CHARACTER_OCTET_LENGTH'],
            ['C', 'NUMERIC_PRECISION'],
            ['C', 'NUMERIC_SCALE'],
            ['C', 'COLUMN_NAME'],
            ['C', 'COLUMN_TYPE'],
            ['C', 'EXTRA']
        ];

        array_walk($isColumns, function (&$c) use ($p) {
            $c = $p->quoteIdentifierChain($c);
        });

        $sql = 'SELECT ' . implode(', ', $isColumns)
            . ' FROM ' . $p->quoteIdentifierChain(['INFORMATION_SCHEMA', 'TABLES']) . 'T'
            . ' INNER JOIN ' . $p->quoteIdentifierChain(['INFORMATION_SCHEMA', 'COLUMNS']) . 'C'
            . ' ON ' . $p->quoteIdentifierChain(['T', 'TABLE_SCHEMA'])
            . '  = ' . $p->quoteIdentifierChain(['C', 'TABLE_SCHEMA'])
            . ' AND ' . $p->quoteIdentifierChain(['T', 'TABLE_NAME'])
            . '  = ' . $p->quoteIdentifierChain(['C', 'TABLE_NAME'])
            . ' WHERE ' . $p->quoteIdentifierChain(['T', 'TABLE_TYPE'])
            . ' IN (\'BASE TABLE\', \'VIEW\')'
            . ' AND ' . $p->quoteIdentifierChain(['T', 'TABLE_NAME'])
            . '  = ' . $p->quoteTrustedValue($table);

        if ($schema != self::DEFAULT_SCHEMA) {
            $sql .= ' AND ' . $p->quoteIdentifierChain(['T', 'TABLE_SCHEMA'])
                . ' = ' . $p->quoteTrustedValue($schema);
        } else {
            $sql .= ' AND ' . $p->quoteIdentifierChain(['T', 'TABLE_SCHEMA'])
                . ' != \'INFORMATION_SCHEMA\'';
        }

        $results = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $columns = [];
        foreach ($results->toArray() as $row) {
            $erratas = [];
            $matches = [];
            if (preg_match('/^(?:enum|set)\((.+)\)$/i', $row['COLUMN_TYPE'], $matches)) {
                $permittedValues = $matches[1];
                if (preg_match_all(
                    "/\\s*'((?:[^']++|'')*+)'\\s*(?:,|\$)/",
                    $permittedValues,
                    $matches,
                    PREG_PATTERN_ORDER
                )
                ) {
                    $permittedValues = str_replace("''", "'", $matches[1]);
                } else {
                    $permittedValues = [$permittedValues];
                }
                $erratas['permitted_values'] = $permittedValues;
            }

            if(strpos($row['EXTRA'],'auto_increment')!==false){
                $isAutoIncrement = true;
            }else{
                $isAutoIncrement = false;
            }

            $columns[$row['COLUMN_NAME']] = [
                'ordinal_position'          => $row['ORDINAL_POSITION'],
                'column_default'            => $row['COLUMN_DEFAULT'],
                'is_nullable'               => ('YES' == $row['IS_NULLABLE']),
                'data_type'                 => $row['DATA_TYPE'],
                'character_maximum_length'  => $row['CHARACTER_MAXIMUM_LENGTH'],
                'character_octet_length'    => $row['CHARACTER_OCTET_LENGTH'],
                'numeric_precision'         => $row['NUMERIC_PRECISION'],
                'numeric_scale'             => $row['NUMERIC_SCALE'],
                'numeric_unsigned'          => (false !== strpos($row['COLUMN_TYPE'], 'unsigned')),
                'erratas'                   => $erratas,
                'auto_increment'            => $isAutoIncrement
            ];
        }
        $this->data['columns'][$schema][$table] = $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($columnName, $table, $schema = null)
    {
        if ($schema === null) {
            $schema = $this->defaultSchema;
        }

        $this->loadColumnData($table, $schema);

        if (! isset($this->data['columns'][$schema][$table][$columnName])) {
            throw new \Exception('A column by that name was not found.');
        }

        $info = $this->data['columns'][$schema][$table][$columnName];

        $column = new ColumnObject($columnName, $table, $schema);
        $props = [
            'ordinal_position', 'column_default', 'is_nullable',
            'data_type', 'character_maximum_length', 'character_octet_length',
            'numeric_precision', 'numeric_scale', 'numeric_unsigned',
            'erratas'
        ];
        foreach ($props as $prop) {
            if (isset($info[$prop])) {
                $column->{'set' . str_replace('_', '', $prop)}($info[$prop]);
            }
        }

        $column->setOrdinalPosition($info['ordinal_position']);
        $column->setColumnDefault($info['column_default']);
        $column->setIsNullable($info['is_nullable']);
        $column->setDataType($info['data_type']);
        $column->setCharacterMaximumLength($info['character_maximum_length']);
        $column->setCharacterOctetLength($info['character_octet_length']);
        $column->setNumericPrecision($info['numeric_precision']);
        $column->setNumericScale($info['numeric_scale']);
        $column->setNumericUnsigned($info['numeric_unsigned']);
        $column->setErratas($info['erratas']);
        $column->setAutoIncrement($info['auto_increment']);

        return $column;
    }
}
