<?php
/**
 * System dictionary class
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2012  Kirill A Egorov,
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 */
class Dictionary
{
	protected static $_instances = array();

	protected static $_external = array();

	protected function __construct(){}

	protected function __clone(){}

	/**
	 * Path to configs
	 * @var string
	 */
	static protected $_configPath = null;

	/**
	 * @var Config_Abstract
	 */
	protected $_data;

	/**
	 * Set config files path
	 * @param string $path
	 * @return void
	 */
	static public function setConfigPath($path)
	{
		self::$_configPath = $path;
	}

	/**
	 * Add external dictionaries list
	 * @param array $list
	 */
	static public function addExternal(array $list)
	{
		self::$_external = array_merge(self::$_external , $list);
	}

	/**
	 * Get external dictionaries list
	 * @return multitype:
	 */
	static public function getExternal()
	{
		return self::$_external;
	}

	/**
	 * Instantiate a dictionary by name
	 * @param string $name
	 * @return Dictionary
	 */
	static public function getInstance($name)
	{
		$name = strtolower($name);
		if(!isset(self::$_instances[$name]))
		{
			$obj = new self();
			if(isset(self::$_external[$name]))
				$obj->_data = Config::factory(Config::File_Array , self::$_external[$name]);
			else
				$obj->_data = Config::factory(Config::File_Array , self::$_configPath . $name . '.php');

			self::$_instances[$name] = $obj;
		}
		return self::$_instances[$name];
	}

	/**
	 * Check if the key exists in the dictionary
	 * @param string $key
	 * @return boolean
	 */
	public function isValidKey($key)
	{
		return $this->_data->offsetExists($key);
	}

	/**
	 * Get value by key
	 * @param string $key
	 * @return string
	 */
	public function getValue($key)
	{
		return $this->_data->get($key);
	}

	/**
	 * Get dictionary data
	 * @return array
	 */
	public function getData()
	{
		return $this->_data->__toArray();
	}

	/**
	 * Add a record
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public function addRecord($key , $value)
	{
		return $this->_data->set($key , $value);
	}

	/**
	 * Delete record by key
	 * @param string $key
	 * @return boolean
	 */
	public function removeRecord($key)
	{
		return $this->_data->remove($key);
	}

	/**
	 * Save changes
	 * @return boolean
	 */
	public function saveChanges()
	{
		if(!$this->_data->save())
			return false;

		$dm = new Dictionary_Manager();
		$dm->resetCache();
		return true;
	}

    /**
	 * Get dictionary as JavaScript code representation
	 * @param boolean $addAll — add value 'All' with a blank key,
	 * @param boolean $addBlank - add empty value
	 * is used in drop-down lists
	 * @return string
	 */
	public function __toJs($addAll = false , $addBlank = false)
	{
		$result = array();

		if($addAll)
			$result[] = array('id' => '' , 'title' => Lang::lang()->get('ALL'));

		if(!$addAll && $addBlank)
		    $result[] = array('id' => '' , 'title' => '');

		foreach($this->_data as $k => $v)
			$result[] = array('id' => strval($k) , 'title' => $v);

		return json_encode($result);
	}

}