<?php

/**
 * Main class for Extjs4 code generation
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2013  Kirill A Egorov,
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 * @uses Lang
 * @package Ext
 */
class Ext_Object implements Ext_Exportable
{
    /**
     * @var Ext_Config $_config
     */
    protected $_config;
    /**
     * @var string $_name
     */
    protected $_name;
    /**
     * @var bool $_isExtended
     */
    protected $_isExtended = false;

    protected $_elements = [];
    protected $_listeners = [];
    protected $_methods = [];
    protected $_localEvents = [];

    public function __construct()
    {
        $this->_loadConfig();
        $this->_initDefaultProperties();
    }

    /**
     * Init default properties for object
     * The method should be overridden by a successor
     */
    protected function _initDefaultProperties()
    {

    }

    /**
     * Compile object as extended component
     * @param boolean $flag
     */
    public function extendedComponent($flag)
    {
        $this->_isExtended = $flag;
        if ($this->_config->isValidProperty('isExtended')) {
            $this->_config->isExtended = $flag;
        }
    }

    /**
     * Check if component should be extended
     * @return boolean
     */
    public function isExtendedComponent()
    {
        if ($this->_isExtended || ($this->_config->isValidProperty('isExtended') && $this->_config->isExtended)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Object class
     * @return string
     */
    public function getClass()
    {
        return str_replace('Ext_', '', get_called_class());
    }

    protected function _loadConfig()
    {
        $this->_config = new Ext_Config(Ext::getPropertyClass(str_replace('Ext_', '', $this->getClass())));
    }

    /**
     * Set object name
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Get object name
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function get($name)
    {
        return $this->_config->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function set($name, $value)
    {
        $this->_config->set($name, $value);
    }

    public function setValues(array $data)
    {
        $this->_config->setValues($data);
    }

    /**
     * Get object Config
     * @return Ext_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Add event listener
     * @param string $name - event name
     * @param string $value - code
     */
    public function addListener($name, $value)
    {
        $this->_listeners[$name] = $value;
    }

    /**
     * Get event handlers
     * @return array
     */
    public function getListeners()
    {
        return $this->_listeners;
    }

    /**
     * Add method
     * @param string $name
     * @param string $paramsString
     * @param string $code
     * @param string $jsDoc
     */
    public function addMethod($name, $paramsString, $code, $jsDoc)
    {
        $this->_methods[$name] = array('params' => $paramsString, 'code' => $code, 'jsDoc' => $jsDoc);
    }

    /**
     * Add event description
     * @param string $name
     * @param string $paramsString
     * @param string $jsDoc
     */
    public function addLocalEvent($name, $paramsString, $jsDoc)
    {
        $this->_localEvents[$name] = array('params' => $paramsString, 'jsDoc' => $jsDoc);
    }

    /**
     * Check if property is valid
     * @param string $name
     * @return boolean
     */
    public function isValidProperty($name)
    {
        return $this->_config->isValidProperty($name);
    }

    /**
     * Get JS code for components description (Objects used as Classes)
     * @param string | boolean $namespace
     * @return string
     */
    public function getDefineJs($namespace = false)
    {
        if ($namespace) {
            $name = $namespace . '.' . $this->getName();
        } else {
            $name = $this->getName();
        }

        $items = '';
        $dockedItems = '';

        /*
         * Store object items & dockedItems  property for using in initComponent
         */
        if ($this->isValidProperty('items')) {
            $items = Utils_String::addIndent($this->items, 2, "\t", true);
            $this->items = '';
        }

        if ($this->isValidProperty('dockedItems')) {
            $dockedItems = $this->dockedItems;
            $this->dockedItems = '';
        }

        $childObjectsInit = '';

        if (!empty($this->_elements)) {
            foreach ($this->_elements as $oName => $object) {
                switch ($object->getClass()) {
                    case 'Docked':
                        $childObjectsInit .= $oName . ' =  ' . Utils_String::addIndent($object->__toString(), 1, "\t",
                                true) . ';' . "\n";
                        break;
                    case 'Component_Filter':
                        $childObjectsInit .= $oName . ' =   Ext.create("' . $object->getViewObject()->getConfig()->getExtends() . '",' . Utils_String::addIndent($object->__toString(),
                                1, "\t", true) . "\n);" . "\n";
                        break;
                    default:
                        if ($object->isInstance()) {
                            $parentName = $object->getObject()->getName();

                            if ($namespace) {
                                $parentName = $namespace . '.' . $parentName;
                            }

                            $childObjectsInit .= $oName . ' =  Ext.create("' . $parentName . '",' . Utils_String::addIndent($object->__toString(),
                                    1, "\t", true) . "\n);" . "\n";

                        } else {
                            $childObjectsInit .= $oName . ' =  Ext.create("' . $object->getConfig()->getExtends() . '",' . Utils_String::addIndent($object->__toString(),
                                    1, "\t", true) . "\n);" . "\n";
                        }
                        break;

                }
            }
        }

        $code = "\n" .
            'Ext.define("' . $name . '",{' . "\n" .
            "\t" . 'extend:"' . $this->_config->getExtends() . '",' . "\n" .
            "\t" . 'childObjects:null,' . "\n" .
            "\t" . 'constructor: function(config) {' . "\n" .
            "\t\t\t" . 'var me = this; ' . "\n" .
            "\t\t\t" . 'config = Ext.apply(' . "\n" .
            Utils_String::addIndent($this->__toString(), 3) .
            ', config || {});' . "\n" .
            "\t\t" . 'this.callParent(arguments);' . "\n" .
            "\t" . '},' . "\n" .
            "\n";

        if (!isset($this->_methods['initComponent'])) {
            $code .= "\t" . 'initComponent:function(){' . "\n" .
                "\t\t" . 'this.addDesignerItems();' . "\n" .
                "\t\t" . 'this.callParent();' . "\n" .
                "\t" . '},' . "\n";
        }

        if (!isset($this->_methods['destroy'])) {
            $code .= "\t" . 'destroy:function(){' . "\n" .
                "\t\t" . 'Ext.Object.each(this.childObjects,function(index, item){' . "\n" .
                "\t\t" . '    if(item.destroy){' . "\n" .
                "\t\t" . '        item.destroy();' . "\n" .
                "\t\t" . '    }' . "\n" .
                "\t\t" . ' });' . "\n" .
                "\t\t" . 'this.callParent(arguments);' . "\n" .
                "\t" . '},' . "\n";
        }

        $code .= "\t" . 'addDesignerItems:function(){' . "\n" .
            "\t\t" . 'var me = this;' . "\n";

        if (strlen($childObjectsInit)) {
            $code .= "\t\t" . 'this.childObjects = {};' . "\n" .
                Utils_String::addIndent($childObjectsInit, 2) . "\n";
        }

        if (!empty($dockedItems)) {
            $code .= "\t\t" . ' this.dockedItems = ' . Utils_String::addIndent($dockedItems, 2, "\t", true) . ";\n";
        }

        if (!empty($items)) {
            if (is_array($items)) {
                $code .= "\t\t" . 'this.items = [' . Utils_String::addIndent(implode("\n,", $items), 3, "\t",
                        true) . "];\n";
            } else {
                $code .= "\t\t" . 'this.items = ' . $items . ';' . "\n";
            }
        }

        $code .= "\n" . $this->_localEventsString() . "\t" . '}' .
            $this->_methodsString();

        $code .= '});';

        return $code;
    }

    /**
     * Conver object methods to string
     * @return string
     */
    protected function _methodsString()
    {
        $code = '';
        if (isset($this->_methods) && !empty($this->_methods)) {
            foreach ($this->_methods as $methodName => $methodData) {
                $code .= ',' . "\n";
                $code .= Utils_String::addIndent($methodData['jsDoc']) . "\n";

                $code .= "\t" . $methodName . ':function(' . $methodData['params'] . '){' . "\n";
                $code .= Utils_String::addIndent($methodData['code'], 2) . "\n";
                $code .= "\t}\n";
            }
        }
        return $code;
    }

    protected function _localEventsString()
    {
        $code = '';
        if (isset($this->_localEvents) && !empty($this->_localEvents)) {
            $items = array();
            foreach ($this->_localEvents as $name => $description) {
                $items[] = $description['jsDoc'] . "\n" . '"' . $name . '"';
            }

            // $code = "\n".Utils_String::addIndent('this.addEvents('."\n".Utils_String::addIndent(implode(",\n", $items))."\n);",2)."\n";
        }
        return $code;
    }

    /**
     * Convert listeners from storage, put into config
     */
    protected function _convertListeners()
    {
        if (empty($this->_listeners)) {
            return;
        }

        $listenersArray = array();

        foreach ($this->_listeners as $name => $code) {

            if ($name === 'handler' && $this->_config->isValidProperty('handler')) {
                $this->_config->handler = $code;

                if ($this->_config->isValidProperty('scope')) {
                    $this->_config->scope = 'this';
                }

            } else {
                $listenersArray[] = "'" . $name . "':" . $code;
            }
        }
        if (!empty($listenersArray)) {
            $this->_config->listeners = "{\n\t" . implode(",\n\t", $listenersArray) . "\n}";
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $this->_convertListeners();
        return $this->_config->__toString();
    }

    /**
     * Add child element
     * @param string $name
     * @param Ext_Object $object
     */
    public function addElement($name, Ext_Object $object)
    {
        $this->_elements[$name] = $object;
    }

    public function __isset($key)
    {
        return isset($this->_config->{$key});
    }

    /**
     * Check if object is instance
     * @return boolean
     */
    public function isInstance()
    {
        return false;
    }

    /**
     * Get object state for smart export
     */
    public function getState()
    {
        $config = $this->getConfig();
        $state = [
            'config' => $config->__toArray(true)
        ];

        if ($config->isValidProperty('store')) {

            $store = $config->store;
            if ($store instanceof Ext_Helper_Store) {
                $state['config']['store'] = [
                    'class' => $store->getClass(),
                    'state' => $store->getState()
                ];
            }
        }
        return $state;
    }

    /**
     * Set object state
     * @param $state
     */
    public function setState(array $state)
    {
        if (isset($state['config'])) {
            $this->getConfig()->importValues($state['config']);
        }

        if (isset($state['state']) && !empty($state['state']) && is_array($state['state'])) {
            foreach ($state['state'] as $property => $value) {
                $this->{$property} = $value;
            }
        }

        $config = $this->getConfig();

        if ($config->isValidProperty('store') && !empty($state['config']['store']['class'])) {
            /**
             * @var Ext_Exportable $store
             */
            $store = new $state['config']['store']['class'];
            $store->setState($state['config']['store']['state']);
            $config->store = $store;
        }
    }
}