<?php
/**
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
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

namespace Dvelum\App\Console;

use Dvelum\Config\ConfigInterface;

abstract class Action implements ActionInterface
{
    /**
     * Main application config
     * @var ConfigInterface
     */
    protected $appConfig;
    protected $config;
    protected $stat = [];
    /**
     * Action params
     * @var array
     */
    protected $params = [];

    /**
     * @param ConfigInterface $appConfig
     * @param array $params
     * @param array $config
     * @return void
     */
    public function init(ConfigInterface $appConfig, array $params = [], array $config = []): void
    {
        $this->appConfig = $appConfig;
        $this->params = $params;
        $this->config = $config;
    }

    /**
     * Get job statistics as string
     * (useful for logs)
     * @return string
     */
    public function getInfo(): string
    {
        $s = '';
        foreach ($this->stat as $k => $v) {
            $s .= $k . ' : ' . $v . '; ';
        }

        return $s;
    }

    public function run() : bool
    {
        $t = microtime(true);
        $result = $this->action();
        $this->stat['time'] = number_format(microtime(true) - $t, 5).'s.';
        return $result;
    }

    /**
     * @return bool
     */
    abstract protected function action(): bool;
}