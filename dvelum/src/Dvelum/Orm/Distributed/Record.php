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

namespace Dvelum\Orm\Distributed;

use Dvelum\Orm;
use Dvelum\Orm\Record\Config;

class Record extends Orm\Record
{
    protected $shard = null;
    /**
     * @var Orm\Distributed\Record\Store $store
     */
    protected $store;

    public function __construct(string $name, $id = false, $shard = false)
    {
        $this->shard = $shard;
        $config = Config::factory($name);
        if ($config->getShardingType() === Config::SHARDING_TYPE_KEY_NO_INDEX && empty($shard) && !empty($id)) {
            throw new Orm\Exception('Sharded object with type of Config::SHARDING_TYPE_KEY_NO_INDEX requires shard to be defined at constructor');
        }

        parent::__construct($name, $id);
    }

    public function loadData(): void
    {
        $model = Model::factory($this->getName());
        $store = $model->getStore();
        $store->setShard((string)$this->shard);
        parent::loadData();
    }

    public function getShard() : string
    {
        return $this->get(Orm\Distributed::factory()->getShardField());
    }
}