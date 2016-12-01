<?php
/*
 * DVelum project http://code.google.com/p/dvelum/, https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2015  Kirill A Egorov
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

use Dvelum\Config;

class Db_Manager implements Db_Manager_Interface
{
    protected $_dbConnections = array();
    protected $_dbConfigs = array(); 
    
    /**
     * @var Config_Abstract
     */
    protected $_appConfig;
    
    /**
     * @param \Dvelum\Config\Config $appConfig - Application config (main)
     */
    public function __construct(Config\Config $appConfig)
    {
        $this->_appConfig = $appConfig;
    }
    
    /**
     * Get Database connection
     * @param string $name
     * @throws Exception
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbConnection($name)
    {
        $workMode = $this->_appConfig->get('development');       
        if(!isset($this->_dbConnections[$workMode][$name]))
        {
           $cfg = $this->getDbConfig($name);
           $db = \Zend_Db::factory($cfg->get('adapter') ,  $cfg->__toArray());
           /*
            * Enable Db profiler for development mode Attention! Db Profiler causes
            * memory leaks at background tasks. (Dev mode)
            */
            if($this->_appConfig->get('development')){
                $db->getProfiler()->setEnabled(true);
                \Debug::addDbProfiler($db->getProfiler());
            }
            $this->_dbConnections[$workMode][$name] = $db;            
        }        
        return $this->_dbConnections[$workMode][$name];
    }
    /**
     * Get Db Connection config
     * @param string $name
     * @throws Exception
     * @return Config_Abstract
     */
    public function getDbConfig($name)
    {
        $workMode = $this->_appConfig->get('development');

        if($workMode == \Application::MODE_INSTALL)
            $workMode = \Application::MODE_DEVELOPMENT;

        if(!isset($this->_dbConfigs[$workMode][$name]))
        {         
            $dbConfigPaths = $this->_appConfig->get('db_configs');
            
            if(!isset($dbConfigPaths[$workMode]))
                throw new Exception('Invalid application work mode ' . $workMode);

            $this->_dbConfigs[$workMode][$name] = Config\Factory::storage()->get($dbConfigPaths[$workMode]['dir'].$name.'.php' , true , false);
        }
        
        return $this->_dbConfigs[$workMode][$name];
    }
}