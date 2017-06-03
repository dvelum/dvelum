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

use Zend\Db;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

/**
 * Class Adapter
 * Db adapter proxy
 */
class Adapter
{
    protected $params;
    protected $adapter;

    public function __construct($params)
    {
        $this->params = $params;
        $this->adapter = new \Zend\Db\Adapter\Adapter($params);
    }

    /**
     * @return Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return Select
     */
    public function select()
    {
        $select = new Select();
        if($this->params['adapter'] === 'Mysqli'){
            $select->setMysqli($this->adapter->getDriver()->getConnection()->getResource());
        }
        return $select;
    }

    /**
     * @return Sql
     */
    public function sql() : Sql
    {
        return  new Sql($this->adapter);
    }

    /**
     * Get Query profiler
     * @return Db\Adapter\Profiler\ProfilerInterface|null
     */
    public function getProfiler(): ?Db\Adapter\Profiler\ProfilerInterface
    {
        return $this->adapter->getProfiler();
    }

    /**
     * Fetch results
     * @param $sql
     * @return array
     */
    public function fetchAll($sql) : array
    {
        $statement = $this->adapter->createStatement();
        $statement->prepare($sql);

        $result = $statement->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult())
        {
            /*
             *  Mysqli performance patch
             */
            if($this->params['adapter'] === 'Mysqli') {
                /**
                 * @var \mysqli_stmt $resource
                 */
                $resource = $result->getResource();
                /**
                 * @var \mysqli_result $result
                 */
                $result = $resource->get_result();
                if($result){
                    $result = $result->fetch_all(MYSQLI_ASSOC);
                    if(!empty($result)){
                        return $result;
                    }
                }
                return [];
            }else{
                $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
                return $resultSet->initialize($result)->toArray();
            }
        }
        return [];
    }

    public function fetchCol()
    {
        throw new \Exception('not implemented');
    }

    public function fetchOne($sql)
    {
        $statement = $this->adapter->createStatement();
        $statement->prepare($sql);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
            $resultSet->initialize($result);
            $result = $resultSet->current();
            if(!empty($result)){
                return  array_values($result)[0];
            }
        }
        return null;
    }

    public function query($sql)
    {
        $this->adapter->query($sql, Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Fetch row from result set
     * @param $sql
     * @return array
     */
    public function fetchRow($sql) : ?array
    {
        $statement = $this->adapter->createStatement();
        $statement->prepare($sql);

        $result = $statement->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
            $resultSet->initialize($result);
            return $resultSet->current();
        }
        return [];
    }


    public function quoteIdentifier(string $string) : string
    {
        return $this->adapter->getPlatform()->quoteIdentifier($string);
    }

    public function quote($value)
    {
        return $this->adapter->getPlatform()->quoteValue($value);
    }

    /**
     * @return array
     */
    public function getConfig() : array
    {
        return $this->params;
    }

    /**
     * Get list of DB tables names
     * @return string[]
     */
    public function listTables()
    {
        $metadata = new Db\Metadata\Metadata($this->adapter);
        return $metadata->getTableNames();
    }

    /**
     * @param string $tableName
     * @return Db\Metadata\Object\TableObject
     */
    public function describeTable(string $tableName)
    {
        $metadata = new Db\Metadata\Metadata($this->adapter);
        return $metadata->getTable($tableName);
    }

    public function beginTransaction()
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
    }

    public function rollback()
    {
        $this->adapter->getDriver()->getConnection()->rollback();
    }

    public function commit()
    {
        $this->adapter->getDriver()->getConnection()->commit();
    }

    /**
     * Fix for mysqli driver
     * convert bollean into integer
     * @param array $values
     * @return array
     */
    protected function convertBooleanValues(array $values) : array
    {
        foreach ($values as &$value){
            if(is_bool($value)){
                $value = intval($value);
            }
        }
        return $values;
    }

    public function insert($table , $values)
    {
        if(!empty($values) && $this->params['adapter'] === 'Mysqli'){
            $values = $this->convertBooleanValues($values);
        }

        $sql = $this->sql();
        $insert = $sql->insert($table);
        $insert->values($values);

        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    }

    public function delete($table, $where = null)
    {
        $sql = $this->sql();
        $delete = $sql->delete($table);

        if(!empty($where)){
            $delete->where($where);
        }

        $statement = $sql->prepareStatementForSqlObject($delete);
        $statement->execute();
    }

    public function update($table, $values, $where = null )
    {
        if(!empty($values) && $this->params['adapter'] === 'Mysqli'){
            $values = $this->convertBooleanValues($values);
        }

        $sql = $this->sql();
        $update = $sql->update($table);
        $update->set($values);

        if(!empty($where)){
            $update->where($where);
        }

        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }

    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        return $this->adapter->getDriver()->getLastGeneratedValue();
    }

    /**
     * @param array $values
     * @return string
     */
    public function quoteValueList(array $values) : string
    {
        return $this->adapter->getPlatform()->quoteValueList($values);
    }
}