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

namespace Dvelum\Orm\Record\Builder\Generic;

use Dvelum\Orm;
use Dvelum\Orm\Record\Builder;

class Pgsql extends Builder\Generic
{
    protected $types = [
        'bit' => self::TYPE_INTEGER,
        'bit varying' => self::TYPE_INTEGER,
        'varbit' => self::TYPE_INTEGER,
        'bool' => self::TYPE_BOOLEAN,
        'boolean' => self::TYPE_BOOLEAN,
        'box' => self::TYPE_VARCHAR,
        'circle' => self::TYPE_VARCHAR,
        'point' => self::TYPE_VARCHAR,
        'line' => self::TYPE_VARCHAR,
        'lseg' => self::TYPE_VARCHAR,
        'polygon' => self::TYPE_VARCHAR,
        'path' => self::TYPE_VARCHAR,
        'character' => self::TYPE_CHAR,
        'char' => self::TYPE_CHAR,
        'bpchar' => self::TYPE_CHAR,
        'character varying' => self::TYPE_VARCHAR,
        'varchar' => self::TYPE_VARCHAR,
        'text' => self::TYPE_TEXT,
        'bytea' => self::TYPE_BLOB,
        'cidr' => self::TYPE_VARCHAR,
        'inet' => self::TYPE_VARCHAR,
        'macaddr' => self::TYPE_VARCHAR,
        'real' => self::TYPE_FLOATING,
        'float4' => self::TYPE_FLOATING,
        'double precision' => self::TYPE_FLOATING,
        'float8' => self::TYPE_FLOATING,
        'decimal' => self::TYPE_DECIMAL,
        'numeric' => self::TYPE_DECIMAL,
        'money' => self::TYPE_FLOATING,
        'smallint' => self::TYPE_INTEGER,
        'int2' => self::TYPE_INTEGER,
        'int4' => self::TYPE_INTEGER,
        'int' => self::TYPE_INTEGER,
        'integer' => self::TYPE_INTEGER,
        'bigint' => self::TYPE_BIGINTEGER,
        'int8' => self::TYPE_BIGINTEGER,
        'oid' => self::TYPE_BIGINTEGER,
        'smallserial' => self::TYPE_INTEGER,
        'serial2' => self::TYPE_INTEGER,
        'serial4' => self::TYPE_INTEGER,
        'serial' => self::TYPE_INTEGER,
        'bigserial' => self::TYPE_BIGINTEGER,
        'serial8' => self::TYPE_BIGINTEGER,
        'pg_lsn' => self::TYPE_BIGINTEGER,
        'date' => self::TYPE_DATE,
        'interval' => self::TYPE_VARCHAR,
        'time without time zone' => self::TYPE_TIME,
        'time' => self::TYPE_TIME,
        'time with time zone' => self::TYPE_TIME,
        'timetz' => self::TYPE_TIME,
        'timestamp without time zone' => self::TYPE_BIGINTEGER,
        'timestamp' => self::TYPE_BIGINTEGER,
        'timestamp with time zone' => self::TYPE_BIGINTEGER,
        'timestamptz' => self::TYPE_BIGINTEGER,
        'abstime' => self::TYPE_BIGINTEGER,
        'tsquery' => self::TYPE_BIGINTEGER,
        'tsvector' => self::TYPE_BIGINTEGER,
        'txid_snapshot' => self::TYPE_VARCHAR,
        'unknown' => self::TYPE_TEXT,
        'uuid' => self::TYPE_VARCHAR,
        'json' => self::TYPE_TEXT,
        'jsonb' => self::TYPE_TEXT,
        'xml' => self::TYPE_TEXT,
    ];

    /**
     * Create / alter db table
     * @param bool $buildForeignKeys
     * @param bool $buildShard
     * @return bool
     */
    public function build(bool $buildForeignKeys = true, bool $buildShard = false) : bool
    {
        // TODO: Implement build() method.
    }

    public function buildForeignKeys($remove = true, $create = true): bool
    {
        // TODO: Implement buildForeignKeys() method.
    }

    public function getPropertySql(string $name, Orm\Record\Config\Field $field): string
    {
        // TODO: Implement getPropertySql() method.
    }
}