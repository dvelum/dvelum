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

namespace  Dvelum\Db;

use Dvelum\Config;

class Manager implements \Db_Manager_Interface
{
    protected $dbConnections = [];
    protected $dbConfigs = [];

    /**
     * @var Config\Adapter
     */
    protected $_appConfig;

    /**
     * @param Config\Adapter $appConfig - Application config (main)
     */
    public function __construct(Config\Adapter $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     * Get Database connection
     * @param string $name
     * @throws \Exception
     * @return Adapter
     */
    public function getDbConnection(string $name) : Adapter
    {
        $workMode = $this->appConfig->get('development');
        if(!isset($this->dbConnections[$workMode][$name]))
        {
            $cfg = $this->getDbConfig($name);
            $cfg->set('driver', $cfg->get('adapter'));
            /*
             * Enable Db profiler for development mode Attention! Db Profiler causes
             * memory leaks at background tasks. (Dev mode)
             */
            if($this->appConfig->get('development')){
                $cfg->set('profiler' , true);
            }

            $db = new Adapter($cfg->__toArray());

            if($this->appConfig->get('development')){
                \Debug::addDbProfiler($db->getProfiler());
            }

            $this->dbConnections[$workMode][$name] = $db;
        }
        return $this->dbConnections[$workMode][$name];
    }
    /**
     * Get Db Connection config
     * @param string $name
     * @throws \Exception
     * @return Config\Adapter
     */
    public function getDbConfig(string $name) : Config\Adapter
    {
        $workMode = $this->appConfig->get('development');

        if($workMode == \Dvelum\App\Application::MODE_INSTALL)
            $workMode = \Dvelum\App\Application::MODE_DEVELOPMENT;

        if(!isset($this->dbConfigs[$workMode][$name]))
        {
            $dbConfigPaths = $this->appConfig->get('db_configs');

            if(!isset($dbConfigPaths[$workMode]))
                throw new \Exception('Invalid application work mode ' . $workMode);

            $this->dbConfigs[$workMode][$name] = Config\Factory::storage()->get($dbConfigPaths[$workMode]['dir'].$name.'.php' , true , false);
        }

        return $this->dbConfigs[$workMode][$name];
    }
}