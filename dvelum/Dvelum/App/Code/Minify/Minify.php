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

namespace Dvelum\App\Code\Minify;

use Dvelum\App\Code\Minify\Adapter\AdapterInterface;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;

class Minify
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;

    static public function factory()
    {
        $config = Config::storage()->get('minify.php');
        return new static($config);
    }

    protected function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Minify JS string
     * @param string $string
     * @return string
     */
    public function minifyJs(string $string) : string
    {
        $adapterClass = $this->config->get('js')['adapter'];
        /**
         * @var AdapterInterface $adapter
         */
        $adapter = new $adapterClass;
        return $adapter->minify($string);
    }

    /**
     * Combine and minify code files
     * @param array $files
     * @param string $toFile
     * @return bool
     */
    public function minifyJsFiles(array $files, string $toFile) : bool
    {
        $adapterClass = $this->config->get('js')['adapter'];
        /**
         * @var AdapterInterface $adapter
         */
        $adapter = new $adapterClass;
        return $adapter->minifyFiles($files, $toFile);
    }

    /**
     * Minify CSS string
     * @param string $string
     * @return string
     */
    public function minifyCss(string $string) : string
    {
        $adapterClass = $this->config->get('css')['adapter'];
        /**
         * @var AdapterInterface $adapter
         */
        $adapter = new $adapterClass;
        return $adapter->minify($string);
    }

    /**
     * Combine and minify code files
     * @param array $files
     * @param string $toFile
     * @return bool
     */
    public function minifyCssFiles(array $files, string $toFile) : bool
    {
        $adapterClass = $this->config->get('css')['adapter'];
        /**
         * @var AdapterInterface $adapter
         */
        $adapter = new $adapterClass;
        return $adapter->minifyFiles($files, $toFile);
    }
}