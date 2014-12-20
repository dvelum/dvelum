<?php

class Db_Query_Storage
{
	/**
	 * @property Cache_Interface
	 */
	protected static $_cache = false;
	protected static $_instances = array();
	/**
	 * Storage adapter
	 * @property Db_Adapter_Interface
	 */
	protected $_adapter = null;
	/**
	 * Adapter Class name
	 * @property string
	 */
	protected $_adapterClass = null;

	/**
	 * Set chache core
	 * @param Cache_Interface $manager
	 */
	static public function setCache(Cache_Interface $manager)
	{
		self::$_cache = $manager;
	}
	/**
	 * @param string $adapter - Adapter name
	 * @return Db_Query_Storage_Adapter_Abstract
	 */
	static public function getInstance($adapter)
	{
		if(!isset(self::$_instances[$adapter]))
			self::$_instances[$adapter] = new self($adapter);
		
		return self::$_instances[$adapter];
	}

	/**
	 * @param string $adapter
	 */
	protected function __construct($adapter)
	{
		$className = 'Db_Query_Storage_Adapter_' . ucfirst($adapter);
		
		if(! class_exists($className))
			trigger_error('Invalid Adapter');
		
		$this->_adapter = new $className();
		$this->_adapterClass = $className;
		
		if(! $this->_adapter instanceof Db_Query_Storage_Adapter_Abstract)
			trigger_error('Invalid Adapter');
	}

	protected function __clone(){}

	/**
	 * Get Adabpter object
	 * @return Db_Query_Storage_Adapter_Abstract
	 */
	public function getAdapter()
	{
		return $this->_adapter;
	}

	/**
	 * Calculate cache index
	 * @param string $id
	 * @return string
	 */
	public function cacheIndex($id)
	{
		return md5('db_query_' . $this->_adapterClass . '_' . $id);
	}

	/**
	 * Load Db_Query
	 * @param string $id
	 * @return Db_Query
	 */
	public function load($id)
	{		
		$cacheIndex = $this->cacheIndex($id);
		
		$query = false;
		
		if(self::$_cache)
		{
			$query = self::$_cache->load($cacheIndex);
			if($query instanceof Db_Query)
				return $query;
		}
		
		$query = $this->_adapter->load($id);
		
		if(!$query instanceof Db_Query)
			return false;
		
		if(self::$_cache)
			self::$_cache->save($query , $cacheIndex);
			
		return $query;	
	}

	/**
	 * Save Db_Query
	 * @param string $id
	 * @param Db_Query $obj
	 * @return boolean
	 */
	public function save($id , Db_Query $obj)
	{
		if(!$this->_adapter->save($id , $obj))
			return false;
			
		if(self::$_cache)
			self::$_cache->save($obj , $this->cacheIndex($id));
		
		return true;
	}

	/**
	 * Remove Db_Query
	 * @param string $id
	 */
	public function delete($id)
	{
		if(self::$_cache)
			self::$_cache->remove($this->cacheIndex($id));
		return $this->_adapter->delete($id);
	}
}