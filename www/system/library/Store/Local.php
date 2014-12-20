<?php
/**
 * The class allows you to store data locally in the form of key value pairs
 * Note that null value causes the keyExists() method return false (for better perfomance)
 * @author Kirill A Egorov 2008
 * @package Store
 */
class Store_Local implements Store_Interface
{
	protected $_storage;
	protected $_name;
	protected static $_instances = array();

	protected function __construct($name)
	{
		$this->_name = $name;
		$this->_storageConnect();
	}

	static public function getInstance($name = "default")
	{
		if(! isset(self::$_instances[$name]))
			self::$_instances[$name] = new self($name);
		return self::$_instances[$name];
	}

	/**
	 * Instantiate storage
	 * @return void
	 */
	protected function _storageConnect()
	{
		$this->_storage = array();
	}

	/**
	 * (non-PHPdoc)
	 * @see www/library/Store/Store_Interface#getData()
	 */
	public function getData()
	{
		return $this->_storage;
	}

	/**
	 * Get items count
	 * @return integer
	 */
	public function getCount()
	{
		return sizeof($this->_storage);
	}

	/**
	 * (non-PHPdoc)
	 * @see www/library/Store/Store_Interface#get($key)
	 */
	public function get($key)
	{
		if(isset($this->_storage[$key]))
			return $this->_storage[$key];
		return null;
	}

	/**
	 * Note that null value causes keyExists return false (for better perfomance)
	 * (non-PHPdoc)
	 * @see www/library/Store/Store_Interface#set($key, $val)
	 */
	public function set($key , $value)
	{
		$this->_storage[$key] = $value;
	}

	/**
	 * (non-PHPdoc)
	 * @see www/library/Store/Store_Interface#setValues($array)
	 */
	public function setValues(array $array)
	{
		foreach($array as $k => $v)
			$this->set($k , $v);
	}

	/**
	 * Note that null value causes the keyExists() method return false (for better perfomance)
	 * (non-PHPdoc)
	 * @see www/library/Store/Store_Interface#keyExists($key)
	 */
	public function keyExists($key)
	{
		return isset($this->_storage[$key]);
	}

	/**
	 * (non-PHPdoc)
	 * @see www/library/Store/Store_Interface#remove($key)
	 */
	public function remove($key)
	{
		unset($this->_storage[$key]);
	}

	/**
	 * (non-PHPdoc)
	 * @see Store_Interface::clear()
	 */
	public function clear()
	{
		$this->_storage = array();
	}
	/**
	 * Replace store data
	 * @param array $data
	 */
	public function setData(array $data)
	{
		$this->_storage = $data;
	}
}