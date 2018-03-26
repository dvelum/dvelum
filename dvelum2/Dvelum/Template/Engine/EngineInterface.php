<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Template\Engine;

use Dvelum\Cache\CacheInterface;
use Dvelum\Config\ConfigInterface;

interface EngineInterface
{
    /**
     * Set template engine configuration options
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config);

    /**
     * Set caching adapter
     * @param CacheInterface|null $cache
     */
    public function setCache(?CacheInterface $cache): void;

    /**
     * Set multiple properties
     * @deprecated
     * @param array $data
     * @return void
     */
    public function setProperties(array $data) : void;

    /**
     * Get property
     * @param string $name
     * @return mixed
     */
    public function get(string $name);
    public function __get($name);
    /**
     * Set property
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name , $value) : void;
    public function __set($name , $value);

    public function __isset($name);
    public function __unset($name);

    /**
     * Empty template data
     * @return void
     */
    public function clear() : void;

    /**
     * Disable caching
     * @return void
     */
    public function disableCache() : void;

    /**
     * Enable caching
     * @return void
     */
    public function enableCache() : void;

    /**
     * Get template data
     * @return array
     */
    public function getData() : array;

    /**
     * Redefine template data using an associative key-value array,
     * old and new data merge
     * @param array $data
     * @return void
     */
    public function setData(array $data) : void;

    /**
     * Render current template
     * @param string $templatePath — the path to the template file
     * @return string
     */
    public function render(string $templatePath) : string;
    /**
     * Render sub template
     * @param string $templatePath
     * @param array $data
     * @param bool|true $useCache
     * @return string
     */
    public function renderTemplate(string $templatePath, array $data = [], bool $useCache = true) : string;
}