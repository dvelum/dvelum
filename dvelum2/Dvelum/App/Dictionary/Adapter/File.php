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

namespace Dvelum\App\Dictionary\Adapter;

use Dvelum\App\Dictionary\DictionaryInterface;
use Dvelum\Lang;
use Dvelum\Config;

class File implements DictionaryInterface
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var Config\ConfigInterface
     */
    protected $data = [];

    public function __construct(string $name , Config\ConfigInterface $config)
    {
        $this->name = $name;

        $configPath = $config->get('configPath') . $name . '.php';

        if(!Config::storage()->exists($configPath))
            Config::storage()->create($configPath);

        $this->data = Config::storage()->get($configPath, true, false);
    }

    /**
     * Get dictionary name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Check if the key exists in the dictionary
     * @param string $key
     * @return bool
     */
    public function isValidKey(string $key) : bool
    {
        return $this->data->offsetExists($key);
    }

    /**
     * Get value by key
     * @param string $key
     * @return string
     */
    public function getValue(string $key) : string
    {
        return $this->data->get($key);
    }

    /**
     * Get dictionary data
     * @return array
     */
    public function getData() : array
    {
        return $this->data->__toArray();
    }

    /**
     * Add a record
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addRecord(string $key , string $value) : void
    {
        $this->data->set($key, $value);
    }

    /**
     * Delete record by key
     * @param string $key
     * @return void
     */
    public function removeRecord(string $key) : void
    {
       $this->data->remove($key);
    }

    /**
     * Get dictionary as JavaScript code representation
     * @param boolean $addAll - add value 'All' with a blank key,
     * @param boolean $addBlank - add empty value is used in drop-down lists
     * @param string|boolean $allText, optional - text for not selected value
     * @return string
     */
    public function __toJs($addAll = false , $addBlank = false , $allText = false) : string
    {
        $result = [];

        if($addAll){
            if($allText === false){
                $allText = Lang::lang()->get('ALL');
            }
            $result[] = ['id' => '' , 'title' => $allText];
        }

        if(!$addAll && $addBlank)
            $result[] = ['id' => '' , 'title' => ''];

        foreach($this->data as $k => $v)
            $result[] = ['id' => strval($k) , 'title' => $v];

        return json_encode($result);
    }

    /**
     * Get key for value
     * @param $value
     * @param boolean $i case insensitive
     * @return mixed, false on error
     */
    public function getKeyByValue(string $value, $i = false)
    {
        foreach($this->data as $k=>$v)
        {
            if($i){
                $v = strtolower($v);
                $value = strtolower($value);
            }
            if($v === $value){
                return $k;
            }
        }
        return false;
    }

    /**
     * Save dictionary
     * @return bool
     */
    public function save() : bool
    {
        $storage = Config::storage();
        return $storage->save($this->data);
    }
}