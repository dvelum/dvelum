<?php
class Dictionary_Manager
{
	const CACHE_KEY_LIST = 'Dictionary_Manager_list';
	const CACHE_KEY_DATA_HASH = 'Dictionary_Manager_dataHash';
	
	protected $_path ='';
	/**
	 * @var Cache_Interface
	 */
	protected $_cache = false;
	
	static protected $_list = null;
	
	/**
	 * Valid dictionary local cache
	 * @var array
	 */
	static protected $_validDictionary = array();

	public function __construct()
	{
		$cfg = Registry::get('main' , 'config');
		$this->_path = $cfg['dictionary'];
		$cacheManager = new Cache_Manager();
		$this->_cache = $cacheManager->get('data');
				
		if($this->_cache && $list = $this->_cache->load(self::CACHE_KEY_LIST))
			self::$_list = $list;
	}
	
	/**
	 * Get list of dictionaries
	 * return array
	 */
	public function getList()
	{
		if(!is_null(self::$_list))
			return array_keys(self::$_list);
					
		$files = File::scanFiles($this->_path, array('.php'), false, File::Files_Only);
		$list = array();
		
		if(!empty($files)){
			foreach($files as $path){
				$name = substr(basename($path),0,-4);
				$list[$name] = $path;
			}
		}
		
		$external = Dictionary::getExternal();
		if(!empty($external))
			$list = array_merge($list,$external);

		self::$_list = $list;
		
		if($this->_cache)
			$this->_cache->save($list, self::CACHE_KEY_LIST);
			
		return array_keys($list);
	}
	/**
	 * Create dictionary
	 * @param string $name
	 * @return boolean
	 */
	public function create($name)
	{
		if(!file_exists($this->_path . $name . '.php') && Config_File_Array::create($this->_path . $name . '.php'))
		{
			self::$_validDictionary[$name]=true;
			$this->resetCache();
			return true;
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
		if(!@rename($this->_path.$oldName.'.php', $this->_path.$newName.'.php'))
			return false;
				
		if(isset(self::$_validDictionary[$oldName]))
			unset(self::$_validDictionary[$oldName]);
			
		self::$_validDictionary[$newName]=true;
		
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
		if(isset(self::$_validDictionary[$name]))
			return true;
			
		if(is_file($this->_path . $name . '.php'))
		{
			self::$_validDictionary[$name] = true;
			return true;
		}
		else
		{
			return false;
		}	
	}
	/**
	 * Remove dictionary
	 * @param string $name
	 * @return boolean
	 */
	public function remove($name)
	{
		$file = $this->_path . $name . '.php';
		if(!is_file($file) || !@unlink($file))
			return false;
			
		if(isset(self::$_validDictionary[$name]))
			unset(self::$_validDictionary[$name]);
			
		$this->resetCache();
			
		return true;
	}
	/**
	 * Reset cache
	 */
	public function resetCache()
	{
		if(!$this->_cache)
			return;
		
		$this->_cache->remove(self::CACHE_KEY_LIST);
		$this->_cache->remove(self::CACHE_KEY_DATA_HASH);
	}
	/**
	 * Get data hash (all dictionaries data)
	 * Enter description here ...
	 */
	public function getDataHash()
	{
		if($this->_cache && $hash = $this->_cache->load(self::CACHE_KEY_DATA_HASH))
			return $hash;
			
		$s='';
		$list = $this->getList();
		
		if(!empty($list))	
			foreach ($list as $name)	
				$s.= $name.':'.Dictionary::getInstance($name)->__toJs();
					
		$s = md5($s);
		
		if($this->_cache)
			$this->_cache->save($s, self::CACHE_KEY_DATA_HASH);

		return $s;
	}
}