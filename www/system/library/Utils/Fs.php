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
	 * @param string $mapFile - output php file path
	 * @param string $mapPackagesFile - output php file path (with packages)
	 * @param Config_File_Array - $packagesConfig
	 * @return void
	 */
	static public function createClassMap(array $startPaths ,  $mapFile , $mapPackagesFile , Config_File_Array $packagesConfig)
	{
		$packages = $packagesConfig->get('packages');
		$packPath = $packagesConfig->get('path');
		$packMap = array();
		
		if(!empty($packages))
			foreach ($packages as $key=>$items)
				if(!empty($items))
					foreach ($items['paths'] as $index=>$path)
						$packMap[$path] = $key;
		 
		$map = array();
		$mapPackaged = array();
	
		foreach ($startPaths as $v)
		    self::_scanClassDir($v, $map, $mapPackaged, $v , $packMap , $packagesConfig);
			
		ksort($map);
		ksort($packMap);
	
		$res1 = @file_put_contents($mapFile, '<?php return ' . var_export($map , true).'; ');
	
		$vars = '';
		$varNames = array();
		$varValues = array();
		foreach ($packagesConfig->get('packages') as $key=>$item)
		{
			if(!$item['active'])
				continue;
			$varName = '$_pkg_' . $key;
			$packPath = $packagesConfig->get('path') . $key . '.php';
			$vars.= $varName." ='".$packPath."';\n";
			$varNames[] = $varName;
			$varValues[] = "'".$packPath."'";
		}
	
		$s = str_replace($varValues, $varNames, var_export($mapPackaged , true)).';';
		$res2 = @file_put_contents($mapPackagesFile, '<?php '."\n" . $vars .' return ' . $s);
	
	
		if($res1 && $res2)
			return true;
		else
			return false;
	}
	
	
	static protected function _scanClassDir($path , &$map , &$mapPackaged , $exceptPath , $packages , $packagesCfg)
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
				$package = false;
	
				if(isset($packages[$item]) && $packagesCfg['packages'][$packages[$item]]['active'])
					$package = $packagesCfg->get('path') . $packages[$item] . '.php';
				else
					$package = $item;
	
				if(!isset($map[$class])) 
					$map[$class] = $item;
				if(!isset($mapPackaged[$class]))
					$mapPackaged[$class] = $package;
			}
			else
			{
				self::_scanClassDir($item, $map, $mapPackaged , $exceptPath , $packages, $packagesCfg);
			}
		}
	}
}