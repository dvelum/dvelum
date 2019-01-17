<?php
/*
* DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
* Copyright (C) 2011-2013  Kirill A Egorov
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * File adapter for Designer_Storage
 * @author Kirill A Egorov 2012
 */
class Designer_Storage_Adapter_File extends Designer_Storage_Adapter_Abstract
{
    protected $_configPath = '';
    protected $dirPostfix = '.files';

    protected $exportPath;

    public function getContentDir($projectFilePath)
    {
        return $projectFilePath . $this->dirPostfix . '/';
    }

    /**
     * @param array $config , optional
     */
    public function __construct($config = false)
    {
        parent::__construct($config);
        if ($config)
            $this->setConfigsPath($config->get('configs'));
    }

    /**
     * (non-PHPdoc)
     * @see Designer_Storage_Adapter_Abstract::load()
     */
    public function load($id)
    {
        if (!is_file($id))
            throw new Exception('Invalid file path' . $id);
        return $this->_unpack(file_get_contents($id));
    }

    /**
     * (non-PHPdoc)
     * @see Designer_Storage_Adapter_Abstract::save()
     */
    public function save($id, Designer_Project $obj, $export = false)
    {
        $obj->resortItems();
        $result = @file_put_contents($id, $this->_pack($obj));

        if ($result == false) {
            $this->_errors[] = 'write: ' . $id;
            return false;
        }

        if ($export)
            return $this->export($id, $obj);
        else
            return true;
    }

    /**
     * (non-PHPdoc)
     * @see Designer_Storage_Adapter_Abstract::delete()
     */
    public function delete($id)
    {
        $id = $this->_configPath . $id;
        return unlink($id);
    }

    /**
     * Set path to configs directory
     * @param string $path
     */
    public function setConfigsPath($path)
    {
        $this->_configPath = $path;
    }

    /**
     * Export project data for VCS
     * @param $file - project file path
     * @param Designer_Project $project
     */
    protected function export($file, Designer_Project $project)
    {
        $this->_errors = array();

        $this->exportPath = $this->getContentDir($file);

        if (!is_dir($this->exportPath)) {
            if (!@mkdir($this->exportPath, 0775)) {
                return false;
            }
        } else {
            File::rmdirRecursive($this->exportPath);
        }

        /*
        $dump = array(
            'file' => $file,
            'checksum' => md5_file($file),
            'date' => date('Y-m-d H:i:s'),
            'dump' => $project
        );

        if(!Utils::exportArray($projectPath.'dump.php' , $dump)) {
            $this->_errors[] = 'write: '.$projectPath.'config.php';
            return false;
        }
        */
        // Export ActionJs
        if (@file_put_contents($this->exportPath . 'ActionJS.js', $project->getActionJs()) === false) {
            $this->_errors[] = 'write: ' . $this->exportPath . 'ActionJS.js';
            return false;
        }
        // Export Project config
        if (!Utils::exportArray($this->exportPath . '__config.php', $project->getConfig())) {
            $this->_errors[] = 'write: ' . $this->exportPath . '__config.php';
            return false;
        }
        // Export Project events
        $events = $this->exportEvents($project);

        if ($events === false)
            return false;

        if (!Utils::exportArray($this->exportPath . '__events.php', $events)) {
            $this->_errors[] = 'write: ' . $this->exportPath . '__events.php';
            return false;
        }
        // Export Project methods
        $methods = $this->exportMethods($project);

        if ($methods === false)
            return false;

        if (!Utils::exportArray($this->exportPath . '__methods.php', $methods)) {
            $this->_errors[] = 'write: ' . $this->exportPath . '__methods.php';
            return false;
        }

        // Export Project Tree
        $tree = $this->parseTree($project);
        if ($tree === false) {
            return false;
        }

        if (!Utils::exportArray($this->exportPath . '__tree.php', $tree)) {
            $this->_errors[] = 'write: ' . $this->exportPath . '__tree.php';
            return false;
        }

        $instances = $this->exportInstances($project);
        if (!Utils::exportArray($this->exportPath . '__instances.php', $instances)) {
            $this->_errors[] = 'write: ' . $this->exportPath . '__instances.php';
            return false;
        }

        return true;
    }

    /**
     * Create project items array
     * @param Designer_Project $project
     */
    protected function parseTree(Designer_Project $project)
    {
        $result = [];
        $items = $project->getTree()->getItems();
        $items = Utils::sortByField($items, 'parent');
        foreach ($items as $v) {
            $exportedObject = $this->exportObject($v['id'], $v['data']);

            if ($exportedObject === false) {
                return false;
            }

            $v['data'] = $exportedObject;
            $result[$v['id']] = $v;
        }
        unset($v);

        return $result;
    }

    /**
     * Export designer object into config
     * @param string $id
     * @param mixed $object
     * @return string
     */
    protected function exportObject($id, $object)
    {
        $objectFile = $this->exportPath . $id . '.config.php';

        $config = [
            'id' => $id,
            'class' => get_class($object)
        ];

        if ($object instanceof Ext_Object) {
            $config['extClass'] = $object->getClass();
            $config['name'] = $object->getName();
            $config['state'] = $object->getState();
        } elseif ($object instanceof Ext_Exportable) {
            $config['state'] = $object->getState();
        }

        if (!Utils::exportArray($objectFile, $config)) {
            $this->_errors[] = 'write: ' . $objectFile;
            return false;
        }

        return $id . '.config.php';
    }

    /**
     * Export project events
     */
    protected function exportEvents(Designer_Project $project)
    {
        $eventManager = $project->getEventManager();
        $list = $eventManager->getEvents();
        $eventsIndex = array();

        foreach ($list as $object => $events) {
            if (empty($events)) {
                continue;
            }

            foreach ($events as $name => &$data) {
                if (!empty($data['code'])) {
                    $eventFile = $this->exportPath . $object . '.events.' . $name . '.js';
                    if (!@file_put_contents($eventFile, $data['code'])) {
                        $this->_errors[] = 'write: ' . $eventFile;
                        return false;
                    }
                    $data['code'] = $object . '.events.' . $name . '.js';
                } else {
                    $data['code'] = false;
                }
            }
            $listFile = $this->exportPath . $object . '.events.php';
            if (!Utils::exportArray($listFile, $events)) {
                $this->_errors[] = 'write: ' . $eventFile;
                return false;
            }
            $eventsIndex[$object] = $object . '.events.php';
        }
        return $eventsIndex;
    }

    /**
     * Export project events
     */
    protected function exportMethods(Designer_Project $project)
    {
        $methodManager = $project->getMethodManager();
        $list = $methodManager->getMethods();
        $methodIndex = array();

        foreach ($list as $object => $methods) {
            if (empty($methods)) {
                continue;
            }
            $methodList = [];
            foreach ($methods as $name => $item) {
                if (!$item instanceof Designer_Project_Methods_Item) {
                    continue;
                }

                $data = array(
                    'name' => $item->getName(),
                    'code' => $item->getCode(),
                    'description' => $item->getDescription(),
                    'params' => $item->getParams(),
                );

                if (!empty($data['code'])) {
                    $eventFile = $this->exportPath . $object . '.methods.' . $name . '.js';
                    if (!@file_put_contents($eventFile, $data['code'])) {
                        $this->_errors[] = 'write: ' . $eventFile;
                        return false;
                    }
                    $data['code'] = $object . '.methods.' . $name . '.js';
                } else {
                    $data['code'] = false;
                }
                $methodList[] = $data;
            }
            $listFile = $this->exportPath . $object . '.methods.php';
            if (!Utils::exportArray($listFile, $methodList)) {
                $this->_errors[] = 'write: ' . $eventFile;
                return false;
            }
            $methodIndex[$object] = $object . '.methods.php';
        }
        return $methodIndex;
    }

    /**
     * Get object instances list
     * @param Designer_Project $project
     * @return array
     */
    public function exportInstances(Designer_Project $project)
    {
        $instances = [];
        $items = $project->getTree()->getItems();

        foreach ($items as $item) {
            if (!$item['data'] instanceof Ext_Object_Instance) {
                continue;
            }
            /**
             * @var Ext_Object_instance $item
             */
            $instances[] = ['id' => $item['id'], 'name' => $item['data']->getName(), 'object' => $item['data']->getObject()->getName()];
        }
        return $instances;
    }

    /**
     * Import project from content dir
     * @param string $file
     * @return Designer_Project | false
     */
    public function import($file)
    {
        $this->exportPath = $this->getContentDir($file);
        $baseFiles = array('__config.php', '__tree.php', '__events.php', '__methods.php', '__instances.php');

        // check base files
        foreach ($baseFiles as $file) {
            if (!file_exists($this->exportPath . $file)) {
                return false;
            }
        }

        $config = require $this->exportPath . '__config.php';
        $project = new Designer_Project();
        $project->setConfig($config);

        $project->setActionJs(@file_get_contents($this->exportPath . 'ActionJS.js'));

        $treeData = require $this->exportPath . '__tree.php';
        $this->importTree($project, $treeData);

        $instances = require $this->exportPath . '__instances.php';
        $this->importInstances($project, $instances);

        $events = require $this->exportPath . '__events.php';
        $this->importEvents($project, $events);

        $methods = require $this->exportPath . '__methods.php';
        $this->importMethods($project, $methods);

        return $project;
    }

    /**
     * Restore project Tree from config
     * @param Designer_Project $project
     * @param $data
     */
    protected function importTree(Designer_Project $project, $data)
    {
        $tree = $project->getTree();
        foreach ($data as $id => $v) {
            $cfg = require $this->exportPath . $v['data'];

            if ($cfg['class'] == 'Designer_Project_Container') {
                $o = new Designer_Project_Container($id);
            } else {
                //$o = new $v['class']($v['name']);
                $o = Ext_Factory::object($cfg['extClass']);
                $o->setState($cfg['state']);
                $o->setName($cfg['name']);
            }
            $tree->addItem($v['id'], $v['parent'], $o, $v['order']);
        }

        $tree->sortItems();
    }

    /**
     * Restore project methods from config
     * @param Designer_Project $project
     * @param array $methods
     */
    protected function importMethods(Designer_Project $project, array $methods)
    {
        $methodManager = $project->getMethodManager();
        foreach ($methods as $object => $configFile) {
            $methodData = require $this->exportPath . $configFile;

            foreach ($methodData as $methodItem) {
                $code = '';
                if (!empty($methodItem['code'])) {
                    $code = @file_get_contents($this->exportPath . $methodItem['code']);
                }

                $m = $methodManager->addMethod($object, $methodItem['name'], $methodItem['params'], $code);
                $m->setDescription($methodItem['description']);
            }
        }
    }

    /**
     * Restore project events from config
     * @param Designer_Project $project
     * @param array $events
     */
    protected function importEvents(Designer_Project $project, array $events)
    {
        $eventManager = $project->getEventManager();
        foreach ($events as $object => $configFile) {
            $objectEvents = require $this->exportPath . $configFile;
            foreach ($objectEvents as $name => $data) {
                $code = '';
                if (!empty($data['code'])) {
                    $code = @file_get_contents($this->exportPath . $data['code']);
                }
                $eventManager->setEvent($object, $name, $code, $data['params'], $data['is_local']);
            }
        }
    }

    /**
     * Restore object instances
     * @param Designer_Project $project
     * @param array $instances
     * @throw Exception
     */
    protected function importInstances(Designer_Project $project, array $instances)
    {
        foreach ($instances as $v) {
            if (!$project->objectExists($v['id'])) {
                throw new Exception('Broken component tree. Undefined instance object ' . $v['id']);
            }
            if (!$project->objectExists($v['object'])) {
                throw new Exception('Broken component tree. Undefined component object ' . $v['object'] . ' as instance ' . $v['id']);
            }
            /**
             * @var Ext_Object_Instance $src
             */
            $src = $project->getObject($v['id']);

            if (!$src->isInstance()) {
                throw new Exception('Broken component tree. Object ' . $v['name'] . ' is not instance of Ext_Object_Instance');
            }
            $src->setObject($project->getObject($v['object']));
        }
    }
}