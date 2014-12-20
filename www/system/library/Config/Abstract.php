<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
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
 * The Config_Abstract abstract class, which is used for implementing configuration adapters
 * @author Kirill A Egorov
 * @abstract
 * @package Config
 */
abstract class Config_Abstract implements ArrayAccess , Iterator
{
    /**
     * Config Data
     * @var array
     */
     protected $_data = array();
     
     /**
      * Config name
      * @var string
      */
     protected $_name;
     
     /**
      * Constructor
      * @param string $name - configuration identifier
      */
     public function __construct($name)
     {
         $this->_name = $name;
     }
     
 	/**
     * Convert into an array
     * @return array
     */
     public function __toArray()
     {
         return $this->_data;
     }
     /**
      * Get the number of elements
      * @return integer
      */
     public function getCount()
     {
     	return count($this->_data);
     }   
      
     /**
      * Get the configuration parameter
      * @param string $key — parameter name
      * @throws Exception
      * @return mixed
      */
     public function get($key)
     {
         if(isset($this->_data[$key]))
             return $this->_data[$key];
         else 
             throw new Exception('Config::get Invalid key '.$key); 
     }
     
     /**
      *  Set the property value
      * @param string $key
      * @param mixed $value
      */
     public function set($key , $value)
     {
         $this->_data[$key] = $value;     
     }
     
     /**
      * Set property values using an array
      * @param array $data
      */
     public function setData(array $data)
     {
     	if(empty($data))
     		return;
     	
     	foreach ($data as $k=>$v)
     		$this->_data[$k]=$v;
     }
     
 	/**
     * Remove a parameter
     * @param string $key
     * @return true
     */
    public function remove($key)
    {
    	if(isset($this->_data[$key]))
    		unset($this->_data[$key]);
    	return true;	
    }
    
    /*
     * Start of ArrayAccess implementation
     */
	public function offsetSet($offset, $value)
	{
        $this->_data[$offset] = $value;        
    }
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }
    /*
     * End of ArrayAccess implementation
     */
    
    /*
     * Start of Iterator implementation
     */
    public function rewind()
    {
       reset($this->_data);
    }
    public function current()
    {
       return $this->_data[key($this->_data)];
    }
    public function key()
    {
       return key($this->_data);
    }
    public function next()
    {
       next($this->_data);
    }
    public function valid()
    {
       return isset($this->_data[key($this->_data)]);
    }
    /*
     * End of Iterator implementation
     */
    
   /**
	* Get data handle
	* Hack method. Do not use it without understanding.
	* Get a direct link to the stored data array
	* @return array
	*/
    public function & dataLink()
    {
    	return $this->_data;
    }
    
    /**
     * Remove all parameters
     */
    public function removeAll()
    {
    	$this->_data = array(); 
    }
    /**
     * Get config name
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}