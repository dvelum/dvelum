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

use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Distributed\Key\Reserved;
use Dvelum\Orm\Distributed\Key\Strategy\VirtualBucket\MapperInterface;
use Dvelum\Orm\Distributed\Model;
use Dvelum\Orm\Exception;
use Dvelum\Orm\Record\Config;
use Dvelum\Orm\RecordInterface;

class VirtualBucket extends UserKeyNoID
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;
    protected $shardField;
    protected $options;
    protected $bucketField;
    protected $exceptIndexPrimaryKey = false;

    /**
     * @var MapperInterface $numericMapper
     */
    protected $numericMapper = null;
    /**
     * @var MapperInterface $stringMapper
     */
    protected $stringMapper = null;

    public function __construct(ConfigInterface $config)
    {
        parent::__construct($config);
        $this->bucketField = $config->get('bucket_field');
    }

    /**
     * @return MapperInterface
     * @throws \Exception
     */
    public function getNumericMapper():MapperInterface
    {
        if(empty($this->numericMapper)){
            $numericAdapter = $this->config->get('keyToBucket')['number'];
            $this->numericMapper =   new $numericAdapter();
        }
        return $this->numericMapper;
    }

    /**
     * @return MapperInterface
     * @throws \Exception
     */
    public function getStringMapper():MapperInterface
    {
        if(empty($this->stringMapper)){
            $numericAdapter = $this->config->get('keyToBucket')['string'];
            $this->stringMapper =   new $numericAdapter();
        }
        return $this->stringMapper;
    }

    /**
     * Reserve
     * @param RecordInterface $object
     * @param array $keyData
     * @return Reserved|null
     */
    public function reserveKey(RecordInterface $object, array $keyData): ?Reserved
    {
        $config = $object->getConfig();
        $keyField = $config->getBucketMapperKey();

        if(empty($keyField)){
            return null;
        }

        $fieldObject = $config->getField($keyField);

        $bucket = null;

        if($keyField == $config->getPrimaryKey()){
            $value = $object->getInsertId();
        }else{
            $value = $object->get($keyField);
        }

        if ($fieldObject->isNumeric()) {
            $bucket = $this->getNumericMapper()->keyToBucket($value);
        } elseif ($fieldObject->isText(true)) {
            $bucket = $this->getStringMapper()->keyToBucket($value);
        }

        if (empty($bucket)) {
            return null;
        }

        $keyData[$this->bucketField] = $bucket->getId();

        unset($keyData[$config->getPrimaryKey()]);
        $result = parent::reserveKey($object, $keyData);

        if (!empty($result)) {
            $result->setBucket($bucket->getId());
        }
        return $result;
    }

    /**
     * Get object shard id
     * @param string $objectName
     * @param mixed $distributedKey
     * @return mixed
     */
    public function findObjectShard(string $objectName, $distributedKey)
    {
        $config = Config::factory($objectName);
        $keyField = $config->getBucketMapperKey();

        $fieldObject = $config->getField($keyField);

        if ($fieldObject->isNumeric()) {
            $mapper = $this->getNumericMapper();
        } elseif ($fieldObject->isText(true)) {
            $mapper = $this->getStringMapper();
        }else{
            throw new Exception('Undefined key mapper for '.$objectName);
        }

        $bucket = $mapper->keyToBucket($distributedKey);
        $indexObject = $config->getDistributedIndexObject();
        $indexModel = Model::factory($indexObject);
        $shard = $indexModel->query()
            ->filters([$this->bucketField=>$bucket->getId()])
            ->fields([$this->shardField])
            ->fetchOne();

        if(empty($shard)){
            return null;
        }
        return $shard;
    }
    /**
     * Get shards for list of objects
     * @param string $objectName
     * @param array $distributedKeys
     * @return array  [shard_id=>[key1,key2,key3], shard_id2=>[...]]
     * @throws \Exception
     */
    public function findObjectsShards(string $objectName, array $distributedKeys) : array
    {
        $config = Config::factory($objectName);
        $keyField = $config->getBucketMapperKey();
        $fieldObject = $config->getField($keyField);

        if ($fieldObject->isNumeric()) {
            $mapper = $this->getNumericMapper();
        } elseif ($fieldObject->isText(true)) {
            $mapper = $this->getStringMapper();
        }else{
            throw new Exception('Undefined key mapper for '.$objectName);
        }

        $indexObject = $config->getDistributedIndexObject();
        $indexModel = Model::factory($indexObject);

        $result = [];
        $search = [];

        foreach ($distributedKeys as $key)
        {
            $bucket = $mapper->keyToBucket($key);
            $search[$bucket->getId()][] = $key;
        }

        $shardData = $indexModel->query()
            ->filters([$this->bucketField=>array_keys($search)])
            ->fields([$this->shardField,$this->bucketField])
            ->fetchAll();

        if(empty($shardData)){
            return [];
        }

        foreach ($shardData as $row)
        {
            $shardId = $row[$this->shardField];
            $bucketId = $row[$this->bucketField];
            if(!isset($result[$shardId])){
                $result[$shardId] = [];
            }
            if(isset($search[$bucketId])){
                $result[$shardId]  = array_merge($result[$shardId],$search[$bucketId]);
            }
        }
        return $result;
    }
}