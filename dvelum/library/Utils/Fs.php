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

}