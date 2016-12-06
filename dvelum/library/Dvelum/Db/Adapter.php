<?php
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
     * @return \Db_Select
     */
    public function select()
    {
        $select = new \Db_Select();
        if($this->params['adapter'] === 'mysqli'){
            $select->setMysqli($this->adapter->getDriver()->getConnection());
        }
        return $select;
    }

    /**
     * @return Sql
     */
    public function sql()
    {
        return new Sql($this->adapter);
    }

    /**
     * @return null|Db\Adapter\Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->adapter->getProfiler();
    }

    public function fetchAll($sql)
    {
        $statement = $this->adapter->createStatement();
        $statement->prepare($sql);

        $result = $statement->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
            return $resultSet->initialize($result)->toArray();
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

    public function fetchRow($sql)
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

    public function quote()
    {
        throw new \Exception('not implemented');
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

    public function insert($table , $values)
    {
        $sql = $this->sql();
        $insert = $sql->insert($table);
        $insert->values($values);

        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();

    }

}