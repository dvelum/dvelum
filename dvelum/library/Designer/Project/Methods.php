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
 * Method manager for Designer project 
 * @author Kirill Egorov 2013  DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @package Designer
 */
class Designer_Project_Methods
{
  protected $_methods = array();

  /**
   * Get methods list
   * @return array  - array of Designer_Project_Methods_Item
   */
  public function getMethods()
  {
    return $this->_methods;
  }
  
  /**
   * Add object method
   * @param string $object
   * @param string $method
   * @param array $params , optional
   * @param string $code , optional
   * @return Designer_Project_Methods_Item | false
   */
  public function addMethod($object , $method , array $params = array() , $code = '')
  {
    if($this->methodExists($object, $method))
      return false;
    
    $methodObject = new Designer_Project_Methods_Item($method);

    if(!empty($params))
      $methodObject->addParams($params);
    
    if(!empty($code))
      $methodObject->setCode($code);
    
    $this->_methods[$object][$method] = $methodObject;
    return $methodObject;
  }
  
  /**
   * Get object methods
   * @param string $object
   * @return array
   */
  public function getObjectMethods($object)
  {
    if(isset($this->_methods[$object]) && !empty($this->_methods[$object]))
      return $this->_methods[$object];
    else 
      return array();
  }
  /**
   * Get method
   * @param string $object
   * @param string $method
   * @return Designer_Project_Methods_Item | false
   */
  public function getObjectMethod($object , $method)
  {
    if(!$this->methodExists($object, $method))
      return false;
    
    return $this->_methods[$object][$method];     
  }
  
  /**
   * Check if method Exists
   * @param string $object
   * @param string $method
   * @return boolean
   */
  public function methodExists($object , $method)
  {
    return (isset($this->_methods[$object]) && isset($this->_methods[$object][$method]));
  }
  
  /**
   * Remove method
   * @param string $object
   * @param string $method
   */
  public function removeMethod($object , $method)
  {
    if($this->methodExists($object , $method))
      unset($this->_methods[$object][$method]);
  }
  
  /**
   * Remove all object methods
   * @param string $object
   */
  public function removeObjectMethods($object)
  {
    unset($this->_methods[$object]);
  }
  
  /**
   * Remove all project methods
   */
  public function removeAll()
  {
    $this->_methods = array();
  }
  
  /**
   * Update method
   * @param string $object
   * @param string $method
   * @param string $code
   * @param array $params
   * @return boolean
   */
  public function updateMethod($object , $method , array $params , $code)
  {
    if(!$this->methodExists($object , $method))
        return false;
    
    $mObject  = $this->_methods[$object][$method];
    $mObject->setCode($code);
    $mObject->setParams($params);

    return true;
  }
  
  /**
   * Rename method
   * @param string $object
   * @param string $oldName
   * @param string $newName
   * @return boolean
   */
  public function renameMethod($object , $oldName , $newName)
  {
    if(!$this->methodExists($object , $oldName))
      return false;
    
    $mObject = $this->_methods[$object][$oldName];
    $mObject->setName($newName);
    unset($this->_methods[$object][$oldName]);
    $this->_methods[$object][$newName] = $mObject;
    return true;
  }
  
  /**
   * Set method code
   * @param string $object
   * @param string $method
   * @param string $code
   * @return boolean
   */
  public function setMethodCode($object , $method , $code)
  {
    if(!$this->methodExists($object , $method))
      return false;
    
    $this->_methods[$object][$method]->setCode($code);    
    return true;
  }
}