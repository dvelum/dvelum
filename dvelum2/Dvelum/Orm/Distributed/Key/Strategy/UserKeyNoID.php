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
use Dvelum\Orm\Distributed\Router;
use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;
use \Exception;

class UserKeyNoID implements GeneratorInterface
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;
    protected $shardField;
    /**
     * @var Router
     */
    protected $router;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->shardField =  $config->get('shard_field');
    }
    /**
     * Set routing adapter
     * @param Router $router
     * @return mixed
     */
    public function setRouter(Router $router) : void
    {
        $this->router = $router;
    }

    /**
     * Delete reserved index
     * @param Record $object
     * @param $distributedKey
     * @return bool
     */
    public function deleteIndex(Record $object, $distributedKey) : bool
    {
        $objectConfig = $object->getConfig();
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);
        $db = $model->getDbConnection();
        try{
            $db->delete($model->table(), $db->quoteIdentifier($db->quoteIdentifier($objectConfig->getShardingKey()) .' = '.$db->quote($distributedKey)));
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
     * @return ?Reserved
     */
    public function reserveIndex(Record $object , string $shard) : ?Reserved
    {
        $objectConfig = $object->getConfig();
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);
        $indexConfig = $model->getObjectConfig();

        $distributedKey = $objectConfig->getShardingKey();
        $savedIndexData = $model->query()->filters([$distributedKey=>$object->get($distributedKey)])->params(['limit'=>1])->fetchRow();

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
            if(!empty($savedIndexData)){
                $keyValue = $indexData[$distributedKey];
                unset($indexData[$distributedKey]);
                unset($indexData[$this->shardField]);
                if(!empty($indexData)){
                    $db->update($model->table(), $indexData, $db->quoteIdentifier($distributedKey) . ' = ' . $db->quote($keyValue));
                }

            }else{
                unset($indexData[$objectConfig->getPrimaryKey()]);
                $db->insert($model->table(), $indexData);
            }

            $db->commit();
            $result = new Reserved();
            $result->setShard($shard);
            return $result;
        }catch (Exception $e){
            $db->rollBack();
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
     * @param array $objectIds
     * @return array  [shard_id=>[key1,key2,key3], shard_id2=>[...]]
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
    public function detectShard(Record $record): ?string
    {
        $objectConfig = $record->getConfig();
        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);

        $distributedKey = $objectConfig->getShardingKey();

        $shard = null;

        $data = $model->query()->filters([$distributedKey=>$record->get($distributedKey)])->params(['limit'=>1])->fetchRow();

        if (!empty($data)) {
            $shard = $data[$this->shardField];
        }
        return $shard;
    }
}