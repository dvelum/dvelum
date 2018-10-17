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

use Dvelum\Orm\Distributed\Key\GeneratorInterface;
use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;
use Dvelum\Orm\Distributed\Key\Reserved;
use Dvelum\Orm\RecordInterface;
use \Exception;

class UniqueID implements GeneratorInterface
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;
    protected $shardField;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->shardField =  $config->get('shard_field');
    }
    /**
     * Delete reserved index
     * @param RecordInterface $object
     * @param mixed $indexId
     * @return bool
     */
    public function deleteIndex(RecordInterface $object, $indexId) : bool
    {
        $objectConfig = $object->getConfig();
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);
        $db = $model->getDbConnection();
        try{
            $db->delete($model->table(), $db->quoteIdentifier($model->getPrimaryKey()).' = '.$db->quote($indexId));
            return true;
        }catch (Exception $e){
            $model->logError('Sharding::reserveIndex '.$e->getMessage());
            return false;
        }
    }

    /**
     * Reserve object id, add to routing table
     * @param Record $object
     * @param string $shard
     * @return Reserved|null
     * @throws Exception
     */
    public function reserveIndex(RecordInterface $object , string $shard) : ?Reserved
    {
        $objectConfig = $object->getConfig();
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);
        $indexConfig = $model->getObjectConfig();
        $db = $model->getDbConnection();

        $fieldList = $indexConfig->getFields();
        $primary = $indexConfig->getPrimaryKey();

        $indexData = [
            $this->shardField => $shard
        ];
        /**
         * @var Record\Config\Field $field
         */
        foreach ($fieldList as $field){
            $fieldName = $field->getName();

            if($fieldName == $primary || $fieldName == $this->shardField){
                continue;
            }

            try{
                $indexData[$fieldName] = $object->get($fieldName);
            }catch (Exception $e){
                $model->logError('Sharding Invalid index structure for  '.$objectConfig->getName().' '.$e->getMessage());
                return null;
            }
        }

        try{
            $db->beginTransaction();
            $db->insert($model->table(),$indexData);

            $id = $db->lastInsertId($model->table(),$objectConfig->getPrimaryKey());
            $db->commit();

            $result = new Reserved();
            $result->setId($id);
            $result->setShard($shard);

            return $result;
        }catch (Exception $e){
            $db->rollback();
            $model->logError('Sharding::reserveIndex '.$e->getMessage());
            return null;
        }
    }

    /**
     * Get object shard id
     * @param string $objectName
     * @param mixed $distributedKey
     * @return mixed
     */
    public function findObjectShard(string $objectName, $distributedKey)
    {
        $objectConfig = Record\Config::factory($objectName);
        $indexObject = $objectConfig->getDistributedIndexObject();

        $model = Model::factory($indexObject);
        $query = $model->query()->filters([$objectConfig->getPrimaryKey() => $distributedKey]);

        $shardData = $query->fetchRow();

        if(empty($shardData)){
            return false;
        }
        return $shardData[$this->shardField];
    }

    /**
     * Get shards for list of objects
     * @param string $objectName
     * @param array $distributedKeys
     * @return array  [shard_id=>[key1,key2,key3], shard_id2=>[...]]
     * @throws Exception
     */
    public function findObjectsShards(string $objectName, array $distributedKeys) : array
    {
        $objectConfig = Record\Config::factory($objectName);
        $indexObject = $objectConfig->getDistributedIndexObject();

        $model = Model::factory($indexObject);
        $query = $model->query()->filters([$objectConfig->getPrimaryKey() => $distributedKeys]);

        $shardData = $query->fetchAll();

        if(empty($shardData)){
            return [];
        }
        $result = [];
        $idField = $model->getObjectConfig()->getPrimaryKey();
        foreach ($shardData as $item){
            $result[$item[$this->shardField]][] = $item[$idField];
        }
        return $result;
    }

    /**
     * Detect object shard by own rules
     * @param RecordInterface $record
     * @return null|string
     */
    public function detectShard(RecordInterface $record): ?string
    {
        return null;
    }
}