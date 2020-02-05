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
 * The class allows you to store data locally in the form of key value pairs
 * Note that null value causes the keyExists() method return false (for better perfomance)
 * @author Kirill Yegorov 2008
 * @package Store
 */
class Local implements AdapterInterface
{
    protected $storage;
    protected $name;

    /**
     * Local constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->storageConnect();
    }

    /**
     * Instantiate storage
     * @return void
     */
    protected function storageConnect()
    {
        $this->storage = [];
    }

    /**
     * (non-PHPdoc)
     * @see Dvelum/Store/Store_Interface#getData()
     */
    public function getData() : array
    {
        return $this->storage;
    }

    /**
     * Get items count
     * @return integer
     */
    public function getCount() : int
    {
        return count($this->storage);
    }

    /**
     * (non-PHPdoc)
     * @see www/library/Store/AdapterInterface#get($key)
     */
    public function get($key)
    {
        if(isset($this->storage[$key]))
            return $this->storage[$key];
        return null;
    }

    /**
     * Note that null value causes keyExists return false (for better perfomance)
     * (non-PHPdoc)
     * @see www/library/Store/Store_Interface#set($key, $val)
     */
    public function set($key , $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * (non-PHPdoc)
     * @see www/library/Store/Store_Interface#setValues($array)
     */
    public function setValues(array $array)
    {
        foreach($array as $k => $v)
            $this->set($k , $v);
    }

    /**
     * Note that null value causes the keyExists() method return false (for better perfomance)
     * (non-PHPdoc)
     * @see www/library/Store/Store_Interface#keyExists($key)
     */
    public function keyExists($key) : bool
    {
        return isset($this->storage[$key]);
    }

    /**
     * (non-PHPdoc)
     * @see www/library/Store/Store_Interface#remove($key)
     */
    public function remove($key) : void
    {
        unset($this->storage[$key]);
    }

    /**
     * (non-PHPdoc)
     * @see AdapterInterface::clear()
     */
    public function clear() : void
    {
        $this->storage = [];
    }
    /**
     * Replace store data
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->storage = $data;
    }
}