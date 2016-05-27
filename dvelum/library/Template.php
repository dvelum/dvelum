<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
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

/**
 * Template class
 * @author Kirill A Egorov 2011
 */
class Template
{
    /**
     * Template data (local variables)
     * @var array
     */
    private $_data = array();

    /**
     * Default cache interface
     * @var Cache_Interface
     */
    protected static $_defaultCache = false;
    /**
     * Check modification time for template file. Invalidate cache
     * @var boolean
     */
    protected static $_checkMTime = false;
    /**
     * @var Cache_Interface
     */
    protected $_cache = false;
    /**
     * @var boolean
     */
    protected $_useCache = true;

    /**
     * @var Template_Storage
     */
    protected $_storage;

    /**
     * Set the template cache manager (system method)
     * @param Cache_Interface $manager
     */
    static public function setCache(Cache_Interface $manager)
    {
        self::$_defaultCache = $manager;
    }

    /**
     * Set _checkMTime flag
     * @param boolean $flag
     */
    static public function checkMtime($flag)
    {
        self::$_checkMTime = $flag;
    }

    public function __construct()
    {
        $this->_cache = self::$_defaultCache;
        $this->_storage = self::storage();

    }
    /**
     * Template Render
     * @param string $path — the path to the template file
     * @return string
     */
    public function render($path)
    {
        $hash = '';
        if($this->_cache && $this->_useCache)
        {
            $hash = md5('tpl_' . $path . '_' . serialize($this->_data));
            $html = $this->_cache->load($hash);

            if($html !== false)
                return $html;
        }

        $realPath = $this->_storage->get($path);

        if(!$realPath){
            return '';
        }

        ob_start();
        include $realPath;
        $result = ob_get_clean();

        if($this->_cache && $this->_useCache)
            $this->_cache->save($result , $hash);

        return $result;
    }

    /**
     * Set property
     * @param string $name
     * @param mixed $value
     */
    public function set($name , $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Set multiple properties
     * @param array $data
     */
    public function setProperties(array $data)
    {
        foreach ($data as $name=>$value)
            $this->_data[$name] = $value;
    }

    /**
     * Get property
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if(!isset($this->_data[$name]))
            return null;
        return $this->_data[$name];
    }

    public function __set($name , $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __unset($name)
    {
        unset($this->_data[$name]);
    }

    /**
     * Empty template data
     */
    public function clear()
    {
        $this->_data = array();
    }

    /**
     * Disable caching
     */
    public function disableCache()
    {
        $this->_useCache = false;
    }

    /**
     * Enable caching
     */
    public function enableCache()
    {
        $this->_useCache = true;
    }

    /**
     * Get template data
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Redefine template data using an associative key-value array,
     * old and new data merge
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Get Templates storage
     * @return Template_Storage
     */
    static public function storage()
    {
        static $store = false;

        if(!$store){
            $store = new Template_Storage();
        }

        return $store;
    }

    /**
     * Render sub template
     * @param string $templatePath
     * @param array $data
     * @param bool|true $useCache
     * @return string
     */
    public function renderTemplate($templatePath, array $data = [], $useCache = true)
    {
        $tpl = new self();
        $tpl->setData($data);

        if(!$useCache)
            $tpl->disableCache();

        return $tpl->render($templatePath);
    }
}