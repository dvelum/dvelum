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
        $manager = new Orm\Object\Manager();

        $names = $manager->getRegisteredObjects();

        if(empty($names))
            return [];

        $tables = [];

        /*
         * forming result set
         */
        foreach ($names as $objectName)
        {
            $configObject = Orm\Object\Config::factory($objectName);
            $objectModel = Model::factory($objectName);
            $config =  $configObject->__toArray();
            $objectTable = $objectModel->table();
            $builder = Orm\Object\Builder::factory($objectName);

            $records = 0;
            $dataLength = 0;
            $indexLength=0;
            $size = 0;

            $oModel = Model::factory($objectName);
            $oDb = $oModel->getDbConnection();
            $oDbConfig = $oDb->getConfig();
            $oDbHash = md5(serialize($oDbConfig));

            $canConnect = true;

            if(!isset($tables[$oDbHash]) && $oDb->getAdapter()->getPlatform()->getName() === 'MySQL')
            {
                $platformAdapter = '\\Dvelum\\Orm\\Stat\\'.$oDb->getAdapter()->getPlatform()->getName();

                if(class_exists($platformAdapter)){
                    $adapter = new $platformAdapter();
                    $tablesData = $adapter->getTablesInfo($oModel);
                }

                if(!empty($tablesData))
                {
                    foreach ($tablesData as $k=>$v)
                    {
                        $tables[$oDbHash][$v['Name']] = [
                            'rows'=>$v['Rows'],
                            'data_length'=>$v['Data_length'],
                            'index_length'=>$v['Index_length']
                        ];
                    }
                }
                unset($tablesData);
            }

            if(isset($tables[$oDbHash][$objectTable]))
            {
                $records = $tables[$oDbHash][$objectTable]['rows'];
                $dataLength = \Utils::formatFileSize($tables[$oDbHash][$objectTable]['data_length']);
                $indexLength = \Utils::formatFileSize($tables[$oDbHash][$objectTable]['index_length']);
                $size = \Utils::formatFileSize($tables[$oDbHash][$objectTable]['data_length'] + $tables[$oDbHash][$objectTable]['index_length']);
            }

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
                'records'=>number_format($records,0,'.',' '),
                'title'=>$title,
                'link_title'=>$linkTitle,
                'rev_control'=>$config['rev_control'],
                'save_history'=>$saveHistory,
                'data_size'=>$dataLength,
                'index_size'=>$indexLength,
                'size'=>$size,
                'system'=>$configObject->isSystem(),
                'validdb'=>$builder->validate(),
                'broken'=>$hasBroken,
                'db_host'=>$oDbConfig['host'] ,
                'db_name'=>$oDbConfig['dbname'],
                'locked'=>$config['locked'],
                'readonly'=>$config['readonly'],
                'can_connect'=>$canConnect,
                'primary_key'=>$configObject->getPrimaryKey(),
                'connection'=>$config['connection']
            ];
        }
        return $data;
    }
}