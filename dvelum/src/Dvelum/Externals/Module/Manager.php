<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
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
 *
 */
declare(strict_types=1);

namespace Dvelum\Externals\Module;

use Dvelum\Config\ConfigInterface;

class Manager
{
    protected $appConfig;
    protected $externalsConfig;

    static public function factory(ConfigInterface $appConfig)
    {
        static $instance = null;
        if(empty($instance)){
            $instance = new static($appConfig);
        }
        return $instance;
    }

    protected function __construct(ConfigInterface $appConfig)
    {
        $this->appConfig = $appConfig;
        $this->externalsConfig = $appConfig->get('externals');
    }

    /**
     * @var array
     */
    protected $modules = [];

    public function getModuleConfig(string $vendor, string $module) : ?array
    {
        $vendor = strtolower($vendor);
        $module = strtolower($module);

        if(isset($this->modules[$vendor][$module])){
            return $this->modules[$vendor][$module];
        }

        $path = $this->externalsConfig['path'];
        $composerPath = $path.'/'.$vendor.'/'.$module.'/'.'composer.json';
        if(file_exists($composerPath)){
            $config = json_decode(file_get_contents($composerPath),true);
            if(!empty($config)){
                $this->modules[$vendor][$module] = $config;
                return $config;
            }
        }
        return null;
    }
}