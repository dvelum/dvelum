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

use Dvelum\Db\Select\Filter;
use Dvelum\Orm;
use Dvelum\Utils;
use \Exception;

/**
 * Base class for data models
 */
class Model extends Orm\Model
{
    /**
     * Get record by id
     * @param integer $id
     * @param array|string $fields — optional — the list of fields to retrieve
     * @return array
     * @throws \Exception
     */
    public function getItem($id, $fields = ['*']) : array
    {
        $sharding = Orm\Distributed::factory();
        $shard = $sharding->findObjectShard($this->getObjectName(), $id);

        if(empty($shard)){
            return [];
        }

        $db = $this->getDbShardConnection($shard);
        $primaryKey = $this->getPrimaryKey();
        $query = $this->query()->setDbConnection($db)
            ->filters([
                $primaryKey  => $id
            ])
            ->fields($fields);

        $result = $query->fetchRow();;

        if(empty($result)){
            $result = [];
        }
        return $result;
    }

    /**
     * Get record by id from shard
     * @param mixed $id
     * @param string $shard
     * @return array
     * @throws \Exception
     */
    public function getItemFromShard($id, string $shard) : array
    {
        $db = $this->getDbShardConnection($shard);
        $primaryKey = $this->getPrimaryKey();

        $query = $this->query()->setDbConnection($db)
            ->filters([
                $primaryKey  => $id
            ]);

        $result = $query->fetchRow();

        if(empty($result)){
            $result = [];
        }
        return $result;
    }

    /**
     * Get data record by field value using cache. Returns first occurrence
     * @param string $field - field name
     * @param string $value - field value
     * @throws Exception
     * @return array
     */
    public function getCachedItemByField(string $field, $value) : array
    {
        $cacheKey = $this->getCacheKey(array('item', $field, $value));
        $data = false;

        if ($this->cache) {
            $data = $this->cache->load($cacheKey);
        }

        if ($data !== false) {
            return $data;
        }

        $data = $this->getItemByField($field, $value);

        if(empty($data)){
            $data = [];
        }

        if ($this->cache && $data) {
            $this->cache->save($data, $cacheKey);
        }

        return $data;
    }

    /**
    * Note check only IndexObject
    * Get Item by field value. Returns first occurrence
    * @param string $fieldName
    * @param mixed $value
    * @param string|array $fields
    * @return array|null
    * @throws Exception
    */
    public function getItemByField(string $fieldName, $value, $fields = '*')
    {
        $model = Model::factory($this->getObjectConfig()->getDistributedIndexObject());
        $item = $model->getItemByField($fieldName, $value);

        if(!empty($item)){
            return $this->getItem($item[$this->getPrimaryKey()],$fields);
        }else{
            return [];
        }
    }

    /**
     * Get a number of entries a list of IDs
     * @param array $ids - list of IDs
     * @param mixed $fields - optional - the list of fields to retrieve
     * @param bool $useCache - optional, default false
     * @return array / false
     * @throws Exception
     */
    final public function getItems(array $ids, $fields = '*', $useCache = false)
    {
        $data = false;
        $cacheKey = '';

        if (empty($ids)) {
            return [];
        }

        if ($useCache && $this->cache) {
            $cacheKey = $this->getCacheKey(array('list', serialize(func_get_args())));
            $data = $this->cache->load($cacheKey);
        }

        if ($data === false) {

            $sharding = Orm\Distributed::factory();
            $shards = $sharding->findObjectsShards($this->getObjectName(), $ids);

            $data = [];

            if(!empty($shards))
            {
                foreach ($shards as $shard=>$items)
                {
                    $db = $this->getDbShardConnection($shard);

                    $results = $this->query()
                         ->setDbConnection($db)
                         ->fields($fields)
                         ->filters([$this->getPrimaryKey()=>$items])
                         ->fetchAll();

                    $data = array_merge($data , $results);
                }
            }

            if(!empty($data)){
                $data = Utils::rekey($this->getPrimaryKey(), $data);
            }

            if ($useCache && $this->cache) {
                $this->cache->save($data, $cacheKey, $this->cacheTime);
            }
        }
        return $data;
    }

    /**
     * Create Orm\Model\Query
     * @return Orm\Distributed\Model\Query
     * @throws Exception
     */
    public function query(): Orm\Model\Query
    {
        return new Model\Query($this);
    }

    /**
     * Delete record
     * @param mixed $recordId record ID
     * @return bool
     */
    public function remove($recordId): bool
    {
        try {
            /**
             * @var Orm\RecordInterface $object
             */
            $object = Orm\Record::factory($this->getObjectName(), $recordId);
        } catch (\Exception $e) {
            $this->logError('Remove record ' . $recordId . ' : ' . $e->getMessage());
            return false;
        }

        if ($this->getStore()->delete($object)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Note check only IndexObject
     *
     * Check whether the field value is unique
     * Returns true if value $fieldValue is unique for $fieldName field
     * otherwise returns false
     * @param int $recordId — record ID
     * @param string $fieldName — field name
     * @param mixed $fieldValue — field value
     * @return bool
     * @throws Exception
     */
    public function checkUnique(int $recordId, string $fieldName, $fieldValue): bool
    {
        $model = Model::factory($this->getObjectConfig()->getDistributedIndexObject());

        $filters = [
             new Filter($this->getPrimaryKey(), $recordId,Filter::NOT),
             $fieldName => $fieldValue
        ];

        return !(boolean) $model->query()->fields(['count' => 'COUNT(*)'])->filters($filters)->fetchOne();
    }

    /**
     * Get insert object
     * @return Orm\Distributed\Model\Insert
     */
    public function insert() : Orm\Model\InsertInterface
    {
        return new Orm\Distributed\Model\Insert($this);
    }
}