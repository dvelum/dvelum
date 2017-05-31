<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
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
 *
 */
declare(strict_types=1);

namespace Dvelum\Console;

use Dvelum\Config\ConfigInterface;

abstract class Action
{
    /**
     * Main application config
     * @var ConfigInterface
     */
    protected $appConfig;
    protected $stat = [];
    /**
     * Action params
     * @var array
     */
    protected $params =[];

    /**
     * @param ConfigInterface $appConfig
     * @param array $params
     */
    public function init(ConfigInterface $appConfig, array $params = [])
    {
        $this->appConfig = $appConfig;
        $this->params = $params;
        $time = microtime(true);
        $this->run();
        $this->stat['time'] = number_format((microtime(true)-$time) , 5).'s.';
        echo get_called_class().': '.$this->getStatString()."\n";
    }

    /**
     * Get job statistics as string
     * (useful for logs)
     * @return string
     */
    public function getStatString()
    {
        $s = '';
        foreach ($this->stat as $k=>$v)
            $s.= $k .' : '.$v.'; ';

        return $s;
    }

    /**
     * @return void
     */
    abstract public function run();
}