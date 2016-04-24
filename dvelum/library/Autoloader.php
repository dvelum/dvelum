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
/**
 * PSR0 Autoloader class
 *
 * @author Kirill Egorov
 */
class Autoloader
{
    protected $_debug = false;
    protected $_debugData = array();
    protected $_classMap = array();
    protected $_paths = array();

    /**
     * Set autoloader config
     *
     * @param array $config
     *          Example:
     *          array(
     *          // Debug mode
     *          'debug' => boolean
     *          // Paths for autoloading
     *          'paths'=> array(...),
     *          // Class map
     *          'map' => array(...)
     *          );
     */
    public function __construct(array $config)
    {
        if(isset($config['paths']))
            $this->_paths = array_values($config['paths']);

        if(isset($config['map']) && ! empty($config['map']))
            $this->_classMap = $config['map'];

        if(isset($config['debug']) && $config['debug'])
            $this->_debug = true;

        /*
         * Registering Autoloader
         */
        spl_autoload_register(array(
            $this ,
            'load'
        ));
    }

    /**
     * Reload configuration options
     * @param array $config
     */
    public function setConfig(array $config)
    {
        if(isset($config['paths']))
            $this->_paths = array_values($config['paths']);

        if(isset($config['map']))
            $this->_classMap = $config['map'];

        if(isset($config['debug']))
            $this->_debug = $config['debug'];
    }

    /**
     * Register library path
     *
     * @param string $path
     * @param boolean $prepend, optional false  - priority
     * @return void
     */
    public function registerPath($path, $prepend = false)
    {
        if($prepend){
            array_unshift($this->_paths, $path);
        }else{
            $this->_paths[] = $path;
        }
    }

    /**
     * Register library paths
     *
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
     *
     * @param string $class
     * @return boolean
     */
    public function load($class)
    {
        $class = str_replace('\\' , '_' , $class);

        if(!empty($this->_classMap) && isset($this->_classMap[$class]))
        {
            /*
             * Try to load from map
             */
            require_once $this->_classMap[$class];
            if($this->_debug)
                $this->_debugData[] = $class;

            return true;
        }
        /*
         * Search for class file
         */
        $file = str_replace('_' , DIRECTORY_SEPARATOR , $class). '.php';

        foreach($this->_paths as $path)
        {
            if(file_exists($path . DIRECTORY_SEPARATOR . $file))
            {
                require_once $path . DIRECTORY_SEPARATOR . $file;
                if($this->_debug)
                    $this->_debugData[] = $class;
                return true;
            }
        }
        return false;
    }

    /**
     * Load class map
     *
     * @property array $path
     * @return array
     */
    public function setMap(array $data)
    {
        $this->_classMap = $data;
    }

    /**
     * Add class map
     *
     * @param array $data
     */
    public function addMap(array $data)
    {
        foreach($data as $k => $v)
            $this->_classMap[$k] = $v;
    }

    /**
     * Debug function.
     * Shows loaded class files
     *
     * @return array
     */
    public function getLoadedClasses()
    {
        return $this->_debugData;
    }
}