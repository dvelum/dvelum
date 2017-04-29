<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net Copyright
 * (C) 2011-2012 Kirill A Egorov This program is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version. This program is distributed
 * in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. You should have received
 * a copy of the GNU General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum;

/**
 * PSR-0 / PSR-4 Autoload class
 *
 * @author Kirill Egorov
 */
class Autoload
{
    protected $debug = false;
    protected $debugData = [];
    protected $classMap = [];
    protected $paths = [];
    protected $psr4Paths = [];

    protected $loaders = ['psr0','psr4'];

    /**
     * Set autoload config
     *
     * @param array $config
     *          Example:
     *          array(
     *          // Debug mode
     *          'debug' => boolean
     *          // Paths for autoloading
     *          'paths'=> array(...),
     *          // Class map
     *          'map' => array(...),
     *          'psr-4' => [prefix=>path]
     *          );
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
        spl_autoload_register([$this , 'load']);
    }

    /**
     * Reload configuration options
     * @param array $config
     */
    public function setConfig(array $config)
    {
        if(isset($config['paths']))
            $this->paths = array_values($config['paths']);

        if(isset($config['map']) && ! empty($config['map']))
            $this->classMap = $config['map'];

        if(isset($config['debug']) && $config['debug'])
            $this->debug = true;

        if(isset($config['psr-4']) && !empty($config['psr-4']))
            $this->psr4Paths = $config['psr-4'];
    }

    /**
     * Register library path
     * @param string $path
     * @param boolean $prepend, optional false  - priority
     * @return void
     */
    public function registerPath($path, $prepend = false)
    {
        if($prepend){
            array_unshift($this->paths, $path);
        }else{
            $this->paths[] = $path;
        }
    }

    /**
     * Register library paths
     * @param array $paths
     */
    public function registerPaths(array $paths)
    {
        if(empty($paths))
            return;

        foreach($paths as $path)
            $this->registerPath($path);
    }

    /**
     * Load class
     * @param string $class
     * @return boolean
     */
    public function load(string $class) : bool
    {
        /*
         * Try to load from map
         */
        if(!empty($this->classMap) && isset($this->classMap[$class]))
        {
            require_once $this->classMap[$class];

            if($this->debug){
                $this->debugData[] = $class;
            }
            return true;
        }else{
         //   echo $class."<br>";
        }

        foreach ($this->loaders as $loader)
        {
            if($this->{$loader}($class))
            {
                if($this->debug){
                    $this->debugData[] = $class;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * PSR-0 autoload
     * @param $class
     * @return bool
     */
    public function psr0(string $class) : bool
    {
        $file = str_replace(['_','\\'] , DIRECTORY_SEPARATOR , $class). '.php';

        foreach($this->paths as $path)
        {
            if(file_exists($path . DIRECTORY_SEPARATOR . $file)) {
                require_once $path . DIRECTORY_SEPARATOR . $file;
                return true;
            }
        }
        return false;
    }

    /**
     * PSR-4 autoload
     * @param string $class
     * @return bool
     */
    public function psr4(string $class) : bool
    {
        foreach ($this->psr4Paths as $prefix => $path)
        {
            if(strpos($class , $prefix) ===0)
            {
                $filePath = str_replace([$prefix,'\\'], [$path,'/'], $class).'.php';
                if(file_exists($filePath))
                {
                    require_once $filePath;
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Load class map
     * @property array $path
     * @return array
     */
    public function setMap(array $data)
    {
        $this->classMap = $data;
    }

    /**
     * Add class map
     * @param array $data
     */
    public function addMap(array $data)
    {
        foreach($data as $k => $v)
            $this->classMap[$k] = $v;
    }

    /**
     * Debug function.
     * Shows loaded class files
     * @return array
     */
    public function getLoadedClasses() : array
    {
        return $this->debugData;
    }
}