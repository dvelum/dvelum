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
 * Resources Class
 * Used for work with JS and CSS sources
 * @author Kirill A Egorov 2011
 */
class Resource
{
	protected static $_instance;
	protected static $_jsCacheUrl = null;
	protected static $_jsCachePath = null;
	protected static $_wwwRoot = null;
	protected static $_wwwPath = null;
	/**
	 * @var Cache_Interface
	 */
	protected static $_cache = false;

	protected $_jsFiles = array();
	protected $_rawFiles = array();
	protected $_cssFiles = array();
	protected $_rawJs = '';
	protected $_rawCss = '';
	protected $_inlineJs = '';

	protected function __construct(){}

	/**
	 * @return Resource
	 */
	public static function getInstance()
	{
		if(!isset(self::$_instance))
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Set chache core
	 * @param Cache_Interface $manager
	 */
	static public function setCache(Cache_Interface $manager)
	{
		self::$_cache = $manager;
	}

	/**
	 * Define cache paths
	 * @param string $url  - url to cache folder
	 * @param string $path - system path to cache folder
	 */
	static public function setCachePaths($url , $path)
	{
		self::$_jsCachePath = $path;
		self::$_jsCacheUrl = $url;
	}

	/**
	 * Set resources directory root path
	 * @param string  $path
	 */
	static public function setResourcePath($path)
	{
		self::$_wwwPath = $path;
	}
	/**
	 * Set resources url path
	 * @param string $path
	 */
	static public function setResourceRoot($path)
	{
		self::$_wwwRoot = $path;
	}

	/**
	 * Add javascript file to the contentent
	 *
	 * @param string $file- file path relate to document root
	 * @param integer $order  - include order
	 * @param boolean $minified - file already minified
	 * @param string $tag - file ident
	 */
	public function addJs($file, $order = false, $minified = false, $tag = false)
	{
		if ($file[0] === '/')
			$file = substr($file, 1);

		$hash = md5($file);

		if($order === false)
			$order = sizeof($this->_jsFiles);

		if(!isset($this->_jsFiles[$hash]))
			$this->_jsFiles[$hash] = array(
					'file' => $file,
					'order' => $order,
					'tag' => $tag,
					'minified' => $minified
			);
	}

	/**
	 * Add css file to the contentent
	 * @param string $file
	 * @param integer $order
	 */
	public function addCss($file , $order = false)
	{
		if($file[0] === '/')
			$file = substr($file, 1);

		$hash = md5($file);
		if($order === false)
			$order = sizeof($this->_cssFiles);

		if(!isset($this->_cssFiles[$hash]))
			$this->_cssFiles[$hash] = array(
					'file' => $file ,
					'order' => $order
			);
	}

	/**
	 * Add Java Script code
	 * (will be minified and cached)
	 * @param string $script
	 */
	public function addRawJs($script)
	{
		$this->_rawJs .= $script;
	}

	/**
	 * Add standalone JS file (no modifications)
	 * @param string $file - file path relative to the document root directory
	 */
	public function addJsRawFile($file)
	{
		if($file[0] === '/')
			$file = substr($file, 1);

		if(! in_array($file , $this->_rawFiles , true))
			$this->_rawFiles[] = $file;
	}

	/**
	 * Add inline Java Script code
	 * @param string $script
	 */
	public function addInlineJs($script)
	{
		$this->_inlineJs .= $script;
	}

	/**
	 * Add inline css syles
	 * @param string $script
	 */
	public function addRawCss($script)
	{
		$this->_rawCss .= $script;
	}
	/**
	 * Include JS resources by tag
	 * @param boolean $useMin
	 * @param boolean $compile
	 * @param string $tag
	 * @return string
	 */
	public function includeJsByTag($useMin = false , $compile = false , $tag = false)
	{
		$s = '';
		$fileList = $this->_jsFiles;

		foreach ($fileList as $k=>$v)
			if($v['tag']!=$tag)
				unset($fileList[$k]);
		/*
         * javascript files
         */
		if(!empty($fileList))
		{
			$fileList = Utils::sortByField($fileList, 'order');
			if($compile)
				$s .= '<script type="text/javascript" src="' .  self::$_wwwRoot . $this->_compileJsFiles($fileList , $useMin) . '"></script>' . "\n";
			else
				foreach($fileList as $file)
					$s .= '<script type="text/javascript" src="' .  self::$_wwwRoot . $file['file'] . '"></script>' . "\n";
		}

		return $s;
	}
	/**
	 * Returns javascript source tags. Include order: Files , Raw , Inline
	 * @param boolean $useMin - use Js minify
	 * @param boolean $compile - compile Files into one
	 * @return string
	 */
	public function includeJs($useMin = false , $compile = false , $tag = false)
	{
		$fileList = $this->_jsFiles;

		foreach ($fileList as $k=>$v)
			if($v['tag']!=$tag)
				unset($fileList[$k]);

		$s = '';
		/*
		 * Raw files
		 */
		if(!empty($this->_rawFiles))
			foreach($this->_rawFiles as $file)
				$s .= '<script type="text/javascript" src="' . self::$_wwwRoot . $file . '"></script>' . "\n";

		$s .=  $this->includeJsByTag($useMin , $compile , $tag);
		/*
		 * Raw javascript
		 */
		if(strlen($this->_rawJs))
		{
			$s .= '<script type="text/javascript" src="' . $this->cacheJs($this->_rawJs) . '"></script>' . "\n";
		}
		/*
		 * Inline javascript
		 */
		if(!empty($this->_inlineJs))
		{
			// it's too expensive
			//if($useMin)
			//	$this->_inlineJs = Code_Js_Minify::minify($this->_inlineJs);
			$s .= '<script type="text/javascript">' . "\n" . $this->_inlineJs . "\n" . ' </script>' . "\n";
		}
		return $s;
	}

	/**
	 * Create cache file for JS code
	 * @param string $code
     * @param boolean $minify, optional default false
     * @return string - file url
	 */
	public function cacheJs($code , $minify = false)
	{
        $hash = md5($code);
        $cacheFile = $hash . '.js';
        $cacheFile = Utils::createCachePath(self::$_jsCachePath , $cacheFile);

        if(!file_exists($cacheFile))
        {
            if($minify)
                $code = Code_Js_Minify::minify($code);

            file_put_contents($cacheFile, $code);
        }

        return str_replace(self::$_jsCachePath, self::$_wwwRoot . self::$_jsCacheUrl , $cacheFile);
	}

	/**
	 * Compile JS files cache
	 * @param array $files - file paths relative to the document root directory
	 * @param boolean $minify - minify scripts
	 * @return string  - cached file path
	 */
	protected function _compileJsFiles($files , $minify)
	{
		$validHash = $this->_getFileHash(Utils::fetchCol('file' , $files));

		$cacheFile = Utils::createCachePath(self::$_jsCachePath, $validHash . '.js');

		$cachedUrl = str_replace(self::$_jsCachePath , self::$_jsCacheUrl , $cacheFile);

		if(file_exists($cacheFile))
			return $cachedUrl;

		$str = '';
		foreach($files as $file)
		{
			$str .= "\n";

			$fileName = $file['file'];
			$paramsPos = strpos($fileName , '?' , true);

			if($paramsPos!==false){
				$fileName = substr($fileName, 0 , $paramsPos);
			}

			$content = file_get_contents(self::$_wwwPath . '/' . $fileName);

			if($minify && ! $file['minified'])
				$str .= Code_Js_Minify::minify($content);
			else
				$str .= $content;
		}
		file_put_contents($cacheFile , $str);
		return $cachedUrl;
	}

	/**
	 * Get a hash for the file list. Used to check for changes in files.
	 * @param array $files - File paths relative to the document root directory
	 * @return string
	 */
	protected function _getFileHash($array)
	{
		$listHash = md5(serialize($array));
		/*
		 * Checking if hash is cached
		 * (IO operations is too expensive)
		 */
		if(self::$_cache)
		{
			$dataHash = self::$_cache->load($listHash);
			if($dataHash)
				return $dataHash;
		}

		$dataHash = '';
		foreach($array as $file)
		{
			$paramsPos = strpos($file , '?' , true);
			if($paramsPos!==false)
			{
				$file = substr($file, 0 , $paramsPos);
			}
			$dataHash .= $file . ':' . filemtime(self::$_wwwPath . '/' . $file);
		}

		if(self::$_cache)
			self::$_cache->save(md5($dataHash), $listHash);

		return md5($dataHash);
	}

	/**
	 * Get html code for css files include
	 * @return string
	 */
	public function includeCss()
	{
		$s = '';

		if(!empty($this->_cssFiles))
		{
			$this->_cssFiles = Utils::sortByField($this->_cssFiles, 'order');

			foreach($this->_cssFiles as $k => $v)
				$s .= '<link rel="stylesheet" type="text/css" href="' . self::$_wwwRoot . $v['file'] . '" />' . "\n";
		}

		if(strlen($this->_rawCss))
			$s .= '<style type="text/css">' . "\n" . $this->_rawCss . "\n" . '</style>' . "\n";

		return $s;
	}

	/**
	 * Get raw JS code
	 * @return string
	 */
	public function getInlineJs()
	{
		return $this->_rawJs;
	}

	/**
	 * Clean raw js
	 */
	public function cleanInlineJs()
	{
		$this->_rawJs = '';
	}
}