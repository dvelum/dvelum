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

namespace Dvelum\Orm\Distributed;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Distributed;
use Dvelum\Orm\RecordInterface;
use Dvelum\Service;

class Router
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;

    /**
     * @var array
     */
    protected $routes = [];
    /**
     * @var array
     */
    protected $objectToRoute;

    static public function factory(): self
    {
        return Service::get('ShardingRouter');
    }

    public function __construct(ConfigInterface $routes)
    {
        $this->config = $routes;
        $this->init();
    }

    protected function init()
    {
        foreach ($this->config as $route) {
            if (!$route['enabled']) {
                continue;
            }
            $this->routes[$route['id']] = $route;
            if (isset($route['objects']) && !empty($route['objects'])) {
                foreach ($route['objects'] as $object) {
                    $this->objectToRoute[$object] = $route['id'];
                }
            }
        }
    }

    /**
     * Check routes for ORM object
     * @param  string $objectName
     * @return bool
     */
    public function hasRoutes(string $objectName): bool
    {
        if (!count($this->routes)) {
            return false;
        }
        if (isset($this->objectToRoute[$objectName]) && !empty($this->objectToRoute[$objectName])) {
            return true;
        }

        return false;
    }

    /**
     * Find shard for ORM\Record
     * @param RecordInterface $record
     * @return null|string
     */
    public function findShard(RecordInterface $record): ?string
    {
        $objectName = $record->getName();
        if (isset($this->objectToRoute[$objectName])) {
            $config = $this->routes[$this->objectToRoute[$objectName]];
            $adapterClass = $config['adapter'];
            /**
             * @var \Dvelum\Orm\Distributed\Router\RouteInterface $adapter
             */
            $adapterConfig = Config\Factory::create($config['config'][$objectName], 'ROUTER_' . $config['id'] .'_'. $objectName);
            $adapter = new $adapterClass(Distributed::factory(), $adapterConfig);
            return $adapter->getShard($record);
        }

        return null;
    }
}