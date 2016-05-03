<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * A class that contains methods for working with filesystem
 * @author Kirill A Egorov 2011
 * @license GPLv3
 */
class File
{
	const Files_Dirs = 0;
	const Files_Only = 1;
	const Dirs_Only = 2;

	/**
	 * Default Separator in file paths
	 * Can be disabled by  setDirectorySeparator (false)
	 * @var mixed string | false
	 */
	static protected $directorySeparator = '/';

	/**
	 * Set directory separator for output data
	 * @param mixed $sep string or false
	 */
	static public function setDirectorySeparator($sep)
	{
		self::$directorySeparator = $sep;
	}

	/**
	 * Get file extension
	 * @param string $name
	 * @return string
	 */
	static public function getExt($name)
	{
		return strrchr(strtolower($name), '.');
	}

	/**
	 * Add path separator to the end of string
	 * @param string $path
	 * @return string
	 */
	static public function fillEndSep($path)
	{
		$length = strlen($path);
		if(!$length || $path[$length - 1] !== DIRECTORY_SEPARATOR)
			$path .= DIRECTORY_SEPARATOR;

		return $path;
	}

	/**
	 * Get file list
	 * @param string $path
	 * @param array $filter - optional  aray of file extensions to search for
	 * @param boolean $recursive - optional	use recursion (default true)
	 * @param integer $type - optional File::Dirs_Only | File::Files_Dirs | File::Files_Only (default File::Files_Dirs)
	 * @param integer $mode - optional RecursiveIteratorIterator::SELF_FIRST | RecursiveIteratorIterator::CHILD_FIRST (default RecursiveIteratorIterator::SELF_FIRST)
	 * @throws Exception
	 * @return array
	 */
	static public function scanFiles($path , $filter = array() , $recursive = true , $type = File::Files_Dirs , $mode = RecursiveIteratorIterator::SELF_FIRST)
	{
		$path = self::fillEndSep($path);
		$files = array();
		$collectDirs = false;
		$collectFiles = false;

		switch($type)
		{
			case self::Files_Only :
				$mode = RecursiveIteratorIterator::LEAVES_ONLY;
				$collectFiles = true;
				break;
			case self::Dirs_Only :
				$collectDirs = true;
				break;
			case self::Files_Dirs :
				$collectDirs = true;
				$collectFiles = true;
				break;
		}
		try
		{
			if($recursive)
				$dirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path , RecursiveDirectoryIterator::SKIP_DOTS) , $mode);
			else
				$dirIterator = new FilesystemIterator($path , FilesystemIterator::SKIP_DOTS);
		}
		catch(Exception $e)
		{
			throw new Exception('You tried to read nonexistent dir: ' . $path );
		}

		$changeSep = false;

		if(self::$directorySeparator && self::$directorySeparator!==DIRECTORY_SEPARATOR){
			$changeSep = self::$directorySeparator;
		}

		foreach($dirIterator as $name => $object)
		{
			$add = false;
			$isDir = $object->isDir();

			if(($isDir && $collectDirs) || (!$isDir && $collectFiles))
				$add = true;

			if(! empty($filter))
				if(! $isDir && ! in_array(self::getExt($name) , $filter , true))
					$add = false;

			if($add){
				if($changeSep){
					$name = str_replace(DIRECTORY_SEPARATOR , $changeSep ,$name);
					$name = str_replace($changeSep.$changeSep , $changeSep, $name);
				}
				$files[] = $name;
			}
		}
		unset($dirIterator);
		return $files;
	}

	/**
	 * Adds given files to existing archive $fileName or create a new archive by the same path
	 * @param string $fileName - path to file
	 * @param mixed $files array or string
	 * @param string $localRoot optional
	 * @return bool
	 */
	static public function zipFiles($fileName , $files , $localRoot = '')
	{
		if(substr($fileName, -4)!=='.zip')
			$fileName.='.zip';

		// delete existing file
		if(file_exists($fileName)){
			unlink($fileName);
		}

		$zip = new ZipArchive();

		/**
		 * ZIPARCHIVE::CREATE (integer)
		 * Create the archive if it does not exist.
		 */
		if($zip->open($fileName , ZIPARCHIVE::CREATE) !== true)
			return false;

		if(is_string($files))
			$files = array($files);

		if(!empty($files))
		{
			foreach ($files as $file)
			{
				if (is_dir($file)){
					if($localRoot!==''){
						$zip->addEmptyDir(str_replace($localRoot, '', $file));
					}else{
						$zip->addEmptyDir($file);
					}
					continue;
				}

				if ($localRoot !== '') {
					$zip->addFile($file, str_replace($localRoot, '', $file));
				} else {
					$zip->addFile($file);
				}
			}
		}
		return $zip->close();
	}

	/**
	 * Extract all files
	 * @param string $source
	 * @param string $destination
	 * @param array|string|boolean $fileEntries - optional - The entries to extract. It accepts either a single entry name or an array of names.
	 * @return bool
	 */
	static public function unzipFiles($source , $destination , $fileEntries = false)
	{
		$zip = new ZipArchive();

		if($zip->open($source) !== true)
			return false;

		if(!empty($fileEntries)) {
			if (!$zip->extractTo($destination, $fileEntries)) {
				return false;
			}
		}else {
			if (!$zip->extractTo($destination)) {
				return false;
			}
		}

		return $zip->close();
	}

	/**
	 * Get Archive items list
	 * @param string $source
	 * @return array
	 */
	static public function getZipItemsList($source)
	{
		$zip = new ZipArchive();

		if($zip->open($source) !== true)
			return false;

		$zipSize = $zip->numFiles - 1;

		$itemsList = array();

		while ($zipSize >= 0){
			$itemsList[] = $zip->getNameIndex($zipSize);
			--$zipSize;
		}
		return  $itemsList;
	}

	/**
	 * Recursively remove files and dirs from given $pathname
	 * @param string $pathname
	 * @param bool $removeParentDir
	 * @return boolean
	 */
	static public function rmdirRecursive($pathname , $removeParentDir = false)
	{
		$filesDirs = File::scanFiles($pathname , false , true , File::Files_Dirs , RecursiveIteratorIterator::CHILD_FIRST);

		foreach($filesDirs as $v)
		{
			if(is_dir($v))
			{
				if(!rmdir($v))
					return false;
			}
			elseif (is_file($v) || is_link($v))
			{
				if(!unlink($v))
					return false;
			}
			else
			{
				return false;
			}
		}

		if($removeParentDir)
			if(!rmdir($pathname))
				return false;

		return true;
	}

    /**
     * Copy directory contents
     * @param string $source
     * @param string $dest
     * @return bool
     */
    static public function copyDir($source, $dest)
    {
        if(!is_dir($dest)){
            if(!@mkdir($dest, 0755, true)){
                return false;
            }
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $source,
                \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ( $iterator as $item) {
            if ($item->isDir()) {
               $subDir =  $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
               if(!is_dir($subDir) && !@mkdir($subDir, 0755)){
                   return false;
               }
            } else {
                if(!@copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName())){
                    return false;
                }
            }
        }
        return true;
    }

	/**
	 * Copies files and dirs to $destPath
	 * @param string $destPath
	 * @param mixed $files
	 * @param string $localRoot - optional
	 * @return bool
	 */
	static public function copyFiles($destPath , $files , $localRoot = '')
	{
		if(!file_exists($destPath))
			if(!mkdir($destPath, 0775))
				return false;

		if(is_string($files))
			$files = array($files);

		if(empty($files))
			return false;

		foreach ($files as $path)
		{
			$dest = $destPath . str_replace($localRoot , '' , $path);
			if(is_dir($path))
			{
				if(!file_exists($dest))
					if(!mkdir($dest, 0775, true))
						return false;
			}
			else
			{
				$dir = dirname($dest);
				if(!file_exists($dir))
					if(!mkdir($dir, 0775, true))
						return false;
				if(!copy($path, $dest))
					return false;
			}
		}
		return true;
	}

	/**
	 * Find the last existing dir by $path
	 * @param string $path
	 * @return boolean
	 */
	static public function getExistingDirByPath($path)
	{
		if(is_file($path))
			return dirname($path);

		if(is_dir($path))
			return $path;

		$pathArr = explode('/', $path);
		for ($i = sizeof($pathArr) - 1; $i > 0; $i--)
		{
			unset($pathArr[$i]);

			$cur = implode('/', $pathArr);

			if(is_dir($cur))
				return $cur;
		}
		return false;
	}

	/**
	 * Checks writing permissions for files.
	 * Returns array with paths (wich is not writable) or true on success
	 * @param array $files
	 * @return mixed
	 */
	static public function checkWritePermission(array $files)
	{
		$cantWrite = array();
		foreach ($files as $path)
		{
			if(is_file($path))
			{
				if(!is_writable($path))
					$cantWrite[] = $path;
				continue;
			}

			if(!is_writable(File::getExistingDirByPath($path)))
				$cantWrite[] = $path;
		}

		if(empty($cantWrite))
			return true;
		else
			return $cantWrite;
	}
}