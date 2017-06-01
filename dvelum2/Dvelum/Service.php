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

use Dvelum\Orm\Exception;

class Service
{
    static protected $services = [];

    static public function register(string $name, callable $handler)
    {
        self::$services[$name] = [
            'handler' => $handler,
            'instance' => null
        ];
    }

    static public function get(string $name)
    {
        if (!isset(self::$services[$name])) {
            throw new Exception('Undefined service ' . $name);
        }

        if (empty(self::$services[$name]['instance'])) {
            $service = self::$services[$name]['handler'];
            $instance = $service();
            self::$services[$name]['instance'] = $instance;
        }
        return self::$services[$name]['instance'];
    }
}