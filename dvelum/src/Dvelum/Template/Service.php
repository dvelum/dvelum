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

namespace Dvelum\Template;

use Dvelum\Cache\CacheInterface;
use Dvelum\Config\Factory as ConfigFactory;
use Dvelum\Config\ConfigInterface;
use Dvelum\Template\Engine\EngineInterface;


class Service
{
    /**
     * @var string $adapterClass
     */
    protected $adapterClass;

    /**
     * @var ConfigInterface $engineConfig
     */
    protected $engineConfig;

    /**
     * @var CacheInterface|null
     */
    protected $cache = null;

    /**
     * Service constructor.
     * @param ConfigInterface $config
     * @param CacheInterface|null $cache
     * @throws \Exception
     */
    public function __construct(ConfigInterface $config, ?CacheInterface $cache)
    {
        $this->adapterClass = $config->get('template_engine');
        $this->engineConfig = ConfigFactory::create($config->get('engine_config'));
        $this->cache = $cache;
    }

    /**
     * @return EngineInterface
     */
    public function getTemplate() : EngineInterface
    {
        /**
         * @var EngineInterface $adapter
         */
        $adapter = new $this->adapterClass();
        $adapter->setConfig($this->engineConfig);
        $adapter->setCache($this->cache);

        return $adapter;
    }
}