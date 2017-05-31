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

namespace Dvelum\App\Dictionary;

use Dvelum\Config\ConfigInterface;
use Dvelum\App\Dictionary;

class Service
{
    protected $objects = [];
    /**
     * @var ConfigInterface
     */
    protected $config = null;

    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function get(string $name) : DictionaryInterface
    {
        if(!isset($this->objects[$name])){
            $this->objects[$name] = new Dictionary\Adapter\File($name, $this->config);
        }
        return $this->objects[$name];
    }
}
