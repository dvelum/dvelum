<?php

use Zend\Db;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

/**
 * Class Db_Adapter
 * Db adapter proxy
 */
class Db_Adapter
{
    protected $params;
    protected $adapter;

    public function __construct($params)
    {
        $this->params = $params;
        $this->adapter = new Zend\Db\Adapter\Adapter($params);
    }

    /**
     * @return Db_Select
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
        throw new Exception('not implemented');
    }

    public function fetchOne()
    {
        throw new Exception('not implemented');
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
        throw new Exception('not implemented');
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
}