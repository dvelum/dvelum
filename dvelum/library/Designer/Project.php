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
 * Designer project class.
 * @author Kirill Egorov 2011
 * @package Designer
 */
class Designer_Project
{
    static protected $reservedNames = array('_Component_', '_Layout_');
    const COMPONENT_ROOT = '_Component_';
    const LAYOUT_ROOT = '_Layout_';

    protected static $_containers = array(
        'Panel',
        'Tabpanel',
        'Toolbar',
        'Form_Fieldset',
        'Form_Fieldcontainer',
        'Form_Checkboxgroup',
        'Form_Radiogroup',
        'Form',
        'Window',
        'Grid',
        'Docked',
        'Tree',
        'Menu',
        'Container',

        'Buttongroup',
        // menu containers
        'Button',
        'Button_Split',
        'Menu_Checkitem',
        'Menu_Item',
        'Menu_Separator'
    );

    public static $hasDocked = array(
        'Panel',
        'Tabpanel',
        'Form',
        'Window',
        'Grid',
        'Tree'
    );

    public static $hasMenu = array(
        'Button',
        'Button_Split',
        'Menu_Checkitem',
        'Menu_Item',
        'Menu_Separator'
    );

    public static $defines = array(
        'Window',
        'Model'
    )//'Form','Window','Store','Model' Designer_Project::$nonDraggable
    ;

    public static $configContainers = array(
        'Form',
        'Fieldcontainer',
        'Fieldset',
        'Window'
    );

    protected static $_nonDraggable = array(
        'Window',
        //'Store' ,
        'Model',
        //'Data_Store_Tree',
        //'Data_Store'
    );

    public static $storeClasses = array(
        'Data_Store',
        'Data_Store_Tree',
        'Data_Store_Buffered',
        'Store'
    );

    /**
     * Objects tree
     * @var Tree
     */
    protected $_tree;

    /**
     * Project config
     * @var array
     */
    protected $_config = array(
        'namespace' => 'appComponents',
        'runnamespace' => 'appApplication',
        'files' => array(),
        'langs' => array()
    );
    /**
     * Events Manager
     * @var Designer_Project_Events
     */
    protected $_eventManager = false;
    /**
     * Methods Manager
     * @var Designer_Project_Methods
     */
    protected $_methodManager = false;
    /**
     * JS Action Code
     * @var string
     */
    protected $_actionJs = '';

    public function __construct()
    {
        $this->_tree = new Tree();
        $this->initContainers();
    }

    /**
     * Init system layout
     */
    protected function initContainers()
    {
        $this->_tree->addItem(self::COMPONENT_ROOT, false, new Designer_Project_Container('Components'), -1000);
        $this->_tree->sortItems(self::COMPONENT_ROOT);

        $this->_tree->addItem(self::LAYOUT_ROOT, false, new Designer_Project_Container('Application'), -500);
        $this->_tree->sortItems(self::LAYOUT_ROOT);
    }

    /**
     * Check if object is Window comonent
     * @param string $class
     * @return boolean
     */
    static public function isWindowComponent($class)
    {
        if (strpos($class, 'Component_Window') !== false)
            return true;
        else
            return false;
    }

    /**
     * Check if object can has parent
     * @param string $class
     * @return boolean
     */
    static public function isDraggable($class)
    {
        if (in_array($class, self::$_nonDraggable, true) || self::isWindowComponent($class))
            return false;
        else
            return true;
    }

    /**
     * Check if object is container
     * @param string $class
     * @return boolean
     */
    static public function isContainer($class)
    {
        if (in_array($class, self::$_containers, true) || self::isWindowComponent($class))
            return true;
        else
            return false;
    }

    static public function isVisibleComponent($class)
    {
        if (in_array($class, self::$storeClasses, true) || $class == 'Model' && strpos($class, 'Data_') !== false) {
            return false;
        }
        return true;
    }

    /**
     * Add Ext_Object to the project
     * @param string $parent - parant object name or "0" for root
     * @param Ext_Object $object
     * @return boolean - success flag
     */
    public function addObject($parent, Ext_Object $object)
    {
        if (strlen($parent)) {
            if (in_array($object->getClass(), self::$_nonDraggable, true) && $parent !== Designer_Project::COMPONENT_ROOT) {
                $parent = Designer_Project::LAYOUT_ROOT;
                $object->isExtendedComponent();
            }

            if (!$this->objectExists($parent)) {
                $parent = Designer_Project::LAYOUT_ROOT;
                $object->isExtendedComponent();
            }
        }
        return $this->_tree->addItem($object->getName(), $parent, $object);
    }

    /**
     * Get project events Manager
     * @return Designer_Project_Events
     */
    public function getEventManager()
    {
        if ($this->_eventManager === false)
            $this->_eventManager = new Designer_Project_Events();
        return $this->_eventManager;
    }

    /**
     * Get project methods Manager
     * @return Designer_Project_Methods
     */
    public function getMethodManager()
    {
        if ($this->_methodManager === false)
            $this->_methodManager = new Designer_Project_Methods();
        return $this->_methodManager;
    }

    /**
     * Remove object from project
     * @param string $name
     * @return boolean - success flag
     */
    public function removeObject($name)
    {
        $eventManager = $this->getEventManager();
        $methodsManager = $this->getMethodManager();

        $eventManager->removeObjectEvents($name);
        $methodsManager->removeObjectMethods($name);

        $childs = $this->_tree->getChildsR($name);

        if (!empty($childs)) {
            foreach ($childs as $id) {
                $eventManager->removeObjectEvents($id);
                $methodsManager->removeObjectMethods($id);
                $this->_tree->removeItem($id);
            }
        }
        return $this->_tree->removeItem($name);
    }

    /**
     * Replace object
     * @param string $name - old object name
     * @param Ext_Object $newObject
     */
    public function replaceObject($name, Ext_Object $newObject)
    {
        $this->_tree->updateItem($name, $newObject);
    }

    /**
     * Change object parent
     * @param string $name - object name
     * @param string $newParent - new parent object name
     * @return boolean - success flag
     */
    public function changeParent($name, $newParent)
    {
        return $this->_tree->changeParent($name, $newParent);
    }

    /**
     * Get project config
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Set project config options
     * @param array $config
     */
    public function setConfig(array $config)
    {
        foreach ($config as $name => $value) {
            $this->_config[$name] = $value;
        }
    }

    public function __get($name)
    {
        if (!isset($this->_config[$name]))
            trigger_error('Invalid config property requested');
        return $this->_config[$name];
    }

    public function __isset($name)
    {
        return isset($this->_config[$name]);
    }

    public function __set($name, $value)
    {
        $this->_config[$name] = $value;
    }

    /**
     * Set item order
     * @param mixed $id
     * @param integer $order
     * @return boolean - success flag
     */
    public function setItemOrder($id, $order)
    {
        return $this->_tree->setItemOrder($id, $order);
    }

    /**
     * Resort tree Items
     * @param mixed $parentId - optional, resort only item childs
     * default - false (resort all items)
     */
    public function resortItems($parentId = false)
    {
        $this->_tree->sortItems($parentId);
    }

    /**
     * Check if object exists
     * @param string $name
     * @return boolean
     */
    public function objectExists($name)
    {
        return $this->_tree->itemExists($name);
    }

    /**
     * Get all objects from project tree
     * @return array;  object indexed by name
     */
    public function getObjects()
    {
        $items = $this->_tree->getItems();
        $data = array();
        if (!empty($items))
            foreach ($items as $config)
                $data[$config['id']] = $config['data'];
        return $data;
    }

    /**
     * Get extended components
     * @return array
     */
    public function getComponents()
    {
        $data = array();
        if ($this->_tree->hasChilds(self::COMPONENT_ROOT)) {
            $childs = $this->_tree->getChilds(self::COMPONENT_ROOT);
            foreach ($childs as $v) {
                $data[$v['id']] = $v['data'];
            }
        }
        return $data;
    }

    /**
     * Get objects tree
     * @return Tree
     */
    public function getTree()
    {
        return $this->_tree;
    }

    /**
     * Get object by name
     * @param string $name
     * @return Ext_Object
     */
    public function getObject($name)
    {
        $objData = $this->_tree->getItem($name);
        return $objData['data'];
    }

    /**
     * Get list of Store objects
     * @return array
     */
    public function getStores($treeStores = true)
    {
        $list = $this->getObjectsByClass(['Store', 'Data_Store', 'Data_Store_Tree', 'Data_Store_Buffered', 'Object_Instance']);

        foreach ($list as $k => $v) {
            if ($v->isInstance() && !in_array($v->getObject()->getClass(),
                    ['Store', 'Data_Store', 'Data_Store_Tree', 'Data_Store_Buffered'], true)) {
                unset($list[$k]);
            }
        }
        return $list;
    }

    /**
     * Get list of Model objects
     * @return array
     */
    public function getModels()
    {
        return $this->getObjectsByClass('Model');
    }

    /**
     * Get list of Menu objects
     * @return array
     */
    public function getMenu()
    {
        return $this->getObjectsByClass('Menu');
    }

    /**
     * Get list of Grid objects
     * @return array
     */
    public function getGrids()
    {
        return $this->getObjectsByClass('Grid');
    }

    /**
     * Get objects by class
     * @param string|array $class
     * @return array, indexed by object name
     */
    public function getObjectsByClass($class)
    {
        if (!is_array($class))
            $class = array($class);

        $class = array_map('ucfirst', $class);

        $items = $this->_tree->getItems();

        if (empty($items))
            return array();

        $result = array();

        foreach ($items as $config) {
            if (in_array($config['data']->getClass(), $class, true)) {
                if ($config['parent'] == self::COMPONENT_ROOT && $config['data'] instanceof Ext_Object && !$config['data']->isInstance()) {
                    $config['data']->extendedComponent(true);
                }
                $result[$config['id']] = $config['data'];
            }
        }
        return $result;
    }

    /**
     * Check if object has childs.
     * @param string $name
     * @return boolean
     */
    public function hasChilds($name)
    {
        return $this->_tree->hasChilds($name);
    }

    /**
     * Check if object has instances
     * @param $name
     * @return bool
     */
    public function hasInstances($name)
    {
        $items = $this->getObjectsByClass('Object_Instance');
        if (!empty($items)) {
            foreach ($items as $object) {
                if ($object->getObject()->getName() == $name)
                    return true;
            }
        }
        return false;
    }

    /**
     * Get object childs
     * @param string $name
     * @return array
     */
    public function getChilds($name)
    {
        return $this->_tree->getChilds($name);
    }

    /**
     * Get parent object
     * @param string $name - object name
     * @return string | false
     */
    public function getParent($name)
    {
        $parentId = $this->_tree->getParentId($name);

        if ($parentId && $this->objectExists($parentId))
            return $parentId;
        else
            return false;
    }

    /**
     * Compile project js code
     * @param array $replace - optional
     * @return string
     */
    public function getCode($replace = array())
    {
        $codeGen = new Designer_Project_Code($this);
        if (!empty($replace))
            return Designer_Factory::replaceCodeTemplates($replace, $codeGen->getCode());
        else
            return $codeGen->getCode();
    }

    /**
     * Get object javascript source code
     * @param string $name
     * @param array $replace
     * @return string
     */
    public function getObjectCode($name, $replace = array())
    {
        $codeGen = new Designer_Project_Code($this);

        if (!empty($replace)) {
            $k = array();
            $v = array();
            foreach ($replace as $item) {
                $k[] = $item['tpl'];
                $v[] = $item['value'];
            }
            return str_replace($k, $v, $codeGen->getObjectCode($name));
        } else {
            return $codeGen->getObjectCode($name);
        }
    }

    /**
     * Check if item exists
     * @param $id
     * @return bool
     */
    public function itemExist($id)
    {
        return $this->_tree->itemExists($id);
    }

    /**
     * Get item data
     * @param mixed $id
     * @return array
     */
    public function getItemData($id)
    {
        return $this->_tree->getItemData($id);
    }

    /**
     * Get root panels list
     * @return array
     */
    public function getRootPanels()
    {
        $list = $this->_tree->getChilds('_Layout_');
        $names = [];

        if (empty($list))
            return [];

        foreach ($list as $v) {
            $object = $v['data'];
            $class = $object->getClass();

            if ($class === 'Object_Instance')
                $class = $object->getObject()->getClass();

            if (in_array($class, Designer_Project::$_containers, true) && $class !== 'Window' && $class != 'Menu' && !Designer_Project::isWindowComponent($class))
                $names[] = $object->getName();
        }
        return $names;
    }

    /**
     * Get Application ActionJs code
     * @return string
     */
    public function getActionJs()
    {
        return $this->_actionJs;
    }

    /**
     * Set Application ActionJs code
     * @param $code
     */
    public function setActionJs($code)
    {
        $this->_actionJs = $code;
    }

    /**
     * Create unique component id
     * @param string $prefix
     * @return string
     */
    public function uniqueId($prefix)
    {
        if (!$this->objectExists($prefix)) {
            return $prefix;
        }

        $postfix = 1;
        while ($this->objectExists($prefix . $postfix)) {
            $postfix++;
        }
        return $prefix . $postfix;
    }

    /**
     * Project converter 0.9.x to  1.x
     * @return boolean -  update flag
     */
    public function convertTo1x($jsPath)
    {
        if ($this->itemExist('_Component_'))
            return false;

        // migrate actionJS
        if (isset($this->_config['actionjs']) && !empty($this->_config['actionjs'])) {
            $jsFilePath = str_replace('./js/', $jsPath, $this->_config['actionjs']);
            if (file_exists($jsFilePath)) {
                $code = @file_get_contents($jsFilePath);
                $this->setActionJs($code);
                unset($this->_config['actionjs']);
            }
        }

        $this->initContainers();
        $items = $this->_tree->getChilds(0);
        $stores = $this->getStores();

        foreach ($stores as $id => $object) {
            $this->changeParent($id, 0);
        }

        foreach ($items as $cmpData) {
            /**
             * @var Ext_Object
             */
            $object = $cmpData['data'];

            if ($object instanceof Designer_Project_Container) {
                continue;
            }

            /*
             * Defined components without auto layout
             */
            if ($object->isExtendedComponent() /*&& $object->isValidProperty('definedOnly') && $object->defineOnly*/) {
                $this->_tree->changeParent($cmpData['id'], '_Component_');
                continue;
            }

            if ($object->isExtendedComponent()) {
                $this->_tree->changeParent($cmpData['id'], Designer_Project::COMPONENT_ROOT);

                if (strpos($object->getClass(), 'Window') === false) {
                    $objectInstance = Ext_Factory::object('Object_Instance');
                    $objectInstance->setObject($object);
                    $objectInstance->setName($object->getName());
                    $this->_tree->addItem($objectInstance->getName() . '_instance', '_Layout_', $objectInstance,
                        $cmpData['order']);
                }
                continue;
            }

            $this->_tree->changeParent($cmpData['id'], Designer_Project::LAYOUT_ROOT);
        }

        foreach ($stores as $id => $object) {
            // create models
            $modelName = $object->getName() . 'Model';

            if (is_object($object->proxy) && is_object($object->proxy->reader)) {
                $reader = $object->proxy->reader;
                if (isset($reader->root) && !empty($reader->root)) {
                    if (empty($reader->rootProperty)) {
                        $reader->rootProperty = $reader->root;
                    }
                    $reader->root = '';
                }
            }


            if (!$this->objectExists($modelName)) {
                /**
                 * @var Ext_Model $model
                 */
                $model = Ext_Factory::object('Model');
                $model->setName($modelName);
                $model->idProperty = 'id';

                $storeFields = $object->getFields();
                foreach ($storeFields as $name => $field) {
                    $field->name = $name;
                    $model->addField(Ext_Factory::object('Data_Field', $field->getConfig()->__toArray(true)));
                }

                //$model->defineOnly = true;
                $this->_tree->addItem($modelName, Designer_Project::COMPONENT_ROOT, $model, -10);
                $object->model = $modelName;
                $object->resetFields();
            }

        }
        return true;
    }
}