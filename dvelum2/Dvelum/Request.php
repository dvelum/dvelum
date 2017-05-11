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

use Dvelum\Config;

/**
 * Class Request
 * @todo refactor! it's temporary realization
 * @package Dvelum
 */
class Request
{
    protected $config;

    protected $request;

    /**
     * @return Request
     */
    static public function factory()
    {
        static $instance = null;

        if(empty($instance)){
            $instance = new static();
        }

        return $instance;
    }

    private function __construct()
    {
        $this->request = \Request::getInstance();
    }


    /**
     * Set configuration options
     * @param Config\ConfigInterface $config
     */
    public function setConfig(Config\ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Set configuration option value
     * @param $name
     * @param $value
     */
    public function setConfigOption(string $name , $value)
    {
        $this->config->set($name, $value);
    }

    /**
     * @return array
     */
    public function postArray() : array
    {
        return \Request::postArray();
    }

    /**
     * @param $field
     * @param $type
     * @param $default
     * @return mixed
     */
    public function post($field, $type, $default)
    {
        return \Request::post($field, $type, $default);
    }

    /**
     * @param $field
     * @param $type
     * @param $default
     * @return mixed
     */
    public function get($field, $type, $default)
    {
        return \Request::post($field, $type, $default);
    }

    public function getPart($index)
    {
        return $this->request->getPart($index);
    }

    public function url(array $paths , $useExtension = true)
    {
        return \Request::url($paths , $useExtension);
    }

    public function extFilters()
    {
        return \Request::extFilters();
    }

    public function isAjax()
    {
        return\Request::isAjax();
    }

    public function hasPost()
    {
        return \Request::hasPost();
    }

    public function getUri()
    {
        return \Request::getInstance()->getUri();
    }

    public function files()
    {
        return \Request::files();
    }
}