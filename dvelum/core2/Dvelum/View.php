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
declare(strict_types=1);

namespace Dvelum;

use Dvelum\Config\Storage;

/**
 * View class
 * @author Kirill A Egorov 2011
 */
class View
{
    /**
     * Template data (local variables)
     * @var array
     */
    private $data = [];

    /**
     * Default cache interface
     * @var \Cache_Interface
     */
    protected static $_defaultCache = false;
    /**
     * Check modification time for template file. Invalidate cache
     * @var boolean
     */
    protected static $_checkMTime = false;
    /**
     * @var \Cache_Interface
     */
    protected $cache = false;
    /**
     * @var boolean
     */
    protected $_useCache = true;

    /**
     * @var \Dvelum\Template\Storage
     */
    protected $storage;

    /**
     * Set the template cache manager (system method)
     * @param \Cache_Interface $manager
     */
    static public function setCache(\Cache_Interface $manager)
    {
        self::$_defaultCache = $manager;
    }

    /**
     * Set _checkMTime flag
     * @param boolean $flag
     */
    static public function checkMtime(bool $flag)
    {
        self::$_checkMTime = $flag;
    }

    public function __construct()
    {
        $this->cache = self::$_defaultCache;
        $this->storage = self::storage();
    }
    /**
     * Template Render
     * @param string $path — the path to the template file
     * @return string
     */
    public function render($path)
    {
        $hash = '';

        $realPath = $this->storage->get($path);

        if(!$realPath){
            return '';
        }

        if($this->cache && $this->useCache)
        {
            if(self::$_checkMTime){
                $hash = md5('tpl_' . $path . '_' . serialize($this->data).filemtime($realPath));
            }else{
                $hash = md5('tpl_' . $path . '_' . serialize($this->data));
            }

            $html = $this->cache->load($hash);

            if($html !== false)
                return $html;
        }

        \ob_start();
        include $realPath;
        $result = \ob_get_clean();

        if($this->cache && $this->useCache)
            $this->cache->save($result , $hash);

        return $result;
    }

    /**
     * Set property
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name , $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Set multiple properties
     * @param array $data
     */
    public function setProperties(array $data)
    {
        foreach ($data as $name=>$value)
            $this->data[$name] = $value;
    }

    /**
     * Get property
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        if(!isset($this->data[$name]))
            return null;

        return $this->data[$name];
    }

    public function __set($name , $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Empty template data
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * Disable caching
     */
    public function disableCache()
    {
        $this->useCache = false;
    }

    /**
     * Enable caching
     */
    public function enableCache()
    {
        $this->useCache = true;
    }

    /**
     * Get template data
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Redefine template data using an associative key-value array,
     * old and new data merge
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get Templates storage
     * @return \Dvelum\Template\Storage
     */
    static public function storage()
    {
        static $store = false;

        if(!$store){
            $store = new \Dvelum\Template\Storage();
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
    public function renderTemplate(string $templatePath, array $data = [], $useCache = true) : string
    {
        $tpl = new self();
        $tpl->setData($data);

        if(!$useCache)
            $tpl->disableCache();

        return $tpl->render($templatePath);
    }
}