<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Externals;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Orm;
use Dvelum\Autoload;
use Dvelum\File;
use Dvelum\Lang;
use Dvelum\Template\Storage;
use \Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Manager
 * @package Dvelum\Externals
 */
class Manager
{
    /**
     * @var ConfigInterface
     */
    protected $appConfig;

    /**
     * @var array
     */
    protected $externalsConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var Autoload
     */
    protected $autoloader;

    protected $errors = [];
    /**
     * Loaded modules index
     * @var array
     */
    protected $loadedModules = [];

    private ContainerInterface $di;

    protected Lang\Dictionary $lang;
    protected Config\Storage\StorageInterface  $configStorage;

    public function __construct(ConfigInterface $config, ContainerInterface $di)
    {
        $this->configStorage = $di->get(Config\Storage\StorageInterface::class);
        $this->config = $this->configStorage->get('external_modules.php');
        $this->autoloader = $di->get(Autoload::class);
        $this->appConfig = $config;
        $this->externalsConfig = $this->appConfig->get('externals');
        $this->di = $di;
        $this->lang = $this->di->get(Lang::class)->lang();
    }

    /**
     * Get autoloader
     * @return Autoload
     */
    public function getAutoloader() : Autoload
    {
        return $this->autoloader;
    }

    /**
     * Find externals
     */
    public function scan()
    {
        $externalsCfg = $this->appConfig->get('externals');
        $path = $externalsCfg['path'];

        if (!is_dir($path)) {
            return true;
        }

        $vendors = File::scanFiles($path, false, false, File::DIRS_ONLY);

        $hasNew = false;
        if (!empty($vendors)) {
            foreach ($vendors as $vendorPath) {
                $modules = File::scanFiles($vendorPath, false, false, File::DIRS_ONLY);
                if (empty($modules)) {
                    continue;
                }

                $vendor = basename($vendorPath);
                foreach ($modules as $modulePath) {
                    if (!file_exists($modulePath . '/config.php')) {
                        continue;
                    }
                    $mConfig = include $modulePath . '/config.php';

                   // $module = basename($modulePath);
                    $moduleId = $mConfig['id'];

                    if (!$this->config->offsetExists($moduleId)) {
                        $this->config->set($moduleId, [
                            'enabled' => false,
                            'installed' => false,
                            'path' => $modulePath
                        ]);
                        $hasNew = true;
                    }
                }
            }
        }
        /*
        if ($hasNew) {
           return $this->saveConfig();
        }
        */

        return true;
    }

    /**
     * Save modules configuration
     * @throws \Exception
     * @return bool
     */
    public function saveConfig() : bool
    {
        if (!$this->configStorage->save($this->config)) {
            $writePath = $this->configStorage->getWrite();
            $this->errors[] = $this->lang->get('CANT_WRITE_FS') . ' ' . $writePath . 'external_modules.php';
            return false;
        }
        return true;
    }

    /**
     * Add external module
     * @param string $id
     * @param array|null $config
     * @throws \Exception
     * @return bool
     */
    public function add(string $id , ?array $config = null) : bool
    {
        if($this->moduleExists($id)){
            return false;
        }

        $this->config->set($id, $config);
        return $this->saveConfig();
    }

    /**
     * Load external modules configuration
     * @return void
     */
    public function loadModules()
    {
        $modules = $this->config->__toArray();

        if (empty($modules)) {
            return;
        }

        $autoLoadPaths = [];
        $autoLoadPathsPsr4 = [];
        $configPaths = [];
        $templatesPaths = [];
        $langPaths = [];

        foreach ($modules as $index => $config) {
            if (!$config['enabled'] || isset($this->loadedModules[$index])) {
                continue;
            }

            $path = File::fillEndSep($config['path']);
            if(!file_exists( $path . '/config.php')){
                continue;
            }
            $modCfg = require $path . '/config.php';

            if (!empty($modCfg['autoloader'])) {
                foreach ($modCfg['autoloader'] as $classPath) {
                    $autoLoadPaths[] = str_replace('./', $path, $classPath);
                }
            }

            if (!empty($modCfg['autoloader-psr-4'])) {
                foreach ($modCfg['autoloader-psr-4'] as $ns =>$classPath) {
                    $autoLoadPathsPsr4[$ns] = str_replace('./', $path, $classPath);
                }
            }

            if (!empty($modCfg['locales'])) {
                $langPaths[] = str_replace(['./', '//'], [$path, ''], $modCfg['locales'] . '/');
            }

            if (!empty($modCfg['configs'])) {
                $configPaths[] = str_replace(['./', '//'], [$path, ''], $modCfg['configs'] . '/');
            }

            if (!empty($modCfg['templates'])) {
                $templatesPaths[] = str_replace(['./', '//'], [$path, ''], $modCfg['templates'] . '/');
            }

            $this->loadedModules[$index] = true;
        }
        // Add autoloader paths
        if (!empty($autoLoadPaths)) {
            $autoloaderConfig =  $this->configStorage->get('autoloader.php');
            $autoloaderCfg = $autoloaderConfig->__toArray();
            $newСhain = $autoloaderCfg['priority'];

            foreach ($autoLoadPaths as $path) {
                $newСhain[] = $path;
            }

            foreach ($autoloaderCfg['paths'] as $path) {
                if (!in_array($path, $newСhain, true)) {
                    $newСhain[] = $path;
                }
            }

            $autoloaderCfg['psr-4'] = array_merge($autoLoadPathsPsr4, $autoloaderCfg['psr-4']);
            $autoloaderCfg['paths'] = $newСhain;

            // update autoloader paths
            $this->autoloader->setConfig(['paths' => $autoloaderCfg['paths'], 'psr-4'=>$autoloaderCfg['psr-4']]);
            // update main configuration
            $autoloaderConfig->setData($autoloaderCfg);
        }
        // Add Config paths
        if (!empty($configPaths)) {
            $storage = $this->configStorage;

            $resultPaths = $storage->get('config_storage.php')->get('file_array')['locked_paths'];
            $lockedPathsIndex = array_flip($resultPaths);

            $paths = $storage->getPaths();

            foreach ($configPaths as $path) {
                $resultPaths[] = $path;
            }

            foreach ($paths as $path){
                if(!isset($lockedPathsIndex[$path])){
                    $resultPaths[] = $path;
                }
            }

            $storage->replacePaths($resultPaths);
        }
        // Add localization paths
        if (!empty($langPaths)) {
            $langStorage = $this->di->get(Lang::class)->getStorage();
            foreach ($langPaths as $path) {
                $langStorage->addPath($path);
            }
        }
        // Add Templates paths
        if (!empty($templatesPaths)) {
            /**
             * @var Storage $templateStorage
             */
            $templateStorage = $this->di->get(Storage::class);
            $paths = $templateStorage->getPaths();
            $mainPath = array_shift($paths);
            // main path
            $pathsResult = [];
            $pathsResult[] = $mainPath;
            $pathsResult = array_merge($pathsResult, $templatesPaths, $paths);

            $templateStorage->setPaths($pathsResult);
        }
    }

    /**
     * Check for external modules
     * @return bool
     * @throws \Exception
     */
    public function hasModules() : bool
    {
        return (bool) $this->config->getCount();
    }

    /**
     * Get modules info
     * @return array
     */
    public function getModules() : array
    {
        $list = $this->config->__toArray();
        $result = [];

        foreach ($list as $code => $config) {
            $path = $config['path'];
            if(!file_exists($path . '/config.php')){
                continue;
            }
            $mod = require $path . '/config.php';
            $mod['enabled'] = $config['enabled'];
            $mod['installed'] = $config['installed'];
            $result[] = $mod;
        }

        return $result;
    }
    public function getComposerPackageName(string $id) : ?string
    {
        $modInfo = $this->config->get($id);

        $path = $modInfo['path'];
        $composerFile = $path . '/composer.json';

        if(!file_exists($composerFile)){
            return null;
        }
        $data = json_decode(file_get_contents($composerFile,), true);
        return  $data['name'];

    }
    public function getModule($id)
    {
        $modInfo = $this->config->get($id);

        $path = $modInfo['path'];
        $mod = require $path . '/config.php';

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
     * @return bool
     */
    public function install($id)
    {
        $modInfo = $this->getModule($id);
        $path = File::fillEndSep($modInfo['path']);
        if (!empty($modInfo['resources'])) {
            $resources = str_replace(['./', '//'], [$path, ''], $modInfo['resources'] . '/');

            if (is_dir($resources)) {
                if (!File::copyDir($resources, $this->externalsConfig['resources_path'] . $id)) {
                    $this->errors[] = $this->lang->get('CANT_WRITE_FS') . ' ' . $this->externalsConfig['resources_path'] . $id;
                    return false;
                }
            }
        }

        $modConf = $this->config->get($id);
        $modConf['installed'] = true;
        $modConf['enabled'] = true;

        $this->config->set($id, $modConf);

        if (!Config::storage()->save($this->config)) {
            $this->errors[] = $this->lang->get('CANT_WRITE_FS') . ' ' . $this->configStorage->getWrite();
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
        if (!empty($modConf['objects'])) {

            $builders = [];
            foreach ($modConf['objects'] as $object) {
                try {
                    $objectCfg = Orm\Record\Config::factory($object);
                    if (!$objectCfg->isLocked() && !$objectCfg->isReadOnly()) {
                        $builder = Orm\Record\Builder::factory($object);
                        $builders[] = $builder;
                        if (!$builder->build(false)) {
                            $errors = $builder->getErrors();
                            if (!empty($errors) && is_array($errors)) {
                                $this->errors[] = implode(', ', $errors);
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
            }

            if (!empty($this->errors)) {
                return false;
            }

            foreach($builders as $builder)
            {
                try {
                    /**
                     * @var Orm\Record\Builder\BuilderInterface $builder
                     */
                    if (!$builder->buildForeignKeys()) {
                        $errors = $builder->getErrors();
                        if (!empty($errors) && is_array($errors)) {
                            $this->errors[] = implode(', ', $errors);
                        }
                    }
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
            }

            if (!empty($this->errors)) {
                return false;
            }
        }

        if (!empty($modConf['post-install'])) {
            $class = $modConf['post-install'];

            if (!class_exists($class)) {
                $this->errors[] = $class . ' class not found';
                return false;
            }

            $installer = new $class;

            if (!$installer instanceof Installer) {
                $this->errors[] = 'Class ' . $class . ' is not instance of Installer';
            }

            $modConfig = Config\Factory::create($modConf, $modConf['id'] . '_config');

            if (!$installer->install($this->appConfig, $modConfig)) {
                $errors = $installer->getErrors();
                if (!empty($errors) && is_array($errors)) {
                    $this->errors[] = implode(', ', $errors);
                    return false;
                }
            }
        }
        // build JS lang
        $langManager = new \Dvelum\App\Backend\Localization\Manager($this->appConfig);
        try {
            $langManager->compileLangFiles();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Uninstall module remove resources
     * @param $id
     * @return bool
     */
    public function uninstall($id) : bool
    {
        $modConf = $this->getModule($id);

        // Remove config record
        $this->config->remove($id);
        $storage = Config::storage();
        if (!$storage->save($this->config)) {
            $this->errors[] = Lang::lang()->get('CANT_WRITE_FS') . ' ' . $storage->getWrite();
            return false;
        }

        // Remove resources
        if (!empty($modConf['resources'])) {
            $installedResources = $this->externalsConfig['resources_path'] . $id;

            if (is_dir($installedResources)) {
                if (!File::rmdirRecursive($installedResources, true)) {
                    $this->errors[] = Lang::lang()->get('CANT_WRITE_FS') . ' ' . $installedResources;
                    return false;
                }
            }
        }
        // Remove Db_Object tables
        if (!empty($modConf['objects']) && $modConf['enabled']) {
            foreach ($modConf['objects'] as $object) {
                try {
                    $objectCfg = Orm\Record\Config::factory($object);
                    if (!$objectCfg->isLocked() && !$objectCfg->isReadOnly()) {
                        $builder = Orm\Record\Builder::factory($object);
                        if (!$builder->remove()) {
                            $this->errors[] = $builder->getErrors();
                        }
                    }
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
            }
        }

        if (!empty($modConf['post-install']) && $modConf['enabled']) {
            $class = $modConf['post-install'];

            if (!class_exists($class)) {
                $this->errors[] = $class . ' class not found';
                return false;
            }

            $installer = new $class;

            if (!$installer instanceof Installer) {
                $this->errors[] = 'Class ' . $class . ' is not instance of Externals_Installer';
            }

            $modConfig = \Dvelum\Config\Factory::create($modConf,$modConf['id'] . '_config');

            if (!$installer->uninstall($this->appConfig, $modConfig)) {
                $errors = $installer->getErrors();
                if (!empty($errors) && is_array($errors)) {
                    $this->errors[] = implode(', ', $errors);
                    return false;
                }
            }
        }

        // Remove module src
        if (is_dir($modConf['path'])) {
            if (!File::rmdirRecursive($modConf['path'], true)) {
                $this->errors[] = Lang::lang()->get('CANT_WRITE_FS') . ' ' . $modConf['path'];
                return false;
            }
        }

        if (empty($this->errors)) {
            return true;
        } else {
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
        $this->config->set($id, $modConf);

        $storage = Config::storage();
        if (!$storage->save($this->config)) {
            $this->errors[] = Lang::lang()->get('CANT_WRITE_FS') . ' ' . $storage->getWrite();
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

    /**
     * Get default repo
     * @return string
     */
    public function getComposerRepo() : string
    {
        return $this->externalsConfig['composer_repo'];
    }

    /**
     * Check if module directory exists and extract module info
     * from module config file
     * @param string $vendor
     * @param string $module
     * @return array|null
     * @throws \Exception
     */
    public function detectModuleInfo(string $vendor, string $module): ?array
    {
        $moduleDir = $this->getModulePath($vendor, $module);
        if(!is_dir($moduleDir)){
            return null;
        }

        if(!file_exists($moduleDir.'/config.php')){
            return null;
        }

        $moduleInfo = include $moduleDir . '/config.php';
        if(!is_array($moduleInfo) || empty($moduleInfo)){
            return null;
        }
        return $moduleInfo;
    }

    /**
     * Get module path by vendor and module name
     * @param string $vendor
     * @param string $module
     * @return string
     * @throws \Exception
     */
    public function getModulePath(string $vendor, string $module) : string
    {
        $externalsCfg = $this->appConfig->get('externals');
        return $externalsCfg['path'] . '/' . $vendor . '/' . $module;
    }
}