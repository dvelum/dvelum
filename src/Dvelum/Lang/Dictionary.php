<?php
/*
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
 * Copyright (C) 2011-2016  Kirill A Egorov
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

namespace Dvelum\Lang;

use Dvelum\Lang;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;

class Dictionary
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $loader;

    /**
     * @var ConfigInterface|bool
     */
    protected $data = false;

    /**
     * @param string $name
     * @param array $loaderConfig
     */
    public function __construct(string $name, array $loaderConfig)
    {
        $this->loader = $loaderConfig;
        $this->name = $name;
    }

    /**
     * Get a localized string by dictionary key.
     * If the necessary key is absent, the following value will be returned: «[key]»
     * @param string $key
     * @return string
     */
    public function get(string $key): string
    {
        if (!$this->data) {
            $this->loadData();
        };

        if ($this->data->offsetExists($key)) {
            return $this->data->get($key);
        }

        return '[' . $key . ']';
    }


    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        if (!$this->data) {
            $this->loadData();
        }

        return $this->data->offsetExists($key);
    }

    /**
     * Convert the localization dictionary to JSON
     * @return string
     */
    public function getJson(): string
    {
        if (!$this->data) {
            $this->loadData();
        }

        return \json_encode($this->data->__toArray());
    }

    /**
     * Convert the localization dictionary to JavaScript object
     * @return string
     */
    public function getJsObject(): string
    {
        if (!$this->data) {
            $this->loadData();
        }

        $items = [];
        foreach ($this->data as $k => $v) {
            $items[] = $k . ':"' . $v . '"';
        }

        return \str_replace("\n", "", '{' . implode(',', $items) . '}');
    }

    /**
     * Load dictionary data
     * @return void
     */
    protected function loadData(): void
    {
        switch ($this->loader['type']) {
            case Config\Factory::File_Array:
                $this->data = Lang::storage()->get($this->loader['src'], true, true);
                break;
        }
    }

    /**
     * Get current dictionary name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}