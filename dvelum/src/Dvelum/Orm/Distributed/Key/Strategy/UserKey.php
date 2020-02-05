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

namespace Dvelum\Orm\Distributed\Key\Strategy;

use Dvelum\Orm\Model;
use Dvelum\Orm\Record;
use Dvelum\Orm\RecordInterface;


class UserKey extends UniqueID
{
    /**
     * Detect object shard by own rules
     * @param RecordInterface $record
     * @return null|string
     * @throws \Exception
     */
    public function detectShard(RecordInterface $record): ?string
    {
        $objectConfig = $record->getConfig();
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);

        $shardingKey = $objectConfig->getShardingKey();

        if(empty($shardingKey)){
            return null;
        }

        $value = $record->get($shardingKey);

        $shard = null;
        $data = $model->query()->filters([$shardingKey => $value])->params(['limit' => 1])->fetchRow();

        if (!empty($data)) {
            $shard = $data[$this->shardField];
        }

        return $shard;
    }
}