<?php
/**
 * DVelum project https://github.com/dvelum/dvelum
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

namespace Dvelum\Cache;

abstract class AbstractAdapter
{
    protected $keyPrefix = '';

    protected $stat = [
        'load' => 0 ,
        'save' => 0 ,
        'remove' => 0
    ];

    /**
     * Adapter configuration
     * @var array
     */
    protected $settings = [];

    /**
     * AbstractAdapter constructor.
     * @param array $options - configuration options
     */
    public function __construct(array $options = [])
    {
        $this->settings = $this->initConfiguration($options);
        $this->connect($this->settings);
    }

    abstract protected function initConfiguration(array $options) : array;
    abstract protected function connect(array $settings);

    /**
     * Prepare key, normalize, add prefix
     * @param $key
     * @return string
     */
    protected function prepareKey($key) : string
    {
        $key = $this->keyPrefix . $key;

        if(!empty($this->options['normalizeKeys']))
            $key = $this->normalize($key);

        return $key;
    }

    /**
     * Normalize key length
     * @param string $key
     * @return string
     */
    protected function normalize(string $key) : string
    {
        return md5($key);
    }

    /**
     * Get cache operations stats
     * @return array
     */
    public function getOperationsStat() : array
    {
        return $this->stat;
    }
}