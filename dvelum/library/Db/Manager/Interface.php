<?php
interface Db_Manager_Interface
{
    /**
     * Get DB connection
     * @param string $name
     * @throws Exception
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbConnection($name);
    /**
     * Get DB connection config
     * @param string $name
     * @throws Exception
     * @return Config_Abstract
     */
    public function getDbConfig($name);
}