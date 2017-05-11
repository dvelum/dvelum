<?php
use Dvelum\Config;
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
     * @return Config\ConfigInterface
     */
    public function getDbConfig(string $name) : Config\ConfigInterface;
}