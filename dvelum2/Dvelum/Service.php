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

namespace Dvelum;

use Dvelum\App\Service\Loader\LoaderInterface;
use Dvelum\Config\ConfigInterface;
use Exception;

class Service
{
    static protected $services = [];
    /**
     * @var ConfigInterface $config
     */
    static protected $config;
    /**
     * @var ConfigInterface $config
     */
    static protected $env;

    static public function register(ConfigInterface $config, ConfigInterface $env)
    {
        self::$config = $config;
        self::$env = $env;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    static public function get(string $name)
    {
        if(!self::$config->offsetExists($name)){
            throw new Exception('Undefined service ' . $name);
        }

        if (!isset(self::$services[$name])) {
            $service = self::$config->get($name)['loader'];
            /**
             * @var LoaderInterface $instance
             */
            $instance = new $service();
            $instance->setConfig(self::$env);
            self::$services[$name] = $instance->loadService();
        }
        return self::$services[$name];
    }
}