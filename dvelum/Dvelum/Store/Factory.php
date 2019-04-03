<?php
/**
 * DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2010-2019  Kirill Yegorov
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

namespace Dvelum\Store;


/**
 * Store Factory
 * @author Kirill Egorov 2010
 */
class Factory
{
    const LOCAL = 1;
    const SESSION = 2;

    static protected $instances = [];

    /**
     * Store factory
     * @param int $type - const
     * @param string $name
     * @return AdapterInterface
     * @throws \Exception
     */
    static public function get($type = self::LOCAL, $name = 'default'): AdapterInterface
    {
        switch ($type) {
            case self::LOCAL :
                if (!isset(self::$instances[$type][$name])) {
                    self::$instances[$type][$name] = new \Dvelum\Store\Local($name);
                }
                return self::$instances[$type][$name];
                break;
            case self::SESSION :
                if (!isset(self::$instances[$type][$name])) {
                    self::$instances[$type][$name] = new \Dvelum\Store\Session($name);
                }
                return self::$instances[$type][$name];
                break;
            default:
                throw new \Exception('Undefined type' . $type);
        }
    }
}