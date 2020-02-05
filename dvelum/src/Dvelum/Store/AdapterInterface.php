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
 * Storage interface
 * @package Store
 * @author Kirill Egorov 2011
 */
interface AdapterInterface{
    /**
     * Store value
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public function set($key,$val);
    /**
     * Set values from array
     * @param array $array
     * @return mixed
     */
    public function setValues(array $array);
    /**
     * Replace store data
     * @param array $data
     * @return mixed
     */
    public function setData(array $data);
    /**
     * Get stored value by key
     * @param string $key
     * @return mixed
     */
    public function get($key);
    /**
     * Check if key exists
     * @param string $key
     * @return bool
     */
    public function keyExists($key) : bool ;
    /**
     * Remove data from storage
     * @param string $key
     * @return void
     */
    public function remove($key): void;
    /**
     * Clear storage.(Remove data)
     */
    public function clear() : void;
    /**
     * Get all storage data
     * @return array
     */
    public function getData() : array;
    /**
     * Get records count
     * @return integer
     */
    public function getCount() : int;
}