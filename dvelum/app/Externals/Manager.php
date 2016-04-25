<?php

class Externals_Manager
{
    /**
     * @var Config_Abstract
     */
    protected $appConfig;

    /**
     * @var Config_File_Array
     */
    protected $config;
    /**
     * @var Autoloader
     */
    protected $autoloader;

    protected $errors = [];

    static protected $defaultConfig = [];

    /**
     * Set manager configuration
     * @param array $config
     */
    static public function setConfig(array $config)
    {
        static::$defaultConfig = $config;
    }

    static public function factory()
    {
        static $manager = false;
        if(!$manager){
            $manager = new static(static::$defaultConfig['appConfig'], static::$defaultConfig['autoloader']);
        }
        return $manager;
    }

    private function __construct(Config_Abstract $config, Autoloader $autoloader)
    {
        $this->config = Config::storage()->get('external_modules.php');
        $this->autoloader = $autoloader;
        $this->appConfig = $config;
    }

    /**
     * Load external modules configuration
     * @return void
     */
    public function loadModules()
    {
        $modules = $this->config->__toArray();

        if(empty($modules)){
            return;
        }

        $autoLoadPaths = [];
        $configPaths = [];
        $langPaths = [];

        foreach($modules as $index => $config)
        {
            if(!$config['enabled']){
                continue;
            }

            $path = $config['path'];
            $modCfg = require $path.'config.php';

            if(!empty($modCfg['autoloader'])){
                foreach($modCfg['autoloader'] as $classPath){
                    $autoLoadPaths[] = str_replace('./', $path, $classPath);
                }
            }

            if(!empty($modCfg['locales'])){
                $langPaths[] = str_replace(['./','//'], [$path,''], $modCfg['locales'].'/');
            }

            if(!empty($modCfg['configs'])){
                $configPaths[] = str_replace(['./','//'], [$path,''], $modCfg['configs'].'/');
            }

        }
        // Add autoloader paths
        if(!empty($autoLoadPaths)){
            $autoloaderCfg = $this->appConfig->get('autoloader');

            foreach($autoLoadPaths as $path){
                $this->autoloader->registerPath($path, true);
                array_unshift($autoloaderCfg['paths'],$path);
            }
            // update main configuration
            $this->appConfig->set('autoloader',$autoloaderCfg);
        }
        // Add Config paths
        if(!empty($configPaths)){
            $storage = Config::storage();
            $storePaths = $storage->getPaths();
            foreach($configPaths as $path){
                $storage->addPath($path);
            }
        }

        // Add localization paths
        if(!empty($langPaths)){
            $langStorage = Lang::storage();
            $storePaths = $langStorage->getPaths();
            $storePaths = $langStorage->getPaths();
            foreach($langPaths as $path){
                $langStorage->addPath($path);
            }
        }
    }

    /**
     * Check for external modules
     * @return bool
     * @throws Exception
     */
    public function hasModules()
    {
        return boolval($this->config->getCount());
    }

    /**
     * Get modules info
     * @return array
     */
    public function getModules()
    {
        $list = $this->config->__toArray();
        $result = [];

        foreach($list as $code=>$config) {
            $path = $config['path'];
            $mod = require $path.'config.php';
            $mod['enabled'] = $config['enabled'];
            $mod['installed'] = $config['installed'];
            $result[] = $mod;
        }

        return $result;
    }

    public function getModule($id)
    {
        $modInfo = $this->config->get($id);

        $path = $modInfo['path'];
        $mod = require $path.'config.php';

        $data = array_merge($modInfo, $mod);
        return $data;
    }

    /**
     * Check if module exists
     * @param $id
     * @return boolean
     */
    public function moduleExists($id)
    {
        return $this->config->offsetExists($id);
    }

    /**
     * Install module, copy resources
     * @param $id
     * @return boolean
     */
    public function install($id)
    {
        $externalsCfg = $this->appConfig->get('externals');

        $modInfo = $this->getModule($id);

        if(!empty($modInfo['resources']))
        {
            $resources = str_replace(['./','//'], [$modInfo['path'],''], $modInfo['resources'].'/');

            if(is_dir($resources)){
                if(!File::copyDir($resources, $externalsCfg['resources_path'].$id)){
                    $this->errors[] = Lang::lang()->get('CANT_WRITE_FS').' '.$externalsCfg['resources_path'].$id;
                    return false;
                }
            }
        }

        $modConf = $this->config->get($id);
        $modConf['installed'] = true;
        $this->config->set($id , $modConf);

        if(!$this->config->save()){
            $this->errors[] = Lang::lang()->get('CANT_WRITE_FS').' '.$this->config->getWritePath();
            return false;
        }

        return true;
    }

    /**
     * Uninstall module remove resources
     * @param $id
     * @return boolean
     */
    public function uninstall($id)
    {

    }

    /**
     * Set enabled status
     * @param $id
     * @param bool $flag
     * @return boolean
     */
    public function setEnabled($id, $flag = true)
    {
        $modConf = $this->config->get($id);
        $modConf['enabled'] = $flag;
        $this->config->set($id , $modConf);

        if(!$this->config->save()){
            $this->errors[] = Lang::lang()->get('CANT_WRITE_FS').' '.$this->config->getWritePath();
            return false;
        }
        return true;
    }

    /**
     * Get errors list
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}