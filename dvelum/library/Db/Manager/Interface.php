<?php
use Dvelum\Config\ConfigInterface;
use Dvelum\Db;

interface Db_Manager_Interface
{
    /**
     * Get DB connection
     * @param string $name
     * @throws Exception
     * @return Db\Adapter
     */
    public function getDbConnection(string $name) : Db\Adapter;
    /**
     * Get DB connection config
     * @param string $name
     * @throws Exception
     * @return ConfigInterface
     */
    public function getDbConfig(string $name) : ConfigInterface;
}