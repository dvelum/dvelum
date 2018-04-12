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
namespace Dvelum\Log;

/**
 * Database log
 * Class Db
 * @package Dvelum\Log
 */
class Db extends \Psr\Log\AbstractLogger implements \Log
{
    /**
     * Database Table
     * @var string
     */
    protected $table;
    /**
     * Database connection
     * @var \Dvelum\Db\Adapter
     */
    protected $db;
    /**
     * Log name
     * @var string
     */
    protected $name;

    protected $logFields = array(
        'name'=>'name',
        'message'=>'message',
        'date'=>'date',
        'level'=>'level',
        'context'=>'context'
    );
    protected $lastError = '';

    /**
     * Db constructor.
     * @param string $logName
     * @param \Dvelum\Db\Adapter $dbConnection
     * @param $tableName
     */
    public function __construct(string $logName , \Dvelum\Db\Adapter $dbConnection , string $tableName)
    {
        $this->name = $logName;
        $this->table = $tableName;
        $this->db = $dbConnection;
    }

    public function log($level, $message, array $context = array())
    {
        try{
            $this->db->insert(
                $this->table,
                [
                    $this->logFields['name'] => $this->name,
                    $this->logFields['message'] => $message,
                    $this->logFields['date']=> date('Y-m-d H:i:s'),
                    $this->logFields['level']=> json_encode($context)
                ]
            );
            return true;
        }catch (\Exception $e){
            echo  $e->getMessage(); die();
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Set database adapter
     * @param \Dvelum\Db\Adapter $db
     */
    public function setDbConnection(\Dvelum\Db\Adapter $db)
    {
        $this->db = $db;
    }
    /**
     * Get last error
     * @return string
     */
    public function getLastError() : string
    {
        return $this->lastError;
    }

    /**
     * Set DB table
     * @param string $table
     */
    public function setTable(string $table)
    {
        $this->table = $table;
    }
}