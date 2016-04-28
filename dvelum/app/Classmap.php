<?php
/**
 * Classmap builder
 */
class Classmap
{
    protected $map = [];
    /**
     * @var Config_Abstract $appConfig
     */
    protected $appConfig;
    /**
     * @var array
     */
    protected $autoloaderCfg = [];

    public function __construct(Config_Abstract $appConfig)
    {
        $this->appConfig = $appConfig;
        $this->autoloaderCfg = $this->appConfig->get('autoloader');
    }

    public function load()
    {
        $map = Config::storage()->get($this->autoloaderCfg);
        if(!empty($map)){
            $this->map = $map->__toArray();
        }
    }

    public function update()
    {
        $this->map = [];

        $paths =  $this->autoloaderCfg['paths'];

        foreach($paths as $v)
        {
            $v = File::fillEndSep($v);
            if(is_dir($v)){
                $this->findClasses($v,$v);
            }
        }
        ksort($this->map);
    }

    /**
     * Find PHP Classes
     * @param $path
     * @param $exceptPath
     * @throws Exception
     */
    protected function findClasses($path , $exceptPath)
    {
        $path = File::fillEndSep($path);
        $items = File::scanFiles($path , ['.php'], false);

        if(empty($items))
            return;

        foreach ($items as $item)
        {
            if(File::getExt($item) === '.php')
            {
                $parts = explode('/', str_replace($exceptPath,'', substr($item,0,-4)));
                $parts = array_map('ucfirst', $parts);
                $class = implode('_', $parts);

                if(!isset($map[$class]))
                    $this->map[$class] = $item;
            }
            else
            {
                $this->findClasses($item , $exceptPath);
            }
        }
    }

    /**
     * save class map
     * @return boolean
     */
    public function save()
    {
        $writePath = Config::storage()->getWrite() . $this->autoloaderCfg['map'];
        return Utils::exportArray($writePath, $this->map);
    }
}