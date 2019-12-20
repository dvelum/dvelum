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
use Dvelum\Orm\Distributed\Key\Reserved;
use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;
use Dvelum\Orm\RecordInterface;
use \Exception;

class UserKeyNoID implements GeneratorInterface
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;
    protected $shardField;
    protected $exceptIndexPrimaryKey = true;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->shardField =  $config->get('shard_field');
    }

    /**
     * Delete reserved index
     * @param RecordInterface $object
     * @param mixed $distributedKey
     * @return bool
     */
    public function deleteIndex(RecordInterface $object, $distributedKey) : bool
    {
        try{
            $objectConfig = $object->getConfig();
            $indexObject = $objectConfig->getDistributedIndexObject();
            $model = Model::factory($indexObject);
            $db = $model->getDbConnection();
            $shardingKey = $objectConfig->getShardingKey();
            if(empty($shardingKey)){
                return false;
            }
            $db->delete($model->table(), $db->quoteIdentifier($db->quoteIdentifier($shardingKey) .' = '.$db->quote($distributedKey)));
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

        $fieldList = $indexConfig->getFields();
        $primary = $indexConfig->getPrimaryKey();

        $indexData = [
            $this->shardField => $shard
        ];
        /**
         * @var Record\Config\Field $field
         */
        foreach ($fieldList as $field)
        {
            $fieldName = $field->getName();

            if(($this->exceptIndexPrimaryKey && $fieldName == $primary) || $fieldName == $this->shardField){
                continue;
            }

            try{
                if($fieldName == $primary && $object->getInsertId()){
                    $indexData[$fieldName] = $object->getInsertId();
                }else{
                    $indexData[$fieldName] = $object->get($fieldName);
                }
            }catch (Exception $e){
                $model->logError('Sharding Invalid index structure for  '.$objectConfig->getName().' '.$e->getMessage());
                return null;
            }
        }
        return $this->reserveKey($object, $indexData);
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

        $query = $model->query()->filters([
            $objectConfig->getShardingKey() => $distributedKey
        ]);

        $shardData = $query->fetchRow();

        if(empty($shardData)){
            return null;
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

        $distributedKey = $objectConfig->getShardingKey();

        if(empty($distributedKey)){
            throw new Exception('undefined distributed key name');
        }

        $model = Model::factory($indexObject);
        $query = $model->query()->filters([
            $objectConfig->getShardingKey()  => $distributedKeys
        ]);

        $shardData = $query->fetchAll();

        if(empty($shardData)){
            return [];
        }

        $result = [];

        foreach ($shardData as $item){
            $result[$item[$this->shardField]][] = $item[$distributedKey];
        }
        return $result;
    }

    /**
     * Detect object shard by own rules
     * @param Record $record
     * @return null|string
     */
    public function detectShard(RecordInterface $record): ?string
    {
        $objectConfig = $record->getConfig();
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);

        $distributedKey = $objectConfig->getShardingKey();

        if(empty($distributedKey) || empty($record->get($distributedKey))){
            return null;
        }

        $shard = null;

        $data = $model->query()->filters([$distributedKey=>$record->get($distributedKey)])->params(['limit'=>1])->fetchRow();

        if (!empty($data)) {
            $shard = $data[$this->shardField];
        }
        return $shard;
    }

    /**
     * Reserve
     * @param RecordInterface $object
     * @param array $keyData
     * @return Reserved|null
     */
    public function reserveKey(RecordInterface $object , array $keyData) : ?Reserved
    {
        $result = $this->insertOrGetKey($object, $keyData);
        if(empty($result)){
            // try restart
            $result =  $this->insertOrGetKey($object, $keyData);
        }
        return $result;
    }

    /**
     * Detect shard for user key
     * @param string $objectName
     * @param mixed $key
     * @return null|string
     * @throws \Exception
     */
    public function detectShardByKey(string $objectName,  $key) : ? string
    {
        $objectConfig = Record\Config::factory($objectName);
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);
        $keyName = $objectConfig->getShardingKey();

        $data = $model->query()->filters([$keyName=>$key])->params(['limit'=>1])->fetchRow();
        if(!empty($data)){
            return $data[$this->shardField];
        }
        return null;
    }

    /**
     * Change shard value for user key in index table
     * @param string $objectName
     * @param mixed $shardingKeyValue
     * @param string $newShard
     * @return bool
     */
    public function changeShard(string $objectName, $shardingKeyValue, string $newShard) : bool
    {
        $objectConfig = Record\Config::factory($objectName);
        $shardingKey = $objectConfig->getShardingKey();
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);
        $db = $model->getDbConnection();

        if(empty($shardingKey)){
            return false;
        }

        try{
            $db->update(
                $model->table(),
                [
                    $this->shardField => $newShard
                ],
                $db->quoteIdentifier($shardingKey).' = '.$db->quote($shardingKeyValue)
            );
            return true;
        }catch (Exception $e){
            $model->logError($e->getMessage());
            return false;
        }
    }

    /**
     * @param RecordInterface $record
     * @param array $keyData
     * @return Reserved|null
     * @throws Exception
     */
    protected function insertOrGetKey(RecordInterface $record , array $keyData) : ?Reserved
    {
        $objectName = $record->getName();
        $objectConfig = Record\Config::factory($objectName);
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);
        $keyName = $objectConfig->getShardingKey();

        $data = $model->query()->filters([$keyName=>$keyData[$keyName]])->params(['limit'=>1])->fetchRow();

        if(!empty($data)){
            $reserved = new Reserved();
            $reserved->setShard($data[$this->shardField]);
            return $reserved;
        }
        $db = $model->getDbConnection();
        try{
            $db->beginTransaction();
            $db->insert($model->table(),$keyData);
            $id = $db->lastInsertId($model->table());
            $data = $model->query()->filters([$model->getPrimaryKey()=>$id])->fetchRow();
            if(empty($data)){
                throw new Exception('Transaction error');
            }
            $db->commit();
            $reserved = new Reserved();
            $reserved->setShard($data[$this->shardField]);
            return $reserved;
        }catch (Exception $e){
            $db->rollback();
            $model->logError($e->getMessage());
            $model->logError('Cannot reserve key for '.$objectName.':: '.$keyData[$this->shardField].' try restart');
        }
        return null;
    }
}