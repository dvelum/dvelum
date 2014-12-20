<?php
class Backend_Cache_Manager
{
	/**
	 * reset all caches
	 * @return boolean
	 */
	static public function resetAll()
	{
		$cacheManager = new Cache_Manager();
		$list = $cacheManager->getRegistered();
		
		foreach ($list as $name=>$cache)
			if($cache)
				$cache->clean();
				
		return true;
	}
	
	static public function resetCache($name)
	{
		$cacheManager = new Cache_Manager();
		$cache = $cacheManager->get($name);
		
		if(!$cache)
			return false;
			
		return $cache->clean();
	}
}