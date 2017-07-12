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

namespace Dvelum\App\Dictionary;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\File;

class Manager
{
    const CACHE_KEYlist = 'Dictionary_Managerlist';
    const CACHE_KEY_DATA_HASH = 'Dictionary_Manager_dataHash';

    /**
     * Base path
     * @var string
     */
    protected $baseDir = '';

    /**
     * Path to localized dictionary
     * @var string
     */
    protected $path = '';
    /**
     * @var \Cache_Interface
     */
    protected $cache = false;
    /**
     * @var string
     */
    protected $language = '';

    static protected $list = null;

    /**
     * Valid dictionary local cache
     * @var array
     */
    static protected $validDictionary = [];

    /**
     * @param ConfigInterface $appConfig
     * @param mixed  \Cache_Interface | false $cache
     * @throws \Exception
     */
    protected function __construct(ConfigInterface $appConfig, $cache = false)
    {
        $this->appConfig = $appConfig;
        $this->language = $appConfig->get('language');
        $this->path = Config::storage()->getWrite();
        $this->baseDir = $appConfig->get('dictionary_folder');
        $this->cache = $cache;

        if ($this->cache && $list = $this->cache->load(self::CACHE_KEYlist)) {
            self::$list = $list;
        }
    }

    /**
     * Get list of dictionaries
     * return array
     */
    public function getList()
    {
        if (!is_null(self::$list)) {
            return array_keys(self::$list);
        }

        $paths = Config::storage()->getPaths();

        $list = [];

        foreach ($paths as $path) {
            if (!file_exists($path . $this->baseDir . 'index/')) {
                continue;
            }

            $files = File::scanFiles($path . $this->baseDir . 'index/', array('.php'), false, File::Files_Only);

            if (!empty($files)) {
                foreach ($files as $path) {
                    $name = substr(basename($path), 0, -4);
                    $list[$name] = $path;
                }
            }
        }

        self::$list = $list;

        if ($this->cache) {
            $this->cache->save($list, self::CACHE_KEYlist);
        }

        return array_keys($list);
    }

    /**
     * Create dictionary
     * @param string $name
     * @param string $language , optional
     * @return bool
     */
    public function create(string $name, $language = false): bool
    {
        if ($language == false) {
            $language = $this->language;
        }

        $indexFile = $this->path . $this->baseDir . 'index/' . $name . '.php';
        $dictionaryFile = $this->path . $this->baseDir . $language . '/' . $name . '.php';

        $configStorage = Config::storage();

        if (!file_exists($dictionaryFile)){
            $dictionaryConfig = Config\Factory::create([], $dictionaryFile);
            if ($configStorage->save($dictionaryConfig)) {
                if (!file_exists($indexFile)) {
                    $indexConfig = Config\Factory::create([], $indexFile);
                    if (!$configStorage->save($indexConfig)) {
                        return false;
                    }
                }
                self::$validDictionary[$name] = true;
                $this->resetCache();
                return true;
            }
        }
        return false;
    }

    /**
     * Rename dictionary
     * @param string $oldName
     * @param string $newName
     * @return boolean
     */
    public function rename($oldName, $newName)
    {
        $dirs = File::scanFiles($this->path . $this->baseDir, false, false, File::Dirs_Only);

        foreach ($dirs as $path) {
            if (file_exists($path . '/' . $oldName . '.php')) {
                if (!@rename($path . '/' . $oldName . '.php', $path . '/' . $newName . '.php')) {
                    return false;
                }
            }
        }

        if (isset(self::$validDictionary[$oldName])) {
            unset(self::$validDictionary[$oldName]);
        }

        self::$validDictionary[$newName] = true;

        $this->resetCache();

        return true;
    }

    /**
     * Check if dictionary exists
     * @param string $name
     * @return boolean
     */
    public function isValidDictionary($name)
    {
        /*
         * Check local cache
         */
        if (isset(self::$validDictionary[$name])) {
            return true;
        }

        if (Config::storage()->exists($this->baseDir . 'index/' . $name . '.php')) {
            self::$validDictionary[$name] = true;
            return true;
        }
        return false;
    }

    /**
     * Remove dictionary
     * @param string $name
     * @return boolean
     */
    public function remove($name)
    {
        $dirs = File::scanFiles($this->path . $this->baseDir, false, false, File::Dirs_Only);

        foreach ($dirs as $path) {
            $file = $path . '/' . $name . '.php';
            if (file_exists($file) && is_file($file)) {
                if (!@unlink($file)) {
                    return false;
                }
            }
        }

        if (isset(self::$validDictionary[$name])) {
            unset(self::$validDictionary[$name]);
        }

        $this->resetCache();

        return true;
    }

    /**
     * Reset cache
     */
    public function resetCache()
    {
        if (!$this->cache) {
            return;
        }

        $this->cache->remove(self::CACHE_KEYlist);
        $this->cache->remove(self::CACHE_KEY_DATA_HASH);
    }

    /**
     * Get data hash (all dictionaries data)
     */
    public function getDataHash()
    {
        if ($this->cache && $hash = $this->cache->load(self::CACHE_KEY_DATA_HASH)) {
            return $hash;
        }

        $s = '';
        $list = $this->getList();

        if (!empty($list)) {
            foreach ($list as $name) {
                $s .= $name . ':' . \Dictionary::factory($name)->__toJs();
            }
        }

        $s = md5($s);

        if ($this->cache) {
            $this->cache->save($s, self::CACHE_KEY_DATA_HASH);
        }

        return $s;
    }

    /**
     * Get Dictionary manager
     * @return Manager
     */
    static public function factory(): Manager
    {
        static $manager = false;

        if (!$manager) {
            $cfg = Config::storage()->get('main.php');
            $cacheManager = new \Cache_Manager();
            $cache = $cacheManager->get('data');
            $manager = new static($cfg, $cache);
        }

        return $manager;
    }


    /**
     * Save changes
     * @param string $name
     * @return bool
     */
    public function saveChanges(string $name): bool
    {
        $dict = \Dictionary::factory($name);
        if (!$dict->save()) {
            return false;
        }

        $this->resetCache();
        $this->rebuildIndex($name);
        $this->mergeLocales($name, $this->language);
        return true;
    }

    /**
     * Rebuild dictionary index
     * @param string $name
     * @return boolean
     */
    public function rebuildIndex($name)
    {
        $dict = \Dictionary::factory($name);
        $storage = Config::storage();

        $filePath = $this->baseDir . 'index/' . $name . '.php';
        $index = $storage->get($filePath, false, false);

        $index->removeAll();
        $index->setData(array_keys($dict->getData()));
        $storage->save($index);

        return true;
    }

    /**
     * Sync localized versions of dictionaries using base dictionary as a reference list of records
     * @param string $name
     * @param string $baseLocale
     * @return boolean
     */
    public function mergeLocales($name, $baseLocale)
    {
        $storage = Config::storage();

        $baseDict = $storage->get($this->baseDir . $baseLocale . '/' . $name . '.php', false, false);

        $locManager = new \Backend_Localization_Manager($this->appConfig);

        foreach ($locManager->getLangs(true) as $locale) {
            if ($locale == $baseLocale) {
                continue;
            }


            $localPath = $this->baseDir . $locale . '/' . $name . '.php';

            if (!$storage->exists($localPath)) {
                if (!$this->create($name, $locale) || !$dict = $storage->get($localPath, false, false)) {
                    return false;
                }
            }

            $dict = $storage->get($localPath, false, false);

            // Add new records from base dictionary and remove redundant records from current
            $mergedData = array_merge(
            // get elements from current dictionary with keys common for arrays
                array_intersect_key($dict->__toArray(), $baseDict->__toArray()),
                // get new records for current dictionary
                array_diff_key($baseDict->__toArray(), $dict->__toArray())
            );

            $dict->removeAll();
            $dict->setData($mergedData);
            $storage->save($dict);
        }

        return true;
    }
}