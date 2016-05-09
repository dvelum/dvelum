<?php
class Template_Storage
{
    /**
     * Runtime cache of configuration files
     * @var array
     */
    static protected $runtimeCache = [];
    /**
     * Storage configuration options
     * @var array
     */
    protected $config = [];
    /**
     * Set configuration options
     * @param array $options
     */
    public function setConfig(array $options)
    {
        foreach($options as $k=>$v){
            $this->config[$k] = $v;
        }
    }
    /**
     * Get template real path  by local path
     * @param string $localPath
     * @param boolean $useCache, optional
     * @return string | false
     */
    public function get($localPath , $useCache = true)
    {
        $key = $localPath;

        if(isset(static::$runtimeCache[$key]) && $useCache)
            return static::$runtimeCache[$key];

        $filePath = false;

        $list = $this->config['paths'];

        foreach($list as $path)
        {
            if(!file_exists($path . $localPath))
                continue;

            $filePath = $path . $localPath;
            break;
        }

        if($filePath === false)
            return false;

        if($useCache)
            static::$runtimeCache[$key] = $filePath;

        return $filePath;
    }

    /**
     * Get template paths
     */
    public function getPaths()
    {
        return $this->config['paths'];
    }

    /**
     * Add templates path
     * @param string $path
     * @return void
     */
    public function addPath($path)
    {
        $this->config['paths'][] = $path;
    }
}