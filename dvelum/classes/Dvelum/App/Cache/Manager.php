<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
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
 *
 */
declare(strict_types=1);

namespace Dvelum\App\Cache;

use Dvelum\Cache\CacheInterface;

class Manager
{
    static protected $connections = [];

    /**
     * Register cache adapter
     * @param string $name
     * @param CacheInterface $cache
     */
    public function register($name , CacheInterface $cache)
    {
        self::$connections[$name] = $cache;
    }
    
    /**
     * Get cache adapter
     * @param string $name
     * @return CacheInterface|bool
     */
    public function get(string $name)
    {
        if(!isset(self::$connections[$name]))
            return false;
        else
            return self::$connections[$name];
    }
    
    /**
     * Remove cache adapter
     * @param string $name
     */
    public function remove(string $name)
    {
        if(!isset(self::$connections[$name]))
            return;

        unset(self::$connections[$name]);
    }
    
    /**
     * Get list of registered adapters
     * @return array
     */
    public function getRegistered()
    {
        return self::$connections;
    }

    /**
     * Init Cache adapter by config
     * @param string $name
     * @param array $config
     * @return bool|CacheInterface
     */
    public function connect(string $name, array $config)
    {
        $cache = false;

        if(isset($config['enabled']) && $config['enabled'])
            $cache = new $config['backend']['name']($config['backend']['options']);

        self::$connections[$name] = $cache;

        return $cache;
    }
}