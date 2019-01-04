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

namespace Dvelum\Config\Storage\File;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Config\Storage\StorageInterface;

class AsArray implements StorageInterface
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
     * Debugger log
     * @var array
     */
    protected $debugInfo = [];

    /**
     * Get config by local path
     * @param string $localPath
     * @param boolean $useCache , optional
     * @param boolean $merge , optional merge with main config
     * @throws \Exception
     * @return ConfigInterface
     */
    public function get(string $localPath, bool $useCache = true, bool $merge = true): ConfigInterface
    {
        // storage config prohibits merging
        if ($this->config['file_array']['apply_to'] === false) {
            $merge = false;
        }

        $key = $localPath . intval($merge);

        if (isset(static::$runtimeCache[$key]) && $useCache) {
            return static::$runtimeCache[$key];
        }

        $data = false;

        $list = $this->config['file_array']['paths'];

        if (!$merge) {
            $list = array_reverse($list);
        }

        foreach ($list as $path) {
            if (!file_exists($path . $localPath)) {
                continue;
            }

            $cfg = $path . $localPath;

            if ($this->config['debug']) {
                $this->debugInfo[] = $cfg;
            }

            if (!$merge) {
                $data = include $cfg;
                break;
            }

            if ($data === false) {
                $data = include $cfg;
            } else {
                $cfgData = include $cfg;
                if ($data === false) {
                    $data = [];
                }
                $data = array_merge($data, $cfgData);
            }
        }

        if ($data === false) {
            throw new \Exception('Getting undefined config [' . $localPath . ']');
        }

        $object = new Config\File\AsArray($this->config['file_array']['write'] . $localPath, false);

        if ($this->config['file_array']['apply_to'] !== false && $merge) {
            $object->setParentId($this->config['file_array']['apply_to'] . $localPath);
        }

        // fast data injection
        $link = &$object->dataLink();
        $link = $data;

        if ($useCache) {
            static::$runtimeCache[$key] = $object;
        }

        return $object;
    }

    /**
     * Create new config file
     * @param string $id
     * @throws \Exception
     * @return bool
     */
    public function create(string $id): bool
    {
        $file = $this->getWrite() . $id;
        $dir = dirname($file);

        if (!file_exists($dir) && !@mkdir($dir, 0775, true)) {
            throw new \Exception('Cannot create ' . $dir);
        }

        if (\Dvelum\File::getExt($file) !== '.php') {
            throw new \Exception('Invalid file name');
        }

        if (\Dvelum\Utils::exportArray($file, []) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Find path for config file (no merge)
     * @param $localPath
     * @return mixed
     */
    public function getPath($localPath)
    {
        $list = array_reverse($this->config['file_array']['paths']);

        foreach ($list as $path) {
            if (!file_exists($path . $localPath)) {
                continue;
            }

            return $path . $localPath;
        }
        return false;
    }

    /**
     * Get list of available configs
     * @param bool $path - optional, default false
     * @param bool $recursive - optional, default false
     * @return array
     */
    public function getList($path = false, $recursive = false): array
    {
        $files = [];
        foreach ($this->config['file_array']['paths'] as $item) {
            if ($path) {
                $item .= $path;
            }

            if (!is_dir($item)) {
                continue;
            }

            $list = \Dvelum\File::scanFiles($item, ['.php'], $recursive, \Dvelum\File::Files_Only);

            if (!empty($list)) {
                $files = array_merge($files, $list);
            }

        }
        return $files;
    }

    /**
     * Check if config file exists
     * @param $localPath
     * @return bool
     */
    public function exists(string $localPath): bool
    {
        foreach ($this->config['file_array']['paths'] as $path) {
            if (file_exists($path . $localPath)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get storage paths
     * @return array
     */
    public function getPaths(): array
    {
        return $this->config['file_array']['paths'];
    }

    /**
     * Add config path
     * @param string $path
     * @return void
     */
    public function addPath(string $path): void
    {
        $this->config['file_array']['paths'][] = $path;
    }

    /**
     * Prepend config path
     * @param $path
     * @return void
     */
    public function prependPath(string $path): void
    {
        \array_unshift($this->config['file_array']['paths'], $path);
    }

    /**
     * Get write path
     * @return string
     */
    public function getWrite(): string
    {
        return $this->config['file_array']['write'];
    }

    /**
     * Get src file path (to apply)
     * @return string
     */
    public function getApplyTo(): string
    {
        return $this->config['file_array']['apply_to'];
    }

    /**
     * Get debug information. (loaded configs)
     * @return array
     */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }

    /**
     * Set configuration options
     * @param array $options
     * @return void
     */
    public function setConfig(array $options): void
    {
        foreach ($options as $k => $v) {
            $this->config[$k] = $v;
        }
    }

    /**
     * Save configuration data
     * @param ConfigInterface $config
     * @return bool
     */
    public function save(ConfigInterface $config): bool
    {
        $parentId = $config->getParentId();
        $id = $config->getName();

        $configData = $config->__toArray();

        if (!empty($parentId) && \file_exists($parentId)) {
            $src = include $parentId;
            $data = [];
            foreach ($configData as $k => $v) {
                if (!isset($src[$k]) || $src[$k] != $v) {
                    $data[$k] = $v;
                }
            }
        } else {
            $data = $configData;
        }

        if (\file_exists($id)) {
            if (!\is_writable($id)) {
                return false;
            }
        } else {
            $dir = dirname($id);

            if (!\file_exists($dir)) {
                if (!@mkdir($dir, 0775, true)) {
                    return false;
                }
            } elseif (!\is_writable($dir)) {
                return false;
            }
        }

        if (\Utils::exportArray($id, $data) !== false) {
            Config\Factory::cache();
            return true;
        }
        return false;
    }

    /**
     * Reset cached configs
     */
    public function resetConfigCache(): void
    {
        static::$runtimeCache = [];
    }

    /**
     * Replace paths data
     * @param array $paths
     */
    public function replacePaths(array $paths): void
    {
        $this->config['file_array']['paths'] = $paths;
        $this->resetConfigCache();
    }
}