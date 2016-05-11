<?php

class Externals_Manager
{
    /**
     * @var Config_Abstract
     */
    protected $appConfig;

    /**
     * @var array
     */
    protected $externalsConfig;

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
        $this->externalsConfig = $this->appConfig->get('externals');
    }

    /**
     * Find externals
     */
    public function scan()
    {
        $externalsCfg = $this->appConfig->get('externals');
        $path = $externalsCfg['path'];

        if(!is_dir($path)){
            return true;
        }

        $vendors =  File::scanFiles($path, false, false, File::Dirs_Only);

        $hasNew = false;
        if(!empty($vendors))
        {
            foreach($vendors as $vendorPath)
            {
                $modules = File::scanFiles($vendorPath, false, false, File::Dirs_Only);
                if(empty($modules)){
                    continue;
                }

                $vendor = basename($vendorPath);
                foreach($modules as $modulePath)
                {
                    if(!file_exists($modulePath.'/config.php')){
                        continue;
                    }

                    $module = basename($modulePath);
                    $moduleId = strtolower($vendor.'_'.$module);
                    if(!$this->config->offsetExists($moduleId)){
                        $this->config->set($moduleId,[
                            'enabled'=> false,
                            'installed' =>false,
                            'path'=>$modulePath
                        ]);
                        $hasNew = true;
                    }
                }
            }
        }

        if($hasNew){
            if(!$this->config->save()){
                $writePath = Config::storage()->getWrite();
                $this->errors[] = Lang::lang()->get('CANT_WRITE_FS').' '.$writePath.'external_modules.php';
                return false;
            };
        }

        return true;
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

            $path = File::fillEndSep($config['path']);
            $modCfg = require $path.'/config.php';

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

            if(!empty($modCfg['templates'])){
                $configPaths[] = str_replace(['./','//'], [$path,''], $modCfg['templates'].'/');
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
            foreach($configPaths as $path){
                $storage->addPath($path);
            }
        }
        // Add localization paths
        if(!empty($langPaths)){
            $langStorage = Lang::storage();
            foreach($langPaths as $path){
                $langStorage->addPath($path);
            }
        }
        // Add Templates paths
        if(!empty($configPaths)){
            $templateStorage = Template::storage();
            foreach($configPaths as $path){
                $templateStorage->addPath($path);
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
            $mod = require $path.'/config.php';
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
        $mod = require $path.'/config.php';

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
        $modInfo = $this->getModule($id);
        $path = File::fillEndSep($modInfo['path']);
        if(!empty($modInfo['resources']))
        {
            $resources = str_replace(['./','//'], [$path,''], $modInfo['resources'].'/');

            if(is_dir($resources)){
                if(!File::copyDir($resources, $this->externalsConfig['resources_path'].$id)){
                    $this->errors[] = Lang::lang()->get('CANT_WRITE_FS').' '.$this->externalsConfig['resources_path'].$id;
                    return false;
                }
            }
        }

        $modConf = $this->config->get($id);
        $modConf['installed'] = true;
        $modConf['enabled'] = true;

        $this->config->set($id , $modConf);

        if(!$this->config->save()){
            $this->errors[] = Lang::lang()->get('CANT_WRITE_FS').' '.$this->config->getWritePath();
            return false;
        }

        return true;
    }

    /**
     * Do post-install module action
     * @param $id
     * @return boolean
     */
    public function postInstall($id)
    {
        $modConf = $this->getModule($id);

        // build objects
        if(!empty($modConf['objects']))
        {
            foreach($modConf['objects'] as $object)
            {
                try{
                    $objectCfg = Db_Object_Config::getInstance($object);
                    if(!$objectCfg->isLocked() && !$objectCfg->isReadOnly()){
                        $builder = new Db_Object_Builder($object);
                        if(!$builder->build()){
                            $errors = $builder->getErrors();
                            if(!empty($errors) && is_array($errors)){
                                $this->errors[] = implode(', ', $errors);
                            }
                        }
                    }
                }catch (Exception $e){
                    $this->errors[] = $e->getMessage();
                }
            }

            if(!empty($this->errors)){
                return false;
            }
        }

        if(isset($modConf['post-install']))
        {
            $class = $modConf['post-install'];

            if(!class_exists($class)){
                $this->errors[] = $class . ' class not found';
                return false;
            }

            $installer = new $class;

            if(!$installer instanceof Externals_Installer){
                $this->errors[] = 'Class ' .  $class . ' is not instance of Externals_Installer';
            }

            $modConfig = new Config_Simple($modConf['id'].'_config');
            $modConfig->setData($modConf);

            if(!$installer->install($this->appConfig, $modConfig)){
                $errors = $installer->getErrors();
                if(!empty($errors) && is_array($errors)){
                    $this->errors[] = implode(', ', $errors);
                    return false;
                }
            }
        }
        // build JS lang
        $langManager = new Backend_Localization_Manager($this->appConfig);
        try{
            $langManager->compileLangFiles();
        }catch (Exception $e){
            $this->errors[] = $e->getMessage();
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
        $modConf = $this->getModule($id);

        // Remove config record
        $this->config->remove($id);
        if(!$this->config->save()){
            $this->errors[] = Lang::lang()->get('CANT_WRITE_FS').' '.$this->config->getWritePath();
            return false;
        }

        // Remove resources
        if(!empty($modConf['resources']))
        {
            $installedResources = $this->externalsConfig['resources_path'].$id;

            if(is_dir($installedResources)){
                if(!File::rmdirRecursive($installedResources, true)){
                    $this->errors[] = Lang::lang()->get('CANT_WRITE_FS').' '.$installedResources;
                    return false;
                }
            }
        }
        // Remove Db_Object tables
        if(!empty($modConf['objects']) && $modConf['enabled'])
        {
            foreach($modConf['objects'] as $object)
            {
                try{
                    $objectCfg = Db_Object_Config::getInstance($object);
                    if(!$objectCfg->isLocked() && !$objectCfg->isReadOnly()){
                        $builder = new Db_Object_Builder($object);
                        if(!$builder->remove()){
                            $this->errors[] = $builder->getErrors();
                        }
                    }
                }catch (Exception $e){
                    $this->errors[] = $e->getMessage();
                }
            }
        }

        if(isset($modConf['post-install']) && $modConf['enabled'])
        {
            $class = $modConf['post-install'];

            if(!class_exists($class)){
                $this->errors[] = $class . ' class not found';
                return false;
            }

            $installer = new $class;

            if(!$installer instanceof Externals_Installer){
                $this->errors[] = 'Class ' .  $class . ' is not instance of Externals_Installer';
            }

            $modConfig = new Config_Simple($modConf['id'].'_config');
            $modConfig->setData($modConf);

            if(!$installer->uninstall($this->appConfig, $modConfig)){
                $errors = $installer->getErrors();
                if(!empty($errors) && is_array($errors)){
                    $this->errors[] = implode(', ', $errors);
                    return false;
                }
            }
        }

        // Remove module src
        if(is_dir($modConf['path'])){
            if(!File::rmdirRecursive($modConf['path'], true)){
                $this->errors[] = Lang::lang()->get('CANT_WRITE_FS') .' ' . $modConf['path'];
                return false;
            }
        }

        if(empty($this->errors)){
            return true;
        }else{
            return false;
        }
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

    /**
     * Get list of software repositories
     * @return array
     */
    public function getRepoList()
    {
        return $this->externalsConfig['repo'];
    }
}