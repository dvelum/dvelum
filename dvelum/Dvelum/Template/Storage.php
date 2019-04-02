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
namespace Dvelum\Template;

class Storage
{
    /**
     * Runtime cache of configuration files
     * @var array
     */
    static protected $runtimeCache = [];

    /**
     * Storage configuration options
     * @var array
     */
    protected $config = [];

    /**
     * Set configuration options
     * @param array $options
     * @return void
     */
    public function setConfig(array $options) : void
    {
        foreach($options as $k=>$v){
            $this->config[$k] = $v;
        }
    }
    /**
     * Get template real path  by local path
     * @param string $localPath
     * @param boolean $useCache, optional
     * @return string | false
     */
    public function get($localPath , $useCache = true)
    {
        $key = $localPath;

        if(isset(static::$runtimeCache[$key]) && $useCache)
            return static::$runtimeCache[$key];

        $filePath = false;

        $list = $this->config['paths'];

        foreach($list as $path)
        {
            if(!\file_exists($path . $localPath))
                continue;

            $filePath = $path . $localPath;
            break;
        }

        if($filePath === false)
            return false;

        if($useCache)
            static::$runtimeCache[$key] = $filePath;

        return $filePath;
    }

    /**
     * Get template paths
     * @return array
     */
    public function getPaths() : array
    {
        return $this->config['paths'];
    }

    /**
     * Add templates path
     * @param string $path
     * @return void
     */
    public function addPath($path) : void
    {
        $this->config['paths'][] = $path;
    }

    /**
     * Set paths
     * @param array $paths
     */
    public function setPaths(array $paths):void
    {
        $this->config['paths'] = $paths;
    }
}