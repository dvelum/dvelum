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

use Dvelum\Orm;

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

        $tables = [];

        /*
         * forming result set
         */
        foreach ($names as $objectName)
        {
            $configObject = Orm\Record\Config::factory($objectName);
            $objectModel = Model::factory($objectName);
            $config =  $configObject->__toArray();
            $objectTable = $objectModel->table();
            $builder = Orm\Record\Builder::factory($objectName);

            $oModel = Model::factory($objectName);
            $oDb = $oModel->getDbConnection();
            $oDbConfig = $oDb->getConfig();

            $canConnect = true;

            $title = '';
            $saveHistory = true;
            $linkTitle = '';

            if(isset($config['title']) && !empty($config['title']))
                $title = $config['title'];

            if(isset($config['link_title']) && !empty($config['link_title']))
                $linkTitle = $config['link_title'];

            if(isset($config['save_history']) && !$config['save_history'])
                $saveHistory = false;

            $hasBroken = false;

            if(!empty($builder->getBrokenLinks()))
                $hasBroken = true;

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
                'validdb'=>$builder->validate(),
                'broken'=>$hasBroken,
                'db_host'=>$oDbConfig['host'] ,
                'db_name'=>$oDbConfig['dbname'],
                'locked'=>$config['locked'],
                'readonly'=>$config['readonly'],
                'can_connect'=>$canConnect,
                'primary_key'=>$configObject->getPrimaryKey(),
                'connection'=>$config['connection'],
                'distributed' => $configObject->isDistributed()
            ];
        }
        return $data;
    }

    public function getDetails($objectName)
    {
        $objectModel = Model::factory($objectName);
        $objectTable = $objectModel->table();

        $records = 0;
        $dataLength = 0;
        $indexLength=0;
        $size = 0;

        $oModel = Model::factory($objectName);
        $oDb = $oModel->getDbConnection();
        $tableInfo = [];

        if($oDb->getAdapter()->getPlatform()->getName() === 'MySQL')
        {
            $platformAdapter = '\\Dvelum\\Orm\\Stat\\'.$oDb->getAdapter()->getPlatform()->getName();

            if(class_exists($platformAdapter)){
                $adapter = new $platformAdapter();
                $tableData = $adapter->getTablesInfo($oModel , $objectTable);
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
        }

        if(!empty($tableInfo))
        {
            $records = $tableInfo['rows'];
            $dataLength = \Utils::formatFileSize($tableInfo['data_length']);
            $indexLength = \Utils::formatFileSize($tableInfo['index_length']);
            $size = \Utils::formatFileSize($tableInfo['data_length'] + $tableInfo['index_length']);
        }

        $data = [[
            'name' => $objectTable,
            'records'=>number_format($records,0,'.',' '),
            'data_size'=>$dataLength,
            'index_size'=>$indexLength,
            'size'=>$size,
        ]];
        return $data;
    }
}