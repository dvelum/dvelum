<?php
/**
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
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
declare(strict_types=1);

namespace Dvelum\App\Module;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Service;
use Dvelum\Lang;
use Dvelum\File;
use Dvelum\Utils;

class Manager
{
    protected $config;
    protected $distConfig;
    protected $curConfig = false;
    protected $mainConfigKey = 'backend_modules';
    /**
     * @var ConfigInterface
     */
    protected $appConfig;
    /**
     * @var ConfigInterface
     */
    protected $modulesLocale;
    /**
     * @var Config\Storage\StorageInterface
     */
    protected $localeStorage;

    static protected $classRoutes = false;

    public function __construct()
    {
        $this->appConfig = Config::storage()->get('main.php');
        $configPath = $this->appConfig->get($this->mainConfigKey);
        $this->config = Config\Factory::storage()->get($configPath, false, true);

        $this->distConfig = new Config\File\AsArray(Config::storage()->getApplyTo() . $configPath);

        if(file_exists(Config::storage()->getWrite() . $configPath)){
            $this->curConfig = new Config\File\AsArray(Config::storage()->getWrite() . $configPath);
        }

        $locale = Lang::lang()->getName();
        $this->localeStorage = Lang::storage();
        $this->modulesLocale = $this->localeStorage->get($locale.'/modules/'.basename($configPath));
    }

    /**
     * Get Modules menu localization
     * @param $lang
     * @return ConfigInterface|false
     * @throws \Exception
     */
    public function getLocale($lang) : ConfigInterface
    {
        $configPath = $this->appConfig->get($this->mainConfigKey);
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        return $langService->getStorage()->get($lang.'/modules/'.basename($configPath));
    }

    /**
     * Get registered modules
     * @return array
     */
    public function getRegisteredModules() : array
    {
        $data = $this->config->__toArray();
        return array_keys($data);
    }

    /**
     * Check if module exists
     * @param string $name
     * @return bool
     */
    public function isValidModule($name) : bool
    {
        return $this->config->offsetExists($name);
    }

    /**
     * Get module configuration
     * @param string $name
     * @return array
     */
    public function getModuleConfig($name) : array
    {
        $data = $this->config->get($name);
        $data['title'] = $name;

        if(isset($this->modulesLocale[$name]))
            $data['title'] = $this->modulesLocale[$name];

        return $data;
    }

    /**
     * Get Module class
     * @param $name
     * @return null|string
     */
    public function getModuleController($name) :?string
    {
        if(!$this->isValidModule($name))
            return null;

        $cfg = $this->config->get($name);
        if(isset($cfg['class']) && !empty($cfg['class']))
            return $cfg['class'];
        else
            return null;
    }

    /**
     * Get module name for class
     * @param string $class
     * @return string
     */
    public function getModuleName($class) : string
    {
        $class = str_replace(['\\','Backend_','_Controller','_App_','App_'], ['_','','','_',''], $class);
        $class = trim(strtolower($class),'_');
        return \Utils_String::formatClassName($class);
    }

    /**
     * Get module name for controller
     * @param string $controller
     * @return null | string
     */
    public function getControllerModule($controller): ?string
    {
        if(!self::$classRoutes){
            $config = $this->config->__toArray();

            foreach ($config as $module=>$cfg)
                self::$classRoutes[$cfg['class']] = $module;
        }

        if(!isset(self::$classRoutes[$controller]))
            return null;
        else
            return self::$classRoutes[$controller];
    }

    /**
     * Get modules list
     * @return array
     */
    public function getList() : array
    {
        $data = $this->config->__toArray();
        foreach ($data as $module=>&$cfg)
        {
            if($this->curConfig && $this->curConfig->offsetExists($module)){
                $cfg['dist'] = false;
            }else{
                $cfg['dist'] = true;
            }

            if(!isset($cfg['in_menu']))
                $cfg['in_menu'] = true;

            if($this->modulesLocale->offsetExists($module)){
                $cfg['title'] = $this->modulesLocale->get($module);
            }else{
                $cfg['title'] = $module;
            }

        }unset($cfg);
        return $data;
    }

    /**
     * Remove module
     * @param string $name
     * @return bool
     */
    public function removeModule(string $name) : bool
    {
        if($this->config->offsetExists($name))
            $this->config->remove($name);

        if($this->modulesLocale->offsetExists($name))
            $this->modulesLocale->remove($name);

        if(!$this->localeStorage->save($this->modulesLocale))
            return false;

        return $this->save();
    }

    /**
     * Add module
     * @param string $name
     * @param array $config
     * @return bool
     */
    public function addModule(string $name , array $config) : bool
    {
        $this->config->set($name , $config);
        $this->resetCache();
        $ret = $this->save();
        if($ret && isset($config['title'])){
            $this->modulesLocale->set($config['id'], $config['title']);
            if(!$this->localeStorage->save($this->modulesLocale)){
                return false;
            }
        }
        return $ret;
    }

    /**
     * Update module data
     * @param string $name
     * @param array $data
     * @return boolean
     */
    public function updateModule(string $name , array $data) : bool
    {
        if($name !== $data['id']){
            $this->modulesLocale->remove($name);
            $this->config->remove($name);
        }

        if(isset($data['title']))
        {
            $this->modulesLocale->set($data['id'] , $data['title']);
            if(!$this->localeStorage->save($this->modulesLocale)){
                return false;
            }
            unset($data['title']);
        }
        $this->config->set($data['id'] , $data);
        return $this->save();
    }

    /**
     * Save modules config
     * @return bool
     */
    public function save() : bool
    {
        $this->resetCache();
        return Config::storage()->save($this->config);
    }
    /**
     * Reset modules cache
     */
    public function resetCache() : void
    {
        self::$classRoutes = false;
    }
    /**
     * Get configuration object
     * @return ConfigInterface
     */
    public function getConfig() : ConfigInterface
    {
        return $this->config;
    }

    /**
     * Get list of Controllers
     * @return array
     */
    public function getControllers() : array
    {
        $backendConfig = Config::storage()->get('backend.php');
        $autoloadCfg = Config::storage()->get('autoloader.php');
        $systemControllers = $backendConfig->get('system_controllers');

        $paths = $autoloadCfg['paths'];

        $dirs = $this->appConfig->get('backend_controllers_dirs');

        $data = [];

        foreach($paths as $path)
        {
            if(basename($path) === 'modules')
            {
                $folders = File::scanFiles($path, false, true, File::Dirs_Only);

                if(empty($folders))
                    continue;

                foreach($folders as $item)
                {
                    foreach ($dirs as $dir){
                        if(!is_dir($item.'/'.$dir)){
                            continue;
                        }
                        $prefix = str_replace('/','_',ucfirst(basename($item)).'_'.$dir.'_');
                        $this->findControllers($item.'/'.$dir, $systemControllers, $data , $prefix);
                    }
                }
            }else{

                foreach ($dirs as $dir) {
                    if (!is_dir($path . '/' . $dir)) {
                        continue;
                    }
                    $prefix = str_replace('/','_', $dir . '_');
                    $this->findControllers($path . '/' . $dir, $systemControllers, $data, $prefix);
                }
            }
        }
        return array_values($data);
    }

    /**
     * Find controller files
     * @param $path
     * @param array $skipList
     * @param & array $result
     * @param string $classPrefix
     * @return void
     * @throws \Exception
     */
    public function findControllers(string $path, $skipList, & $result, string $classPrefix = '') : void
    {
        $folders = File::scanFiles($path, false, true, File::Dirs_Only);

        if(empty($folders))
            return;

        foreach ($folders as $item)
        {
            $name = basename($item);

            if(file_exists($item.'/Controller.php'))
            {
                $name = str_replace($path.'/', '', $item.'/Controller.php');
                $name = $classPrefix . Utils::classFromPath($name);
                $namespaceName = '\\'.str_replace('_','\\',$name);
                /*
                 * Skip system controller
                 */
                if(in_array($name, $skipList , true) || in_array($namespaceName, $skipList , true))
                    continue;

                if(class_exists($name)){
                    $result[$name] = ['id'=>$name,'title'=>$name];
                }elseif(class_exists($namespaceName)){
                    $result[$namespaceName] = ['id'=>$namespaceName,'title'=>$namespaceName];
                }
            }
            $this->findControllers($item, $skipList, $result, $classPrefix);
        }
    }

    /**
     * Get list of controllers without modules
     * @return array
     */
    public function getAvailableControllers() : array
    {
        $list = $this->getControllers();

        /*
         * Hide registered controllers
         */
        $registered = array_flip(Utils::fetchCol('class' , $this->getConfig()->__toArray()));
        foreach($list as $k=>$v){
            if(isset($registered[$v['id']])){
                unset($list[$k]);
            }
        }
        return array_values($list);
    }

    /**
     * Check if module uses version control
     * @name string - module name
     * @return bool
     * @todo refactor
     */
    public function isVcModule(string $name) : bool
    {
        $class = $this->getModuleController($name);

        if(empty($class) || !class_exists($class)) {
            return false;
        }

        $reflector = new \ReflectionClass($class);

        if($reflector->isSubclassOf('Backend_Controller_Crud_Vc'))
            return true;
        else
            return false;
    }
}