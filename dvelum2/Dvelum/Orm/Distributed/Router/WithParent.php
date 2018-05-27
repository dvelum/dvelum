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

namespace Dvelum\Orm\Distributed\Router;

use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Distributed;
use Dvelum\Orm\RecordInterface;

class WithParent implements RouteInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var Distributed
     */
    protected $sharding;

    public function __construct(Distributed $sharding, ConfigInterface $config)
    {
        $this->config = $config;
        $this->sharding = $sharding;
    }

    /**
     * Find shard for object
     * @param RecordInterface $record
     * @return null|string
     */
    public function getShard(RecordInterface $record) : ?string
    {
        $parentObject = $this->config->get('parent');
        $parentField = $this->config->get('parent_field');
        $parentId = $record->get($parentField);
        $objectShard = '';

        if(!empty($parentId)){
            $objectShard = $this->sharding->findObjectShard($parentObject , $parentId);
        }

        if(empty($objectShard)){
            $objectShard = null;
        }

        return $objectShard;
    }
}