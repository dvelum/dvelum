<?php

/*
* DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
* Copyright (C) 2011-2013  Kirill A Egorov
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Designer_Storage
{
    /**
     * @property \Dvelum\Cache\CacheInterface|bool
     */
    protected static $_cache = false;
    protected static $_instances = [];

    /**
     * Storage adapter
     * @property Designer_Storage_Adapter_Abstract
     */
    protected $_adapter = null;
    /**
     * Adapter Class name
     * @property string
     */
    protected $_adapterClass = null;

    /**
     * Set chache core
     * @param Cache_Interface $manager
     */
    static public function setCache(Cache_Interface $manager)
    {
        self::$_cache = $manager;
    }

    /**
     * @param string $adapter - Adapter name
     * @param  Config_Abstract - optional
     * @return Designer_Storage_Adapter_Abstract
     */
    static public function getInstance($adapter, $config = false)
    {
        if (!isset(self::$_instances[$adapter]))
            self::$_instances[$adapter] = new self($adapter, $config);

        return self::$_instances[$adapter];
    }

    /**
     * @param string $adapter
     */
    protected function __construct($adapter, $config)
    {
        $className = 'Designer_Storage_Adapter_' . ucfirst($adapter);

        if (!class_exists($className))
            trigger_error('Invalid Adapter');

        $this->_adapter = new $className($config);
        $this->_adapterClass = $className;

        if (!$this->_adapter instanceof Designer_Storage_Adapter_Abstract)
            trigger_error('Invalid Adapter');
    }

    protected function __clone()
    {
    }

    /**
     * Get Adabpter object
     * @return Designer_Storage_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Calculate cache index
     * @param string $id
     * @return string
     */
    public function cacheIndex($id)
    {
        return md5('db_query_' . $this->_adapterClass . '_' . $id);
    }

    /**
     * Load Designer_Project
     * @param string $id
     * @return Designer_Project|bool
     */
    public function load($id)
    {
        $cacheIndex = $this->cacheIndex($id);

        if (self::$_cache) {
            $project = self::$_cache->load($cacheIndex);
            if ($project && $project instanceof Designer_Project) {
                return $project;
            }
        }

        $project = $this->_adapter->load($id);

        if (!$project instanceof Designer_Project)
            return false;

        if (self::$_cache)
            self::$_cache->save($project, $cacheIndex);

        return $project;
    }

    /**
     * Import project from contents
     * @param $id
     * @return mixed
     */
    public function import($id)
    {
        return $this->_adapter->import($id);
    }

    /**
     * Save Designer_Project
     * @param string $id
     * @param Designer_Project $obj
     * @param boolean $export
     * @return boolean
     */
    public function save($id, Designer_Project $obj, $export = false)
    {
        if (!$this->_adapter->save($id, $obj, $export))
            return false;

        if (self::$_cache) {
            self::$_cache->save($obj, $this->cacheIndex($id));
        }
        return true;
    }

    /**
     * Remove Db_Query
     * @param string $id
     */
    public function delete($id)
    {
        if (self::$_cache)
            self::$_cache->remove($this->cacheIndex($id));
        return $this->_adapter->delete($id);
    }

    /**
     * Get error list
     */
    public function getErrors()
    {
        return $this->_adapter->getErrors();
    }
}