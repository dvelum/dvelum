<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2016  Kirill A Egorov
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

use Dvelum\Config\ConfigInterface;

class Lang
{
    protected $dictionary = false;
    protected $dictionaryName = '';

    static protected $dictionaries = [];
    static protected $loaders = [];
    static protected $defaultDictionary = '';


    /**
     * @param string $name
     */
    protected function __construct(string $name)
    {
        $this->dictionaryName = $name;
        if(isset(self::$dictionaries[$name]))
            $this->dictionary = self::$dictionaries[$name];
    }

    /**
     * Get current dictionary name
     * @return string
     */
    public function getName() : string
    {
        return $this->dictionaryName;
    }

    /**
     * Set default localization
     * @param string $name
     * @throws \Exception
     */
    static public function setDefaultDictionary(string $name)
    {
        if(!isset(self::$dictionaries[$name]) && !isset(self::$loaders[$name]))
            throw new \Exception('Dictionary '.$name.' is not found');

        self::$defaultDictionary = $name;
    }

    /**
     * Get default dictionary (lang)
     * @return string
     */
    static public function getDefaultDictionary() :string
    {
        return self::$defaultDictionary;
    }

    /**
     * Add localization dictionary
     * @param string $name — localization name
     * @param ConfigInterface  $dictionary — configuration object
     * @return void
     */
    static public function addDictionary(string $name , Config\ConfigInterface $dictionary)
    {
        self::$dictionaries[$name] = $dictionary;
    }

    /**
     * Add localization loader
     * @param string $name - dictionary name
     * @param mixed $src - dictionary source
     * @param integer $type - Config constant
     */
    static public function addDictionaryLoader(string $name , $src , $type = Config\Factory::File_Array)
    {
        self::$loaders[$name] = array('src'=> $src , 'type' =>$type);
    }

    /**
     * Load dictionary data
     * @param string $name
     */
    protected function loadDictionary(string $name) : void
    {
        if($this->dictionary)
            return;

        if(isset(self::$dictionaries[$name]))
        {
            $this->dictionary = self::$dictionaries[$name];
            return;
        }

        if(isset(self::$loaders[$name]))
        {
            switch(self::$loaders[$name]['type']){
                case Config\Factory::File_Array:
                    self::$dictionaries[$name] = static::storage()->get(self::$loaders[$name]['src'] , true , true);
                    $this->dictionary = self::$dictionaries[$name];
                    break;
            }
        }
    }

    /**
     * Get a localized string by dictionary key.
     * If the necessary key is absent, the following value will be returned: «[key]»
     * @param string $key
     * @return string
     */
    public function get(string $key) : string
    {
        $this->loadDictionary($this->dictionaryName);

        if($this->dictionary->offsetExists($key))
            return $this->dictionary->get($key);
        else
            return '[' . $key . ']';
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        $this->loadDictionary($this->dictionaryName);

        return $this->dictionary->offsetExists($key);
    }

    /**
     * Convert the localization dictionary to JSON
     * @return string
     */
    public function getJson() : string
    {
        $this->loadDictionary($this->dictionaryName);
        return \json_encode($this->dictionary->__toArray());
    }

    /**
     * Convert the localization dictionary to JavaScript object
     * @return string
     */
    public function getJsObject() : string
    {
        $this->loadDictionary($this->dictionaryName);
        $items = array();
        foreach($this->dictionary as $k => $v)
            $items[] = $k . ':"' . $v . '"';

        return \str_replace("\n","",'{' . implode(',' , $items) . '}');
    }

    /**
     * Get link to localization dictionary by localization name or
     * get default dictionary
     * @param string $name optional,
     * @throws \Exception
     * @return Lang
     */
    static public function lang(string $name = '') : self
    {
        if(empty($name))
            $name = self::$defaultDictionary;

        if(!isset(self::$dictionaries[$name]) && !isset(self::$loaders[$name]))
            throw new \Exception('Lang::lang Dictionary "'.$name.'" is not found');

        return new self($name);
    }

    /**
     * Get configuration storage
     * @return Config\Storage\StorageInterface
     */
    static public function storage() : Config\Storage\StorageInterface
    {
        static $store = false;

        if(!$store){
            $store = new Config\Storage\File\AsArray();
        }

        return $store;
    }
}