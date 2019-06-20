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

namespace Dvelum\Config;

/**
 * Interface ConfigInterface
 * @package Dvelum\Config
 */
interface ConfigInterface extends \ArrayAccess , \Iterator
{
    public function __construct(string $name);

    /**
     * Convert into an array
     * @return array
     */
    public function __toArray() : array;

    /**
     * Get the number of elements
     * @return integer
     */
    public function getCount() : int;

    /**
     * Get the configuration parameter
     * @param string $key — parameter name
     * @throws \Exception
     * @return mixed
     */
    public function get(string $key);

    /**
     *  Set the property value
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key , $value) : void;

    /**
     * Set property values using an array
     * @param array $data
     */
    public function setData(array $data) : void;

    /**
     * Remove a parameter
     * @param string $key
     * @return true
     */
    public function remove(string $key);

    /**
     * Get data handle
     * Hack method. Do not use it without understanding.
     * Get a direct link to the stored data array
     * @return array
     */
    public function & dataLink() : array;

    /**
     * Remove all parameters
     */
    public function removeAll() : void;
    /**
     * Get config name
     * @return string
     */
    public function getName() : string;

    /**
     * Get parent config identifier
     * @return string|null
     */
    public function getParentId() : ?string;

    /**
     * Set parent configuration identifier
     * @param string $id
     */
    public function setParentId(?string $id) :void;
}