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

namespace Dvelum\Orm\Distributed\Model;
use Dvelum\Orm\Distributed\Model;
use Dvelum\Orm;

class Query extends Orm\Model\Query
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
        unset($this->db);
    }
    /**
     * Set Shard
     * @param string $shard
     * @return Query
     */
    public function setShard(string $shard) : Query
    {
        $this->db = $this->model->getDbShardConnection($shard);
        return $this;
    }
}