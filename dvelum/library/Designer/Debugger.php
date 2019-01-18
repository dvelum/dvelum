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

class Designer_Debugger
{
    /**
     * @var Designer_Project
     */
    protected $_project;

    public function __construct(Designer_Project $project)
    {
        $this->_project = $project;
    }

    /**
     * @param string $objectName
     * @throws Exception
     * @return Ext_Object
     */
    protected function _getObject($objectName)
    {
        if (!$this->_project->objectExists($objectName))
            throw new Exception('Designer_Debugger::_getObject nonexistent object ' . $objectName);
        return $this->_project->getObject($objectName);
    }

    /**
     * @return Tree
     */
    public function getTree()
    {
        return $this->_project->getTree();
    }

    /**
     * @param string $objectName
     * @param boolean $exceptEmpty
     * @return array
     */
    public function getObjectProperties($objectName, $exceptEmpty = true)
    {
        $o = $this->_getObject($objectName);
        if ($o instanceof Ext_Object) {
            return $o->getConfig()->__toArray($exceptEmpty);
        } else {
            return array();
        }
    }

    /**
     * @param string $objectName
     * @return array
     */
    public function getObjectEvents($objectName)
    {
        $object = $this->_getObject($objectName);
        $eventManager = $this->_project->getEventManager();
        $objectEvents = $eventManager->getObjectEvents($objectName);
        $data = array();
        if (!empty($objectEvents)) {
            $eventsConfig = $object->getConfig()->getEvents()->__toArray();

            foreach ($objectEvents as $event => $config) {
                if (isset($config['is_local']) && $config['is_local']) {
                    $data[] = array(
                        'name' => $event,
                        'params' => $config['params'],
                        'value' => $config['code'],
                        'is_local' => true
                    );
                } else {
                    $data[] = array(
                        'name' => $event,
                        'params' => @$eventsConfig[$event],
                        'value' => $config['code'],
                        'is_local' => false
                    );
                }
            }
        }
        return $data;
    }

    /**
     * @param string $objectName
     * @throws Exception
     * @return array
     */
    public function getObjectVariables($objectName)
    {
        $pObject = $this->_getObject($objectName);

        $o = new ReflectionObject($pObject);
        $properties = $o->getProperties();
        $data = array();

        foreach ($properties as $prop) {
            $prop->setAccessible(true);

            if ($prop->isProtected()) $pModifiers = 'protected ';
            if ($prop->isPrivate()) $pModifiers = 'private ';
            if ($prop->isPublic()) $pModifiers = 'public ';

            if ($prop->isStatic()) $pModifiers .= 'static';

            $data[] = array(
                'access' => $pModifiers,
                'name' => $prop->getName(),
                'value' => $prop->getValue($pObject)
            );
        }
        return $data;
    }

    /**
     * @param string $objectName
     * @throws Exception
     * @return array
     */
    public function getObjectMethods($objectName)
    {
        $pObject = $this->_getObject($objectName);

        $o = new ReflectionObject($pObject);
        $methods = $o->getMethods();

        $data = array();

        foreach ($methods as $prop) {
            $prop->setAccessible(true);
            $pModifiers = '';

            if ($prop->isFinal()) $pModifiers .= 'final ';
            if ($prop->isAbstract()) $pModifiers .= 'abstract ';
            if ($prop->isProtected()) $pModifiers .= 'protected ';
            if ($prop->isPrivate()) $pModifiers .= 'private ';
            if ($prop->isPublic()) $pModifiers .= 'public ';
            if ($prop->isStatic()) $pModifiers .= 'static ';

            $params = $prop->getParameters();
            $paramsList = array();
            foreach ($params as $param) {
                $paramStr = '';

                if ($param->isArray()) {
                    $paramStr .= 'array ';
                } else {
                    $class = $param->getClass();
                    if (!is_null($class))
                        $paramStr .= $class->getName() . ' ';
                }

                if ($param->isPassedByReference())
                    $paramStr .= ' &';

                $paramStr .= '$' . $param->getName() . ' ';

                if ($param->isDefaultValueAvailable()) {
                    $val = $param->getDefaultValue();
                    if (is_string($val) || empty($val)) {
                        $paramStr .= ' = "' . $param->getDefaultValue() . '"';
                    } else {
                        $paramStr .= ' = ' . $param->getDefaultValue();
                    }
                }
                $paramsList[] = $paramStr;
            }

            $data[] = array(
                'access' => $pModifiers,
                'name' => $prop->getName(),
                'params' => $paramsList
            );
        }
        return $data;
    }

    /**
     * @param string $objectName
     * @return string
     */
    public function getObjectExtClass($objectName)
    {
        return $this->_getObject($objectName)->getClass();
    }

    /**
     * @param string $objectName
     * @return string
     */
    public function getObjectPHPClass($objectName)
    {
        return get_class($this->_getObject($objectName));
    }

    /**
     * Check if object is extended component
     * @param string $objectName
     * @return boolean
     */
    public function isExtendedObject($objectName)
    {
        $o = $this->_project->getObject($objectName);
        if ($o instanceof Ext_Object) {
            return $this->_project->getObject($objectName)->isExtendedComponent();
        }
        return false;
    }

    /**
     * Get methods for Ext Object
     * @param string $objectName
     * @return array
     */
    public function getObjectLocalMethods($objectName)
    {
        return $this->_project->getMethodManager()->getObjectMethods($objectName);
    }
}