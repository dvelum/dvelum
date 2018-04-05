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

namespace Dvelum\Orm\Sharding\Key;

use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Model;
use Dvelum\Orm\Sharding;
use Dvelum\Utils;
use Dvelum\Config;
use Dvelum\Orm\Record;
use \Exception;

class OrmIndex implements GeneratorInterface
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;
    protected $shardField;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->shardField =  Sharding::factory()->getShardField();
    }

    /**
     * Reserve object id, add to routing table
     * @param Record $object
     * @return ?Reserved
     */
    public function reserveIndex(Record $object) : ?Reserved
    {
         $shard = false;
         $objectConfig = $object->getConfig();

        /**
         * @todo DETECT SHARD
         */


        $indexObject = $objectConfig->getDistributedIndexObject();
        $model = Model::factory($indexObject);
        $db = $model->getDbConnection();

        try{
            $db->beginTransaction();
            $db->insert(
                $model->table(),
                [
                    $this->shardField => $shard
                ]
            );
            $id = $db->lastInsertId($model->table(),$objectConfig->getPrimaryKey());
            $db->commit();

            $result = new Reserved();
            $result->setId($id);
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
     * @param mixed $objectId
     * @return mixed
     */
    public function getObjectShard(string $objectName, $objectId)
    {
        $objectConfig = Record\Config::factory($objectName);
        $indexObject = $objectConfig->getDistributedIndexObject();

        $model = Model::factory($indexObject);
        $shardData = $model->query()->filters([$objectConfig->getPrimaryKey(), $objectId])->fetchRow();

        if(empty($shardData)){
            return false;
        }

        return $shardData[$this->shardField];
    }
}