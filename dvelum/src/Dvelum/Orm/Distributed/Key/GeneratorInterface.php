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

use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Record;
use Dvelum\Orm\RecordInterface;

interface GeneratorInterface
{
    /**
     * GeneratorInterface constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config);

    /**
     * Reserve object id, save route
     * @param RecordInterface $object
     * @param string $shard
     * @return null|Reserved
     */
    public function reserveIndex(RecordInterface $object, string $shard): ?Reserved;

    /**
     * Delete reserved index
     * @param RecordInterface $record
     * @param mixed $distributedKey
     * @return bool
     */
    public function deleteIndex(RecordInterface $record, $distributedKey) : bool;

    /**
     * Get object shard id
     * @param string $objectName
     * @param mixed $distributedKey
     * @return mixed
     */
    public function findObjectShard(string $objectName, $distributedKey);
    /**
     * Get shards for list of objects
     * @param string $objectName
     * @param array $distributedKeys
     * @return array  [shard_id=>[key1,key2,key3], shard_id2=>[...]]
     */
    public function findObjectsShards(string $objectName, array $distributedKeys) : array;

    /**
     * Detect object shard by own rules
     * @param RecordInterface $record
     * @return null|string
     */
    public function detectShard(RecordInterface $record): ?string;
}