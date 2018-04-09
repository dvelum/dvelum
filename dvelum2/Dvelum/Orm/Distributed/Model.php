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
     * @return array|false
     */
    final public function getItem($id, $fields = ['*'])
    {
        $config = $this->getObjectConfig();
        if($config->isDistributed()){
            $sharding = Distributed::factory();
            $shard = $sharding->getObjectShard($config->getName(), $id);

            if(!$shard){
                return false;
            }

            $db = $this->dbManager->getDbConnection($config->get('connection'), $shard);
        }else{
            $db = $this->getSlaveDbConnection();
        }

        $primaryKey = $this->getPrimaryKey();
        $result = $this->query(true)->setDbConnection($db)
            ->filters([
                $primaryKey  => $id
            ])
            ->fields($fields)
            ->fetchRow();

        if(empty($result)){
            $result = false;
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
    public function getCachedItemByField(string $field, $value)
    {
        if($this->getObjectConfig()->isDistributed()){
            throw new Exception('getCachedItemByField method can`t work properly with distributed objects');
        }

        $cacheKey = $this->getCacheKey(array('item', $field, $value));
        $data = false;

        if ($this->cache) {
            $data = $this->cache->load($cacheKey);
        }

        if ($data !== false) {
            return $data;
        }

        $data = $this->getItemByField($field, $value);

        if ($this->cache && $data) {
            $this->cache->save($data, $cacheKey);
        }

        return $data;
    }

    /**
    * Get Item by field value. Returns first occurrence
    * @param string $fieldName
    * @param $value
    * @param string $fields
    * @return array|null
    * @throws Exception
    */
    public function getItemByField(string $fieldName, $value, $fields = '*')
    {
        if($this->getObjectConfig()->isDistributed()){
            throw new Exception('getItemByField method can`t work properly with distributed objects');
        }

        $sql = $this->dbSlave->select()->from($this->table(), $fields);
        $sql->where($this->dbSlave->quoteIdentifier($fieldName) . ' = ?', $value)->limit(1);
        return $this->dbSlave->fetchRow($sql);
    }

    /**
     * Get a number of entries a list of IDs
     * @param array $ids - list of IDs
     * @param mixed $fields - optional - the list of fields to retrieve
     * @param bool $useCache - optional, defaul false
     * @return array / false
     * @todo Create distributed version
     */
    final public function getItems(array $ids, $fields = '*', $useCache = false)
    {
        $data = false;

        if (empty($ids)) {
            return [];
        }

        if ($useCache && $this->cache) {
            $cacheKey = $this->getCacheKey(array('list', serialize(func_get_args())));
            $data = $this->cache->load($cacheKey);
        }

        if ($data === false) {
            $sql = $this->dbSlave->select()->from($this->table(),
                $fields)->where($this->dbSlave->quoteIdentifier($this->getPrimaryKey()) . ' IN(' . \Utils::listIntegers($ids) . ')');
            $data = $this->dbSlave->fetchAll($sql);

            if (!$data) {
                $data = [];
            }

            if ($useCache && $this->cache) {
                $this->cache->save($data, $cacheKey, $this->cacheTime);
            }

        }
        return $data;
    }

    /**
     * Create Orm\Model\Query
     * @return Orm\Model\Query
     * @throws Exception
     */
    public function query(): Orm\Model\Query
    {
        return new Query($this);
    }

    /**
     * Delete record
     * @param mixed $recordId record ID
     * @return bool
     * @todo Create distributed version
     */
    public function remove($recordId): bool
    {
        try {
            $object = Orm\Record::factory($this->name, $recordId);
        } catch (\Exception $e) {
            $this->logError('Remove record ' . $recordId . ' : ' . $e->getMessage());
            return false;
        }

        if ($this->getObjectsStore()->delete($object)) {
            return true;
        } else {
            return false;
        }
    }

    /**
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
        if($this->getObjectConfig()->isDistributed()){
            throw new Exception('checkUnique method can`t work properly with distributed objects');
        }

        return !(boolean)$this->dbSlave->fetchOne($this->dbSlave->select()->from($this->table(),
            array('count' => 'COUNT(*)'))->where($this->dbSlave->quoteIdentifier($this->getPrimaryKey()) . ' != ?',
            $recordId)->where($this->dbSlave->quoteIdentifier($fieldName) . ' =?', $fieldValue));
    }








    public function getIndexes() : array
    {
        $config = $this->getObjectConfig();
        $indexObject =  $config->getDistributedIndexObject();
        if(!empty($indexObject)){
            $model =  Orm\Model::factory($indexObject);

        }
    }
}