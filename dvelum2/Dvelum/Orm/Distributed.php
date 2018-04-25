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

use Dvelum\Orm\Distributed\Key\GeneratorInterface;
use Dvelum\Orm\Distributed\Key\Reserved;
use Dvelum\Orm\Distributed\Router;
use Dvelum\Utils;
use Dvelum\Config;

class Distributed
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
     * Weight map for fast shard random selection
     * @var array
     */
    protected $weightMap;


    protected $router;

    /**
     * Factory method
     * @return Distributed
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
        $adapterClass = $this->config->get('key_generator');
        $this->keyGenerator = new $adapterClass($this->config);
        $this->router = Router::factory();

        $this->shards = Utils::rekey(
            'id',
            Config::storage()->get(
                $this->config->get('shards') ,  false, false
            )->__toArray()
        );

        $this->weightMap = [];
        foreach ($this->shards as $index=>$data){
            $this->weightMap =  array_merge(array_fill(0, $data['weight'], (string) $index), $this->weightMap);
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
        if($this->router->hasRoutes($record->getName())){
            $shard = $this->router->findShard($record);
        }

        if(empty($shard)){
            $shard = $this->randomShard();
        }

        return $this->keyGenerator->reserveIndex($record, $shard);
    }

    /**
     * Delete reserved index
     * @param Record $record
     * @param $indexId
     * @return bool
     */
    public function deleteIndex(Record $record, $indexId) : bool
    {
        return $this->keyGenerator->deleteIndex($record, $indexId);
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
     * Get shards info
     * @return array
     */
    public function getShards() : array
    {
        return $this->shards;
    }

    /**
     * Get object field with shard id
     * @return string
     */
    public function getShardField() : string
    {
        return $this->config->get('shard_field');
    }

    /**
     * Get random shard from list using weight
     */
    public function randomShard() : string
    {
       return $this->weightMap[array_rand($this->weightMap)];
    }
}