<?php

/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
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
 *
 */
declare(strict_types=1);

namespace Dvelum\App;

use Dvelum\Config\ConfigInterface;
use Dvelum\Config;
use Dvelum\Externals\Manager;
use Dvelum\File;
use Dvelum\Utils;

/**
 * Classmap builder
 */
class PlatformClassMap
{
    protected $map = [];
    /**
     * @var ConfigInterface $appConfig
     */
    protected $appConfig;
    /**
     * @var array
     */
    protected $autoloaderCfg = [];

    protected Config\Storage\StorageInterface $configStorage;
    protected Manager $externalsManager;

    public function __construct(
        ConfigInterface $appConfig,
        Config\Storage\StorageInterface $configStorage,
        Manager $externalsManager
    ) {
        $this->appConfig = $appConfig;
        $this->configStorage = $configStorage;
        $this->externalsManager = $externalsManager;
        $this->autoloaderCfg = $configStorage->get('autoloader.php')->__toArray();
    }

    public function load()
    {
        $map = $this->configStorage->get($this->autoloaderCfg->get('map'));
        if (!empty($map)) {
            $this->map = $map->__toArray();
        }
    }

    public function update()
    {
        $this->map = [];
        $autoloader = $this->externalsManager->getAutoloader();
        $paths = $autoloader->getRegisteredPaths();

        foreach ($paths as $v) {
            $v = File::fillEndSep($v);
            if (is_dir($v)) {
                $this->findClasses($v, $v);
            }
        }

        $psr4 = $this->autoloaderCfg['psr-4'];
        foreach ($psr4 as $baseSpace => $path) {
            $v = File::fillEndSep($path);
            if (is_dir($v)) {
                $this->findPsr4Classes($v, $v, $baseSpace);
            }
        }
        ksort($this->map);
    }

    /**
     * Find PHP Classes
     * @param $path
     * @param $exceptPath
     * @throws \Exception
     */
    protected function findClasses(string $path, string $exceptPath)
    {
        $path = File::fillEndSep($path);
        $items = File::scanFiles($path, ['.php'], false);

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (File::getExt($item) === '.php') {
                if (!empty($this->autoloaderCfg['noMap'])) {
                    $found = false;
                    foreach ($this->autoloaderCfg['noMap'] as $excludePath) {
                        if (strpos($item, $excludePath) !== false) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        continue;
                    }
                }

                $parts = explode('/', str_replace($exceptPath, '', substr($item, 0, -4)));
                $parts = array_map('ucfirst', $parts);
                $class = implode('_', $parts);

                if (!isset($this->map[$class])) {
                    try {
                        if (!isset($this->map[$class]) && (class_exists($class) || interface_exists($class))) {
                            $this->map[$class] = $item;
                        } else {
                            $class = str_replace('_', '\\', $class);
                            if (!isset($this->map[$class]) && (class_exists($class) || interface_exists($class))) {
                                $this->map[$class] = $item;
                            }
                        }
                    } catch (\Error $e) {
                        echo $e->getMessage() . "\n";
                    }
                }
            } else {
                $this->findClasses($item, $exceptPath);
            }
        }
    }

    /**
     * Find PHP Classes
     * @param string $path
     * @param string $exceptPath
     * @param string $baseSpace
     * @throws \Exception
     */
    protected function findPsr4Classes(string $path, string $exceptPath, string $baseSpace)
    {
        $path = File::fillEndSep($path);

        $items = File::scanFiles($path, ['.php'], false);

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (File::getExt($item) === '.php') {
                $parts = explode('/', str_replace($exceptPath, '', substr($item, 0, -4)));
                $parts = array_map('ucfirst', $parts);
                $class = $baseSpace . '\\' . implode('\\', $parts);

                if (!isset($this->map[$class])) {
                    $this->map[$class] = $item;
                }
            } else {
                $this->findPsr4Classes($item, $exceptPath, $baseSpace);
            }
        }
    }

    /**
     * save class map
     * @return boolean
     */
    public function save()
    {
        $writePath = $this->configStorage->getWrite() . $this->autoloaderCfg['map'];
        return Utils::exportArray($writePath, $this->map);
    }
}