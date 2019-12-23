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

use Dvelum\Db\Adapter;
use Dvelum\Lang;
use Dvelum\Orm;
use Dvelum\Service;
use Dvelum\Utils;

class Stat
{
    /**
     * Get orm objects statistics
     * @return array
     */
    public function getInfo() : array
    {
        $data = [];

        /*
         * Getting list of objects
         */
        $manager = new Orm\Record\Manager();

        $names = $manager->getRegisteredObjects();

        if(empty($names))
            return [];

        /*
         * forming result set
         */
        foreach ($names as $objectName)
        {
            $configObject = Orm\Record\Config::factory($objectName);
            $objectModel = Model::factory($objectName);
            $config =  $configObject->__toArray();
            $objectTable = $objectModel->table();


            $oModel = Model::factory($objectName);
            $oDb = $oModel->getDbConnection();
            $oDbConfig = $oDb->getConfig();


            $title = '';
            $saveHistory = true;
            $linkTitle = '';

            if(isset($config['title']) && !empty($config['title']))
                $title = $config['title'];

            if(isset($config['link_title']) && !empty($config['link_title']))
                $linkTitle = $config['link_title'];

            if(isset($config['save_history']) && !$config['save_history'])
                $saveHistory = false;

            $data[] = [
                'name'=>$objectName,
                'table'=>$objectTable,
                'engine'=>$config['engine'],
                'vc'=>$config['rev_control'],
                'fields'=>sizeof($config['fields']),

                'title'=>$title,
                'link_title'=>$linkTitle,
                'rev_control'=>$config['rev_control'],
                'save_history'=>$saveHistory,

                'system'=>$configObject->isSystem(),

                'db_host'=>$oDbConfig['host'] ,
                'db_name'=>$oDbConfig['dbname'],
                'locked'=>$config['locked'],
                'readonly'=>$config['readonly'],
                'primary_key'=>$configObject->getPrimaryKey(),
                'connection'=>$config['connection'],
                'distributed' => $configObject->isDistributed(),
                'external' => '' /* @todo check external */
            ];
        }
        return $data;
    }

    public function getDetails($objectName, ?Adapter $db = null) : array
    {
        $objectModel = Model::factory($objectName);
        if(empty($db)){
            $db = $objectModel->getDbConnection();
        }
        $data = $this->getTableInfo($objectName, $db);
        return [$data];
    }

    protected function getTableInfo($objectName, Adapter $db)
    {
        $objectModel = Model::factory($objectName);
        $objectTable = $objectModel->table();

        $records = 0;
        $dataLength = 0;
        $indexLength=0;
        $size = 0;

        $tableInfo = [
            'rows' => [],
            'data_length' => null,
            'index_length' => null
        ];

        $data = [];

        if($db->getAdapter()->getPlatform()->getName() === 'MySQL')
        {
            $platformAdapter = '\\Dvelum\\Orm\\Stat\\'.$db->getAdapter()->getPlatform()->getName();

            if(class_exists($platformAdapter)){
                $adapter = new $platformAdapter();
                $tableData = $adapter->getTablesInfo($db, $objectTable);
            }

            if(!empty($tableData))
            {
                $tableInfo = [
                    'rows'=>$tableData['Rows'],
                    'data_length'=>$tableData['Data_length'],
                    'index_length'=>$tableData['Index_length']
                ];
            }
            unset($tableData);

            if(!empty($tableInfo))
            {
                $records = $tableInfo['rows'];
                $dataLength = Utils::formatFileSize($tableInfo['data_length']);
                $indexLength = Utils::formatFileSize($tableInfo['index_length']);
                $size = Utils::formatFileSize($tableInfo['data_length'] + $tableInfo['index_length']);
            }

            $data = [
                'name' => $objectTable,
                'records'=>number_format($records,0,'.',' '),
                'data_size'=>$dataLength,
                'index_size'=>$indexLength,
                'size'=>$size,
                'engine'=>$objectModel->getObjectConfig()->get('engine'),
                'external' => '' /* @todo check external */
            ];
        }

        return $data;
    }

    public function getDistributedDetails(string $objectName, ?string $shard = null) : array
    {
        $config = Orm\Record\Config::factory($objectName);
        if(!$config->isDistributed()){
            throw new Exception($objectName.' is not distributed');
        }
        $objectModel = Model::factory($objectName);
        $connectionName = $objectModel->getConnectionName();
        $sharding = Distributed::factory();
        $shards = $sharding->getShards();
        $table = $objectModel->table();
        $data = [];

        if(!empty($shards))
        {
            if(!empty($shard)){
                $shardInfo = $this->getTableInfo($objectName, $objectModel->getDbManager()->getDbConnection($connectionName,null, $shard));
                $shardInfo['name'] = $shard.' : '.$table;
                $data[] = $shardInfo;
            }else{
                foreach ($shards as $info)
                {
                    $shardInfo = $this->getTableInfo($objectName, $objectModel->getDbManager()->getDbConnection($connectionName,null, $info['id']));
                    $shardInfo['name'] = $info['id'].' : '.$table;
                    $data[] = $shardInfo;
                }
            }
        }
        return $data;
    }

    /**
     * Validate Db object
     * @param string $objectName
     * @return array
     * @throws \Exception
     */
    public function validate(string $objectName) : array
    {
        /**
         * @var Lang\Dictionary $lang
         */
        $lang = Service::get('lang')->getDictionary();
        $config = Record\Config::factory($objectName);
        $builder = Orm\Record\Builder::factory($objectName);

        $hasBroken = false;

        if($config->isDistributed()){
            $valid =  $builder->validateDistributedConfig();
        }else{
            $valid =  $builder->validate();
        }

        if(!empty($builder->getBrokenLinks()))
            $hasBroken = true;

        if($hasBroken || !$valid) {
            $group =  $lang->get('INVALID_STRUCTURE');
        }else{
            $group =  $lang->get('VALID_STRUCTURE');
        }
        $result = [
            'title' => $config->getTitle(),
            'name'  => $objectName,
            'validdb' => $valid,
            'broken' => $hasBroken,
            'locked' => $config->get('locked'),
            'readonly'  => $config->get('readonly'),
            'distributed' => $config->isDistributed(),
            'shard_title' => '-',
            'id' => $objectName
        ];
        return $result;
    }

    /**
     * @param string $objectName
     * @param string $shard
     * @return array
     */
    public function validateDistributed(string $objectName, string $shard) : array
    {
        /**
         * @var  Lang $lang
         */
        $lang = Service::get('lang')->getDictionary();
        $config = Record\Config::factory($objectName);
        $builder = Orm\Record\Builder::factory($objectName);
        $model = Model::factory($objectName);
        $connectionName = $model->getConnectionName();

        $sharding = Distributed::factory();
        $shards = $sharding->getShards();

        $result[] = $this->validate($objectName);

        foreach ($shards as $item)
        {
            if(strlen($shard) && $item['id']=!$shard){
                continue;
            }

            $hasBroken = false;
            $builder->setConnection($model->getDbManager()->getDbConnection($connectionName,null, (string) $item['id']));
            $valid = $builder->validate();

            if(!empty($builder->getBrokenLinks()))
                $hasBroken = true;

            /*
            if($hasBroken || !$valid) {
                $group =  $lang->get('INVALID_STRUCTURE');
            }else{
                $group =  $lang->get('VALID_STRUCTURE');
            }
            */

            $result[] = [
                'title' => $config->getTitle(),
                'name'  => $objectName,
                'validdb' => $valid,
                'broken' => $hasBroken,
                'locked' => $config->get('locked'),
                'readonly'  => $config->get('readonly'),
                'distributed' => $config->isDistributed(),
                'shard' => $item['id'],
                'shard_title' => $item['id'],
                'id' => $objectName . $item['id']
            ];
        }
        return $result;
    }
}