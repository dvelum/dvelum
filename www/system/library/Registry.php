<?php
class Registry
{
	static protected $_data = array();
	
	/**
	 * Get stored value
	 * @param string $key
	 * @param string $namespace, optional
	 * @return mixed
	 * @throws Exception
	 */
	static public function get($key , $namespace = 'default')
	{
		if(!self::isValidKey($key , $namespace))
			throw new Exception('Registry undefined key ' . $namespace . '.' . $key);
		
		return self::$_data[$namespace][$key];
	}
	/**
	 * Store value
	 * @param string $key
	 * @param mixed $value
	 * @param string $namespace, optional
	 */
	static public function set($key , $value , $namespace = 'default')
	{
		self::$_data[$namespace][$key] = $value;
	}
	/**
	 * Remove item by key
	 * @param string $key
	 * @param string $namespace, optional
	 */
	static public function remove($key , $namespace = 'default')
	{
		if(isset(self::$_data[$namespace][$key]))
			unset(self::$_data[$namespace][$key]);
	}	
	/**
	 * Check if key exists
	 * @param string $key
	 * @param string $namespace, optional
	 * @return boolean
	 */
	static public function isValidKey($key , $namespace = 'default')
	{
		return isset(self::$_data[$namespace][$key]);
	}
}