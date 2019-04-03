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

namespace Dvelum\Config;

use Dvelum\Cache\CacheInterface;
use Dvelum\Config;


/**
 * Configuration Object Factory
 * @author Kirill Yegorov 2010
 * @package Config
 */
class Factory
{
    const Simple = 0;
    const File_Array = 1;

    /**
     * @var \Store_Interface|bool
     */
    protected static $store = false;
    /**
     * @var CacheInterface|bool
     */
    protected static $cache = false;

    protected static $storageAdapter = '\\Dvelum\\Config\\Storage\\File\\AsArray';

    static public function setStorageAdapter(string $class) : void
    {
        if(!class_exists($class)){
            throw new \Exception('Undefined storage adapter '.$class);
        }
        self::$storageAdapter = $class;
    }
    /**
     * Set cache adapter
     * @param CacheInterface $core
     */
    public static function setCacheCore($core)
    {
        self::$cache = $core;
    }

    /**
     * Get cache adapter
     * @return CacheInterface | false
     */
    public static function getCacheCore()
    {
        return self::$cache;
    }

    /**
     * Factory method
     * @param integer $type -type of the object being created, Config class constant
     * @param string $name - identifier
     * @param boolean $useCache - optional , default true. Use cache if available
     * @return ConfigInterface
     */
    static public function config(int $type , string $name , bool $useCache = true) : ConfigInterface
    {
        $store = self::$store;
        $cache = self::$cache;

        if(!$store)
            $store = self::connectLocalStore();

        $config = false;
        $configKey = $type . '_' . $name;

        /*
         * Check if config is already loaded
         */
        if($useCache && $store->keyExists($configKey))
            return $store->get($configKey);

        /*
         * If individual keys
         */
        if($useCache && $cache && $config = $cache->load($configKey))
        {
            $store->set($configKey , $config);
            return $config;
        }

        switch($type)
        {
            case self::File_Array :
                $config =  static::storage()->get($name,$useCache);
                break;
            case self::Simple :
                $config = new Config\Adapter($name);
                break;
        }

        if($useCache)
            $store->set($configKey , $config);

        if($useCache && $cache)
            $cache->save($config , $configKey);
        else
            self::cache();

        return $config;
    }

    /**
     * Clear cache
     * @return void
     */
    static public function resetCache() : void
    {
        if(is_null(self::$store))
            self::connectLocalStore();

        if(empty(self::$store))
            return;

        foreach(self::$store as $k => $v)
        {
            if(self::$cache)
                self::$cache->remove($k);

            self::$store->remove($k);
        }
    }

    /**
     * Instantiate storage
     * @return \Store_Interface
     */
    static protected function connectLocalStore()
    {
        self::$store = \Store::factory(\Store::Local , 'class_' . __CLASS__);
        return self::$store;
    }

    /**
     * Cache data again
     * @property mixed $key - optional
     * @return void
     */
    static public function cache($key = false)
    {
        if(!self::$cache)
            return;

        if($key === false)
        {
            foreach(self::$store as $k => $v)
            {
                self::$cache->save($v , $k);
            }
        }
        else
        {
            if(self::$store->keyExists($key))
            {
                self::$cache->save(self::$store->get($key), (string) $key);
            }
        }
    }

    /**
     * Get configuration storage
     * @param bool $force  - Reset runtime cache reload object, optional default false
     * @return Storage\StorageInterface
     */
    static public function storage($force = false) : Config\Storage\StorageInterface
    {
        static $store = false;

        if($force){
            $store = false;
        }

        if(!$store){
            /**
             * @var Config\Storage\StorageInterface $store;
             */
            $store = new self::$storageAdapter();
            if(!empty($config)){
                $store->setConfig($config->__toArray());
            }
        }
        return $store;
    }

    /**
     * Create new config object
     * @param array $data
     * @param string|null $name
     * @return ConfigInterface
     */
    static public function create(array $data, ?string $name = null) : ConfigInterface
    {
        $config = new Config\Adapter($name);
        $config->setData($data);
        return $config;
    }
}