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

use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\{
    Object, Model, Exception
};

use Dvelum\Db;
use Dvelum\Utils;
use Dvelum\Config;

class Orm
{
    protected $configObjects = [];
    protected $configFiles = [];
    protected $models = [];
    /**
     * @var ConfigInterface
     */
    protected $configSettings;
    /**
     * @var ConfigInterface
     */
    protected $modelSettings;

    public function init(ConfigInterface $config, Db\ManagerInterface $dbManager, string $language, \Cache_Interface $cache = null)
    {
        $eventManager = new \Eventmanager();

        if ($cache) {
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

        $this->modelSettings = Config\Factory::create([
            'hardCacheTime' => $config->get('hardcache'),
            'dataCache' => $cache,
            'dbObjectStore' => $objectStore,
            'defaultDbManager' => $dbManager,
            'errorLog' => false,
        ]);

        /*
         * Prepare Db_Object
         */
        $translator = new Orm\Object\Config\Translator($language . '/objects.php');

        Orm\Object\Builder::useForeignKeys($config->get('foreign_keys'));

        $this->configSettings = Config\Factory::create([
            'configPath' => $config->get('object_configs'),
            'translator' => $translator,
            'useForeignKeys' => $config->get('foreign_keys')
        ]);

        if ($config->get('db_object_error_log')) {
            $log = new Log\File($config->get('db_object_error_log_path'));
            /*
             * Switch to Db_Object error log
             */
            if (!empty($config->get('error_log_object'))) {
                $errorModel = $this->model($config->get('error_log_object'));
                $errorTable = $errorModel->table();
                $errorDb = $errorModel->getDbConnection();

                $logOrmDb = new Log\Db('db_object_error_log', $errorDb, $errorTable);
                $logModelDb = new Log\Db('model', $errorDb, $errorTable);
                $this->modelSettings->set('defaultLog', new Log\Mixed($log, $logModelDb));
                $objectStore->setLog($logOrmDb);
            } else {
                $this->modelSettings->set('defaultLog', $log);
                $objectStore->setLog($log);
            }
        }
    }

    /**
     * Factory method of object creation is preferable to use, cf. method  __construct() description
     * @param string $name
     * @param int|int[]|bool $id , optional default false
     * @throws Exception
     * @return Orm\Object|Orm\Object[]
     */
    public function object(string $name, $id = false)
    {
        if (!is_array($id)) {
            return new Object($name, $id);
        }

        $list = [];

        $model = $this->model($name);
        $config = $this->config($name);

        $data = $model->getItems($id);

        /*
         * Load links info
         */
        $links = $config->getLinks([Object\Config::LINK_OBJECT_LIST]);
        $linksData = [];

        if (!empty($links)) {
            foreach ($links as $object => $fields) {
                foreach ($fields as $field => $linkType) {
                    $fieldObject = $config->getField($field);
                    if ($fieldObject->isManyToManyLink()) {
                        $relationsObject = $config->getRelationsObject($field);
                        $relationsData = Model::factory($relationsObject)->getList([
                            'sort' => 'order_no',
                            'dir' => 'ASC'
                        ], ['sourceid' => $id], ['targetid', 'sourceid']);
                    } else {
                        $linkedObject = $fieldObject->getLinkedObject();
                        $linksObject = Model::factory($linkedObject)->getStore()->getLinksObjectName();
                        $linksModel = Model::factory($linksObject);
                        $relationsData = $linksModel->getList(['sort' => 'order', 'dir' => 'ASC'], [
                                'src' => $name,
                                'srcid' => $id,
                                'src_field' => $field,
                                'target' => $linkedObject
                            ], ['targetid', 'sourceid' => 'srcid']);
                    }
                    if (!empty($relationsData)) {
                        $linksData[$field] = Utils::groupByKey('sourceid', $relationsData);
                    }
                }
            }
        }

        $primaryKey = $config->getPrimaryKey();
        foreach ($data as $item) {
            $o = new Object($name);
            $o->disableAcl(true);
            /*
             * Apply links info
             */
            if (!empty($linksData)) {
                foreach ($linksData as $field => $source) {
                    if (isset($source[$item[$primaryKey]])) {
                        $item[$field] = Utils::fetchCol('targetid', $source[$item[$primaryKey]]);
                    }
                }
            }

            $o->setId($item[$primaryKey]);
            $o->setRawData($item);
            $list[$item[$primaryKey]] = $o;
            $o->disableAcl(false);
        }
        return $list;
    }

    /**
     * Instantiate data structure for the objects named $name
     * @param string $name - object name
     * @param boolean $force - reload config
     * @return Orm\Object\Config
     * @throws Exception
     */
    public function config(string $name, bool $force = false): Orm\Object\Config
    {
        $name = strtolower($name);

        if ($force || !isset($this->configObjects[$name])) {
            $this->configObjects[$name] = new Object\Config($name, $force, $this->configSettings);
        }

        return $this->configObjects[$name];
    }

    /**
     * Object config existence check
     * @param $name
     * @return bool
     */
    public function configExists($name): bool
    {
        $name = strtolower($name);

        if (isset($this->configObjects[$name]) || isset($this->configFiles[$name])) {
            return true;
        }

        $cfgPath = $this->configSettings->get('configPath');

        if (Config\Factory::storage()->exists($cfgPath . $name . '.php')) {
            $this->configFiles[$name] = $cfgPath . $name . '.php';
            return true;
        }

        return false;
    }

    /**
     * Get ORM Object Config settings
     * @return ConfigInterface
     */
    public function getConfigSettings(): ConfigInterface
    {
        return $this->configSettings;
    }

    /**
     * Get Orm Model Settings
     * @return ConfigInterface
     */
    public function getModelSettings() : ConfigInterface
    {
        return $this->modelSettings;
    }

    /**
     * Factory method of model instantiation
     * @param string $objectName — the name of the object in ORM
     * @return Model
     */
    public function model(string $objectName): Model
    {
        $listName = strtolower($objectName);

        if (isset($this->models[$listName])) {
            return $this->models[$listName];
        }

        $objectName = implode('_', array_map('ucfirst', explode('_', $objectName)));

        /*
         * Instantiate real or virtual model
         */
        if (class_exists('Model_' . $objectName)) {
            $class = 'Model_' . $objectName;
            $this->models[$listName] = new $class($objectName, $this->modelSettings);
        } else {
            $this->models[$listName] = new Model($objectName, $this->modelSettings);
        }
        return $this->models[$listName];
    }
}