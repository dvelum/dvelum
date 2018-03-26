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

namespace Dvelum\Template\Adapter;
use Dvelum\Cache\CacheInterface;

/**
 * View class
 * @author Kirill A Egorov 2011
 */
class ActiveTemplate
{
    /**
     * Template data (local variables)
     * @var array
     */
    private $data = [];

    /**
     * Default cache interface
     * @property \Cache_Interface | bool $defaultCache
     */
    protected static $defaultCache = false;
    /**
     * Check modification time for template file. Invalidate cache
     * @property bool $checkMTime
     */
    protected static $checkMTime = true;
    /**
     * @property \Cache_Interface $cache
     */
    protected $cache = false;
    /**
     * @property boolean $useCache
     */
    protected $useCache = true;

    /**
     * @property  \Dvelum\Template\Storage $storage
     */
    protected $storage;

    /**
     * Set the template cache manager (system method)
     * @param CacheInterface $manager
     */
    static public function setCache(CacheInterface $manager)
    {
        self::$defaultCache = $manager;
    }

    /**
     * Set _checkMTime flag
     * @param boolean $flag
     */
    static public function checkMtime(bool $flag)
    {
        self::$checkMTime = $flag;
    }

    public function __construct(?array $data = null)
    {
        $this->cache = self::$defaultCache;
        $this->storage = self::storage();
        if(isset($data) && is_array($data)){
            $this->data = $data;
        }
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
            if(self::$checkMTime){
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
     * @return void
     */
    public function set(string $name , $value) : void
    {
        $this->data[$name] = $value;
    }

    /**
     * Set multiple properties
     * @param array $data
     * @return void
     */
    public function setProperties(array $data) : void
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
     * @return void
     */
    public function clear() : void
    {
        $this->data = [];
    }

    /**
     * Disable caching
     * @return void
     */
    public function disableCache() : void
    {
        $this->useCache = false;
    }

    /**
     * Enable caching
     * @return void
     */
    public function enableCache() : void
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
     * @return void
     */
    public function setData(array $data) : void
    {
        $this->data = $data;
    }

    /**
     * Get Templates storage
     * @return \Dvelum\Template\Storage
     */
    static public function storage() : \Dvelum\Template\Storage
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