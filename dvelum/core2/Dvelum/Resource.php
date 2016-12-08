<?php

namespace Dvelum;

use Dvelum\Config;

class Resource
{
    /**
     * @var Config\Config
     */
    protected $config;

    /**
     * @var \Cache_Interface
     */
    protected $cache = false;

    /**
     * @return Resource
     */
    static public function factory()
    {
        static $instance = null;

        if(empty($instance)){
            $instance = new static();
        }

        return $instance;
    }

    public function setConfig(Config\Config $config)
    {
        $this->config = $config;
        if($this->config->offsetExists('cache')){
            $this->cache = $this->config->get('cache');
        }
    }
    
    protected $jsFiles = [];
    protected $rawFiles = [];
    protected $cssFiles = [];
    protected $rawJs = '';
    protected $rawCss = '';
    protected $inlineJs = '';

    protected function __construct(){}
    

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
            $order = sizeof($this->jsFiles);

        if(!isset($this->jsFiles[$hash]))
        {
            $item =  new \stdClass();
            $item->file = $file;
            $item->order = $order;
            $item->tag = $tag;
            $item->minified = $minified;

            $this->jsFiles[$hash] = $item;

        }
    }

    /**
     * Add css file to the contentent
     * @param string $file
     * @param mixed $order
     */
    public function addCss($file , $order = false)
    {
        if($file[0] === '/')
            $file = substr($file, 1);

        $hash = md5($file);

        if($order === false){
            $order = sizeof($this->cssFiles);
        }

        if(!isset($this->cssFiles[$hash]))
        {
            $item =  new \stdClass();
            $item->file = $file;
            $item->order = $order;
            $this->cssFiles[$hash] = $item;
        }
    }

    /**
     * Add Java Script code
     * (will be minified and cached)
     * @param string $script
     */
    public function addRawJs($script)
    {
        $this->rawJs .= $script;
    }

    /**
     * Add standalone JS file (no modifications)
     * @param string $file - file path relative to the document root directory
     */
    public function addJsRawFile($file)
    {
        if($file[0] === '/')
            $file = substr($file, 1);

        if(! in_array($file , $this->rawFiles , true))
            $this->rawFiles[] = $file;
    }

    /**
     * Add inline Java Script code
     * @param string $script
     */
    public function addInlineJs($script)
    {
        $this->inlineJs .= $script;
    }

    /**
     * Add inline css syles
     * @param string $script
     */
    public function addRawCss($script)
    {
        $this->rawCss .= $script;
    }
    /**
     * Include JS resources by tag
     * @param boolean $useMin
     * @param boolean $compile
     * @param mixed $tag
     * @return string
     */
    public function includeJsByTag($useMin = false , $compile = false , $tag = false)
    {
        $s = '';
        $fileList = $this->jsFiles;

        foreach ($fileList as $k=>$v){
            if($v->tag != $tag){
                unset($fileList[$k]);
            }
        }

        /*
         * javascript files
         */
        if(!empty($fileList))
        {
            $fileList = \Utils::sortByProperty($fileList, 'order');
            if($compile)
                $s .= '<script type="text/javascript" src="' .  $this->config->get('wwwRoot') . $this->compileJsFiles($fileList , $useMin) . '"></script>' . "\n";
            else
                foreach($fileList as $file)
                    $s .= '<script type="text/javascript" src="' .   $this->config->get('wwwRoot') . $file->file . '"></script>' . "\n";
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
        $fileList = $this->jsFiles;

        foreach ($fileList as $k=>$v){
            if($v->tag != $tag){
                unset($fileList[$k]);
            }
        }

        $s = '';
        /*
         * Raw files
         */
        if(!empty($this->rawFiles))
            foreach($this->rawFiles as $file)
                $s .= '<script type="text/javascript" src="' .  $this->config->get('wwwRoot') . $file . '"></script>' . "\n";

        $s .=  $this->includeJsByTag($useMin , $compile , $tag);
        /*
         * Raw javascript
         */
        if(strlen($this->rawJs))
        {
            $s .= '<script type="text/javascript" src="' . $this->cacheJs($this->rawJs) . '"></script>' . "\n";
        }
        /*
         * Inline javascript
         */
        if(!empty($this->inlineJs))
        {
            // it's too expensive
            //if($useMin)
            //	$this->inlineJs = Code_Js_Minify::minify($this->inlineJs);
            $s .= '<script type="text/javascript">' . "\n" . $this->inlineJs . "\n" . ' </script>' . "\n";
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
        $cacheFile = \Utils::createCachePath( $this->config->get('jsCachePath') , $cacheFile);

        if(!file_exists($cacheFile))
        {
            if($minify)
                $code = \Code_Js_Minify::minify($code);

            file_put_contents($cacheFile, $code);
        }

        return str_replace($this->config->get('jsCachePath'), $this->config->get('wwwRoot'). $this->config->get('jsCacheUrl'), $cacheFile);
    }

    /**
     * Compile JS files cache
     * @param array $files - file paths relative to the document root directory
     * @param boolean $minify - minify scripts
     * @return string  - cached file path
     */
    protected function compileJsFiles($files , $minify)
    {
        $validHash = $this->getFileHash(\Utils::fetchCol('file' , $files));

        $cacheFile = \Utils::createCachePath($this->config->get('jsCachePath'), $validHash . '.js');

        $cachedUrl = \str_replace($this->config->get('jsCachePath'), $this->config->get('jsCacheUrl') , $cacheFile);

        if(file_exists($cacheFile))
            return $cachedUrl;

        $str = '';
        foreach($files as $item)
        {
            $str .= "\n";

            $fileName = $item->file;
            $paramsPos = \strpos($fileName , '?' , true);

            if($paramsPos!==false){
                $fileName = \substr($fileName, 0 , $paramsPos);
            }

            $content = \file_get_contents( $this->config->get('wwwPath') . '/' . $fileName);

            if($minify && ! $item->minified)
                $str .= \Code_Js_Minify::minify($content);
            else
                $str .= $content;
        }
        \file_put_contents($cacheFile , $str);
        return $cachedUrl;
    }

    /**
     * Get a hash for the file list. Used to check for changes in files.
     * @param array $files - File paths relative to the document root directory
     * @return string
     */
    protected function getFileHash($array)
    {
        $listHash = \md5(\serialize($array));
        /*
         * Checking if hash is cached
         * (IO operations is too expensive)
         */
        if($this->cache)
        {
            $dataHash = $this->cache->load($listHash);
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
            $dataHash .= $file . ':' . filemtime( $this->config->get('wwwPath') . '/' . $file);
        }

        if($this->cache)
            $this->cache->save(\md5($dataHash), $listHash);

        return \md5($dataHash);
    }

    /**
     * Get html code for css files include
     * @return string
     */
    public function includeCss()
    {
        $s = '';

        if(!empty($this->cssFiles))
        {
            $this->cssFiles = \Utils::sortByProperty($this->cssFiles, 'order');

            foreach($this->cssFiles as $k => $v)
                $s .= '<link rel="stylesheet" type="text/css" href="' . $this->config->get('wwwRoot') . $v->file . '" />' . "\n";
        }

        if(strlen($this->rawCss))
            $s .= '<style type="text/css">' . "\n" . $this->rawCss . "\n" . '</style>' . "\n";

        return $s;
    }

    /**
     * Get raw JS code
     * @return string
     */
    public function getInlineJs()
    {
        return $this->rawJs;
    }

    /**
     * Clean raw js
     */
    public function cleanInlineJs()
    {
        $this->rawJs = '';
    }

}
