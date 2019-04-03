<?php
/*
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
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

namespace Dvelum\Template\Engine;

use Dvelum\Cache\CacheInterface;
use Dvelum\Config\ConfigInterface;
use \Exception;
use Dvelum\View;

/**
 * View class
 * @author Kirill A Egorov 2011
 */
class ActiveTemplate implements EngineInterface
{
    /**
     * Template data (local variables)
     * @var array
     */
    private $data = [];

    /**
     * @property CacheInterface|null $cache
     */
    protected $cache = null;
    /**
     * @property boolean $useCache
     */
    protected $useCache = true;

    /**
     * @var int|bool $cacheLifetime
     */
    protected $cacheLifetime = false;

    /**
     * @property ConfigInterface $config
     */
    protected $config;

    /**
     * Set template configuration
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Set caching adapter
     * @param CacheInterface|null $cache
     */
    public function setCache(?CacheInterface $cache): void
    {
        $this->cache = $cache;
    }
    /**
     * Template Render
     * @param string $templatePath — the path to the template file
     * @return string
     */
    public function render(string $templatePath): string
    {
        $hash = '';
        $realPath = View::storage()->get($templatePath);

        if(!$realPath){
            return '';
        }

        if($this->cache && $this->useCache)
        {
            $hash = md5('tpl_' . $templatePath . '_' . serialize($this->data).filemtime($realPath));
            $html = $this->cache->load($hash);

            if($html !== false)
                return $html;
        }

        \ob_start();
        include $realPath;
        $result = \ob_get_clean();

        if($this->cache && $this->useCache){
            $this->cache->save($result , $hash, $this->cacheLifetime);
        }
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
     * Render sub template
     * @param string $templatePath
     * @param array $data
     * @param bool|true $useCache
     * @throws Exception
     * @return string
     */
    public function renderTemplate(string $templatePath, array $data = [], bool $useCache = true) : string
    {
        $tpl = View::factory();
        $tpl->setData($data);

        if(!$useCache)
            $tpl->disableCache();

        return $tpl->render($templatePath);
    }

    /**
     * Set lifetime for cache data
     * @param int $sec
     */
    public function setCacheLifetime(int $sec) : void
    {
        $this->cacheLifetime = $sec;
    }
}