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
        $this->autoloaderCfg = Config::storage()->get('autoloader.php')->__toArray();
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

        $psr4 = $this->autoloaderCfg['psr-4'];
        foreach ($psr4 as $baseSpace=>$path)
        {
            $v = File::fillEndSep($path);
            if(is_dir($v)){
                $this->findPsr4Classes($v,$v, $baseSpace);
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
    protected function findClasses(string $path , string $exceptPath)
    {
        $path = File::fillEndSep($path);
        $items = File::scanFiles($path , ['.php'], false);

        if(empty($items))
            return;

        foreach ($items as $item)
        {
            if(File::getExt($item) === '.php')
            {
                if(!empty($this->autoloaderCfg['noMap'])){
                    $found = false;
                    foreach($this->autoloaderCfg['noMap'] as $excludePath){
                        if(strpos($item, $excludePath)!==false){
                            $found = true;
                            break;
                        }
                    }
                    if($found){
                        continue;
                    }
                }

                $parts = explode('/', str_replace($exceptPath,'', substr($item,0,-4)));
                $parts = array_map('ucfirst', $parts);
                $class = implode('_', $parts);

                if(!isset($map[$class]))
                {
                    try{
                        if(class_exists($class)){
                            $this->map[$class] = $item;
                        }else{
                            $class = str_replace('_','\\', $class);
                            if(!isset($map[$class]) && class_exists($class)){
                                $this->map[$class] = $item;
                            }
                        }
                    }catch (Error $e){
                        echo $e->getMessage()."\n";
                    }
                }
            }
            else
            {
                $this->findClasses($item , $exceptPath);
            }
        }
    }
    /**
     * Find PHP Classes
     * @param string $path
     * @param string $exceptPath
     * @param string $baseSpace
     * @throws Exception
     */
    protected function findPsr4Classes(string $path , string $exceptPath, string $baseSpace)
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
                $class = $baseSpace.'\\'.implode('\\', $parts);

                if(!isset($map[$class]))
                    $this->map[$class] = $item;
            }
            else
            {
                $this->findPsr4Classes($item , $exceptPath, $baseSpace);
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