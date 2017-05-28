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

namespace Dvelum;

use Dvelum\Orm\Exception;
use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Model;
use Dvelum\Db;

class Orm
{
    static public function init(ConfigInterface $config, Db\ManagerInterface $dbManager, string $language, \Cache_Interface $cache = null)
    {
        $eventManager = new \Eventmanager();

        if($cache){
            $eventManager->setCache($cache);
        }

        /*
         * Prepare Db object storage
         */
        $objectStore = new Orm\Object\Store(array(
            'linksObject' => $config->get('orm_links_object'),
            'historyObject' => $config->get('orm_history_object'),
            'versionObject' => $config->get('orm_version_object'),
        ));
        $objectStore->setEventManager($eventManager);

        /*
         * Prepare models
         */
        Model::setDefaults(array(
            'hardCacheTime'  => $config->get('hardcache'),
            'dataCache' => $cache  ,
            'dbObjectStore'  => $objectStore,
            'defaultDbManager' => $dbManager,
            'errorLog' => false
        ));

        /*
         * Prepare Db_Object
         */
        $translator = new Orm\Object\Config\Translator($language . '/objects.php');

        Orm\Object\Config::setConfigPath($config->get('object_configs'));
        Orm\Object\Config::setTranslator($translator);
        Orm\Object\Builder::useForeignKeys($config->get('foreign_keys'));

        if($config->get('db_object_error_log'))
        {
            $log = new Log\File($config->get('db_object_error_log_path'));
            /*
             * Switch to Db_Object error log
             */
            if(!empty($config->get('error_log_object')))
            {
                $errorModel = Model::factory($config->get('error_log_object'));
                $errorTable = $errorModel->table();
                $errorDb = $errorModel->getDbConnection();

                $logOrmDb = new Log\Db('db_object_error_log' , $errorDb , $errorTable);
                $logModelDb = new Log\Db('model' , $errorDb , $errorTable);
                Model::setDefaultLog(new Log\Mixed($log, $logModelDb));
                $objectStore->setLog($logOrmDb);
            }else{
                Model::setDefaultLog($log);
                $objectStore->setLog($log);
            }
        }
    }

    /**
     * Factory method of object creation is preferable to use, cf. method  __construct() description
     * @param string $name
     * @param int|int[]|bool $id, optional default false
     * @throws Exception
     * @return Orm\Object|Orm\Object[]
     */
    static public function object(string $name , $id = false)
    {
        return Orm\Object::factory($name , $id);
    }

    /**
     * Instantiate data structure for the objects named $name
     * @param string $name - object name
     * @param boolean $force - reload config
     * @return Orm\Object\Config
     * @throws Exception
     */
    static public function config(string $name , bool $force = false) : Orm\Object\Config
    {
        return Orm\Object\Config::factory($name, $force);
    }
}