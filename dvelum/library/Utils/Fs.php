<?php
class Utils_Fs
{
	/**
	 * Create class name from filepath
	 * @param string $path
	 * @return string or false
	 */
	static public function classFromPath($path)
	{
		// windows path hack
		$path = str_replace('\\','/', $path);
		$path = str_replace('//','/', $path);
		 
		if(File::getExt($path)!=='.php')
			return false;
	
		$path = substr($path, 0, -4);
	
		if(strpos($path , ('../')) === 0)
			$path = substr($path, 3);
		elseif(strpos($path , ('./')) === 0)
		$path = substr($path, 2);
		elseif(strpos($path , '/') === 0)
		$path = substr($path, 1);
		return implode('_' , array_map('ucfirst',explode('/', $path)));
	}
	
	/**
	 * Scan files and create class map for autoloader
	 * @param array $startPaths - paths for scan (relative paths)
	 * @param string $mapFile - path to map
	 * @return boolean
	 */
	static public function createClassMap(array $startPaths ,  $mapFile)
	{
		$map = array();

		foreach($startPaths as $v) {
			self::_scanClassDir($v, $map, $mapPackaged, $v);
		}
		ksort($map);
		Utils::exportArray($mapFile ,$map);
	}
	
	
	static protected function _scanClassDir($path , &$map , &$mapPackaged , $exceptPath)
	{
		$path = File::fillEndSep($path);
		$items = File::scanFiles($path , array('.php'), false);
	
		if(empty($items))
			return;
	
		foreach ($items as $item)
		{
			if(File::getExt($item) === '.php')
			{
				$parts = explode(DIRECTORY_SEPARATOR, str_replace($exceptPath,'', substr($item,0,-4)));
				$parts = array_map('ucfirst', $parts);
				$class = implode('_', $parts);

				if(!isset($map[$class])) 
					$map[$class] = $item;
			}
			else
			{
				self::_scanClassDir($item, $map, $mapPackaged , $exceptPath );
			}
		}
	}
}