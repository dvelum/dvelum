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
class Lang
{
	protected $_dictionary = false;
	protected $_dictionaryName = '';
	
	static protected $_dictionaries = array();
	static protected $_loaders = array();
	static protected $_defaultDictionary = null;
	
	
	/**
	 * @param string $name
	 */
	protected function __construct($name)
	{
		$this->_dictionaryName = $name;
		if(isset(self::$_dictionaries[$name]))
			$this->_dictionary = self::$_dictionaries[$name];
	}

	/**
	 * Get current dictionary name
	 * @return string
	 */
	public function getName()
	{
		return $this->_dictionaryName;
	}
	
	/**
	 * Set default localization
	 * @param string $name
	 * @throws Exception
	 */
	static public function setDefaultDictionary($name)
	{
		if(!isset(self::$_dictionaries[$name]) && !isset(self::$_loaders[$name]))
			throw new Exception('Dictionary '.$name.' is not found');
		
		self::$_defaultDictionary = $name;	
	}

	/**
	 * Get default dictionary (lang)
	 * @return string
	 */
	static public function getDefaultDictionary()
	{
	  return self::$_defaultDictionary;
	}
	
	/**
	 * Add localization dictionary
	 * @param string $name — localization name
	 * @param Config_Abstract $dictionary — configuration object
	 * @return void
	 */
	static public function addDictionary($name , Config_Abstract $dictionary)
	{
		self::$_dictionaries[$name] = $dictionary;
	}
	
	/**
	 * Add localization loader
	 * @param string $name - dictionary name
	 * @param mixed $src - dictionary source
	 * @param integer $type - Config constant
	 */
	static public function addDictionaryLoader($name , $src , $type = Config::File_Array)
	{
		self::$_loaders[$name] = array('src'=> $src , 'type' =>$type);
	}

	
	protected function _loadDictionary($name)
	{
		if($this->_dictionary)
			return;
		
		if(isset(self::$_dictionaries[$name]))
		{
			$this->_dictionary = self::$_dictionaries[$name];
			return;
		}
		
		if(isset(self::$_loaders[$name]))
		{
			switch(self::$_loaders[$name]['type']){
				case Config::File_Array :
					self::$_dictionaries[$name] = static::storage()->get(self::$_loaders[$name]['src'] , true , true);
					$this->_dictionary = self::$_dictionaries[$name];
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
	public function get($key)
	{
		$this->_loadDictionary($this->_dictionaryName);
		
		if($this->_dictionary->offsetExists($key))
			return $this->_dictionary->get($key);
		else
			return '[' . $key . ']';
	}
	
	public function __get($key)
	{
		return $this->get($key);
	}

    public function __isset($key)
    {
        $this->_loadDictionary($this->_dictionaryName);

        return $this->_dictionary->offsetExists($key);
    }

    /**
	 * Convert the localization dictionary to JSON
	 * @return string
	 */
	public function getJson()
	{
		$this->_loadDictionary($this->_dictionaryName);
		return json_encode($this->_dictionary->__toArray());
	}

	/**
	 * Convert the localization dictionary to JavaScript object
	 * @return string
	 */
	public function getJsObject()
	{
		$this->_loadDictionary($this->_dictionaryName);
		$items = array();
		foreach($this->_dictionary as $k => $v)
			$items[] = $k . ':"' . $v . '"';
		
		return str_replace("\n","",'{' . implode(',' , $items) . '}');
	}

	/**
	 * Get link to localization dictionary by localization name or
	 * get default dictionary
	 * @param string $name optional, 
	 * @throws Exception
	 * @return Lang
	 */
	static public function lang($name = false)
	{
		if($name === false)
			$name = self::$_defaultDictionary;

		if(!isset(self::$_dictionaries[$name]) && !isset(self::$_loaders[$name]))
			throw new Exception('Lang::lang Dictionary "'.$name.'" is not found');

		return new self($name);
	}

	/**
	 * Get configuration storage
	 * @return Config_Storage
     */
	static public function storage()
	{
		static $store = false;

		if(!$store){
			$store = new Config_Storage();
		}

		return $store;
	}
}