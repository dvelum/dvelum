<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2018  Kirill Yegorov
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

namespace Dvelum\Orm\Distributed\Key;

use Dvelum\Orm\Distributed\Router;
use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Record;

interface GeneratorInterface
{
    /**
     * GeneratorInterface constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config);

    /**
     * Reserve object id, save route
     * @param Record $object
     * @param string $shard
     * @return null|Reserved
     */
    public function reserveIndex(Record $object, string $shard): ?Reserved;

    /**
     * Get object shard id
     * @param string $objectName
     * @param mixed $objectId
     * @return mixed
     */
    public function getObjectShard(string $objectName, $objectId);

    /**
     * Set routing adapter
     * @param Router $router
     * @return mixed
     */
    public function setRouter(Router $router) : void;
}