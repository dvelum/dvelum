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

namespace  Dvelum\Db;

use Dvelum\Orm\Distributed;

class OrmManager extends Manager
{

    /**
     * Get Database connection
     * @param string $name
     * @param null|string $workMode
     * @param null|string $shard
     * @return Adapter
     * @throws \Exception
     */
    public function getDbConnection(string $name, ?string $workMode = null, ?string $shard = null) : Adapter
    {
        if(empty($workMode)){
            $workMode = $this->appConfig->get('development');
        }

        if(empty($shard)){
            $shardKey = '1';
        }else{
            $shardKey = $shard;
        }

        if(!isset($this->dbConnections[$workMode][$name][$shardKey]))
        {
            $cfg = $this->getDbConfig($name);

            $cfg->set('driver', $cfg->get('adapter'));
            /*
             * Enable Db profiler for development mode Attention! Db Profiler causes
             * memory leaks at background tasks. (Dev mode)
             */
            if($this->appConfig->get('development') && $this->appConfig->offsetExists('use_db_profiler') && $this->appConfig->get('use_db_profiler')){
                $cfg->set('profiler' , true);
            }

            if(!empty($shard))
            {
                $sharding = Distributed::factory();
                $shardInfo = $sharding->getShardInfo($shard);
                $cfg->set('host', $shardInfo['host']);
                if(isset($shardInfo['override']) && !empty($shardInfo['override'])){
                    foreach ($shardInfo['override'] as $k=>$v){
                        $cfg->set($k,$v);
                    }
                }
            }
            $db = $this->initConnection($cfg->__toArray());
            $this->dbConnections[$workMode][$name][$shardKey] = $db;
        }
        return $this->dbConnections[$workMode][$name][$shardKey];
    }
}