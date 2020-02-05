<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2018  Kirill Yegorov
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

namespace Dvelum\Orm\Record;

use Dvelum\Config as Cfg;
use Dvelum\Orm;
use Dvelum\Orm\Model;

/**
 * Builder for Orm\Record
 * @package Orm
 * @subpackage Orm\Record
 * @author Kirill Ygorov
 * @license General Public License version 3
 *
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
     * @throws Orm\Exception
     * @return Builder\AbstractAdapter
     */
    static public function factory(string $objectName, bool $forceConfig = true): Builder\AbstractAdapter
    {
        $objectConfig = Config::factory($objectName);

        $adapter = 'Builder_Generic';

        $config = Cfg::factory(\Dvelum\Config\Factory::Simple, $adapter);

        $log = false;
        if (static::$writeLog) {
            $log = new \Dvelum\Log\File\Sql(static::$logsPath . $objectConfig->get('connection') . '-' . static::$logPrefix . '-build.sql');
        }

        $ormConfig = Cfg::storage()->get('orm.php');

        $config->setData([
            'objectName' => $objectName,
            'configPath' => $ormConfig->get('object_configs'),
            'log' => $log,
            'useForeignKeys' => static::$foreignKeys
        ]);

        $model = Model::factory($objectName);
        $platform = $model->getDbConnection()->getAdapter()->getPlatform();

        $platform = $platform->getName();
        $builderAdapter = static::class . '\\' . $platform;

        if (class_exists($builderAdapter)) {
            return new $builderAdapter($config, $forceConfig);
        }

        $builderAdapter = static::class . '\\Generic\\' . $platform;

        if (class_exists($builderAdapter)) {
            return new $builderAdapter($config, $forceConfig);
        }

        throw new Orm\Exception('Undefined Platform');
    }

    public static $booleanTypes = [
        'bool',
        'boolean'
    ];

    public static $numTypes = [
        'tinyint',
        'smallint',
        'mediumint',
        'int',
        'integer',
        'bigint',
        'float',
        'double',
        'decimal',
        'bit',
        'biginteger'
    ];

    public static $intTypes = [
        'tinyint',
        'smallint',
        'mediumint',
        'int',
        'integer',
        'bigint',
        'bit',
        'biginteger'
    ];

    public static $floatTypes = [
        'decimal',
        'float',
        'double'
    ];

    public static $charTypes = [
        'char',
        'varchar'
    ];

    public static $textTypes = [
        'tinytext',
        'text',
        'mediumtext',
        'longtext'
    ];

    public static $dateTypes = [
        'date',
        'datetime',
        'time',
        'timestamp'
    ];

    public static $blobTypes = [
        'tinyblob',
        'blob',
        'mediumblob',
        'longblob'
    ];

    /**
     * Write SQL log
     * @param bool $flag
     * @return void
     */
    static public function writeLog(bool $flag): void
    {
        self::$writeLog = $flag;
    }

    /**
     * Set query log file prefix
     * @param string $string
     * @return void
     */
    static public function setLogPrefix(string $string): void
    {
        self::$logPrefix = strval($string);
    }

    /**
     * Set logs path
     * @param string $string
     * @return void
     */
    static public function setLogsPath(string $string): void
    {
        self::$logsPath = $string;
    }

    /**
     * Use foreign keys
     * @param bool $flag
     * @return void
     */
    static public function useForeignKeys(bool $flag): void
    {
        self::$foreignKeys = $flag;
    }

    /**
     * Check if foreign keys is used
     * @return bool
     */
    static public function foreignKeys(): bool
    {
        return self::$foreignKeys;
    }
}
