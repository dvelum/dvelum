<?php
class Config_Storage
{
    /**
     * Runtime cache of configuration files
     * @var array
     */
    static protected $runtimeCache = array();
    /**
     * Storage configuration options
     * @var array
     */
    protected $config;
    /**
     * Debugger log
     * @var array
     */
    protected $debugInfo = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get config by local path
     * @param string $localPath
     * @param boolean $useCache, optional
     * @param boolean $merge, optional merge with main config
     * @return Config_Abstract | false
     */
    public function get($localPath , $useCache = true , $merge = true)
    {
        $key = $localPath.intval($merge);

        if(isset(static::$runtimeCache[$key]) && $useCache)
            return static::$runtimeCache[$key];

        $data = false;

        $list = $this->config['file_array']['paths'];

        if(!$merge)
            $list = array_reverse($list);

        foreach($list as $path)
        {
            if(!file_exists($path . $localPath))
                continue;

            $cfg = $path . $localPath;


            if($this->config['debug'])
                $this->debugInfo[] = $cfg;

            if(!$merge){
                $data = include $cfg;
                break;
            }

            if($data === false){
                $data = include $cfg;
            }else{
                $cfgData = include $cfg;
                $data = array_merge($data , $cfgData);
            }
        }

        if($data === false)
            return false;

        $object = new Config_File_Array($this->config['file_array']['write'] . $localPath , false);
        $object->setApplyTo($this->config['file_array']['apply_to'] . $localPath );
        $object->setData($data);

        if($useCache)
            static::$runtimeCache[$key] = $object;

        return $object;
    }

    /**
     * Find path for config file (no merge)
     * @param $localPath
     */
    public function getPath($localPath)
    {
        $configPath = false;
        $list = array_reverse($this->config['file_array']['paths']);

        foreach($list as $path)
        {
            if(!file_exists($path . $localPath))
                continue;

            return $path . $localPath;
        }
    }

    /**
     * Check if config file exists
     * @param $localPath
     * @return bool
     */
    public function exists($localPath)
    {
        foreach($this->config['file_array']['paths'] as $path)
        {
            if(file_exists($path . $localPath))
                return true;
        }
        return false;
    }

	/**
	 * Get storage paths
	 * @return array
	 */
	public function getPaths()
	{
		return $this->config['file_array']['paths'];
	}

	/**
	 * Get write path
	 * @return string
	 */
	public function getWrite()
	{
		return $this->config['file_array']['write'];
	}

    /**
     * Get debug information. (loaded configs)
     * @return array
     */
    public function getDebugInfo()
    {
        return $this->debugInfo;
    }
}