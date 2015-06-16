<?php
class Cache_Manager
{
	static protected $_connections = array();

	/**
	 * Register cache adapter
	 * @param string $name
	 * @param Cache_Interface $cache
	 */
	public function register($name , Cache_Interface $cache)
	{
		self::$_connections[$name] = $cache;
	}
	/**
	 * Get cache adapter
	 * @param string $name
	 * @return Cache_Interface|boolean
	 */
	public function get($name)
	{
		if(!isset(self::$_connections[$name]))
			return false;
		else
			return self::$_connections[$name];
	}
	/**
	 * Remove cache adapter
	 * @param string $name
	 */
	public function unregister($name)
	{
		if(!isset(self::$_connections[$name]))
			return;

		unset(self::$_connections[$name]);
	}
	/**
	 * Get list of registered adapters
	 * @return array
	 */
	public function getRegistered()
	{
		return self::$_connections;
	}

	/**
	 * Init Cache adapter by config
	 * @param string $name
	 * @param array $config
	 * @return boolean|Cache_Interface
	 */
	public function connect($name, array $config)
	{
		$cache = false;

	  if(isset($config['enabled']) && $config['enabled'])
	    $cache = new $config['backend']['name']($config['backend']['options']);

		self::$_connections[$name] = $cache;

		return $cache;
	}
}