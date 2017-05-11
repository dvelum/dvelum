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

namespace Dvelum\Config\Storage;

use Dvelum\Config\ConfigInterface;

interface StorageInterface
{
    public function get(string $localPath , bool $useCache = true , bool $merge = true) : ConfigInterface;

    /**
     * Create new config file
     * @param string $id
     * @return boolean
     */
    public function create(string $id) : bool;

    /**
     * Find path for config file (no merge)
     * @param $localPath
     * @return mixed
     */
    public function getPath($localPath);

    /**
     * Get list of available configs
     * @param bool $path - optional, default false
     * @param bool $recursive - optional, default false
     * @return array
     */
    public function getList($path = false, $recursive = false) : array;

    /**
     * Check if config file exists
     * @param $localPath
     * @return bool
     */
    public function exists(string $localPath) : bool;

    /**
     * Get storage paths
     * @return array
     */
    public function getPaths() : array;

    /**
     * Add config path
     * @param string $path
     * @return void
     */
    public function addPath(string $path) : void;

    /**
     * Prepend config path
     * @param $path
     * @return void
     */
    public function prependPath(string $path) : void;

    /**
     * Get write path
     * @return string
     */
    public function getWrite() : string;

    /**
     * Get src file path (to apply)
     * @return string
     */
    public function getApplyTo() : string;

    /**
     * Get debug information. (loaded configs)
     * @return array
     */
    public function getDebugInfo() : array;

    /**
     * Set configuration options
     * @param array $options
     * @return void
     */
    public function setConfig(array $options) : void;

    /**
     * Save configuration data
     * @param ConfigInterface $config
     * @return bool
     */
    public function save(ConfigInterface $config) : bool;
}