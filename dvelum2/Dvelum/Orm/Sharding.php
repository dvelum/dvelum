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

namespace Dvelum\Orm;

use Dvelum\Orm\Sharding\Key\GeneratorInterface;
use Dvelum\Orm\Sharding\Key\Reserved;
use Dvelum\Utils;
use Dvelum\Config;

class Sharding
{
    static protected $instance = false;

    protected $config;

    protected $shards = [];

    /**
     * @var Model $shardModel
     */
    protected $shardModel;

    /**
     * @var GeneratorInterface $keyGenerator
     */
    protected $keyGenerator;

    /**
     * Factory method
     * @return Sharding
     */
    static public function factory() : self
    {
        if(!static::$instance){
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct()
    {
        $this->config = Config::storage()->get('sharding.php');
        $this->shardModel = Model::factory($this->config->get('shard_object'));
        $adapterClass = $this->get('key_generator');
        $this->keyGenerator = new $adapterClass($this->config);

        $list = $this->shardModel->query()->fetchAll();

        if(!empty($list)){
            $this->shards = Utils::rekey($this->shardModel->getPrimaryKey() , $list);
        }
    }

    /**
     * Get object shard id
     * @param string $objectName
     * @param mixed $objectId
     * @return mixed
     */
    public function getObjectShard(string $objectName, $objectId)
    {
        return $this->keyGenerator->getObjectShard($objectName, $objectId);
    }

    /**
     * @param $objectName
     * @param array $objectId
     * @return array  [ [shard_id=>[itemId1,itemId2]] ]
     * @todo  write method
     */
    public function getObjectsShards(string $objectName, array $objectId) : array
    {
        return $this->keyGenerator->getObjectsShards($objectName, $objectId);
    }


    /**
     * Reserve object id, add to routing table
     * @param $record
     * @return ?Reserved
     */
    public function reserveIndex(Record $record) : ?Reserved
    {
        return $this->keyGenerator->reserveIndex($record);
    }

    /**
     * Get shard info by id
     * @param $id
     * @return array|bool
     */
    public function getShardInfo($id)
    {
        if(isset($this->shards[$id])){
            return $this->shards[$id];
        }else{
            return false;
        }
    }

    /**
     * Get object field with shard id
     * @return string
     */
    public function getShardField() : string
    {
        return $this->config->get('shard_field');
    }
}