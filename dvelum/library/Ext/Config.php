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
 * Class describes Ext_Object configuration (property helper)
 * @author Kirill A Egorov
 * @package Ext
 */
class Ext_Config
{
    const JS_TOKEN = '[js:]';
    /**
     * @var Ext_Events
     */
	protected $_events = false;
	/**
	 * @var Ext_Property
	 */
	protected $_properties;
	protected $_data = array();
	protected $_class = null;

	public function __construct($class)
	{
		$this->_class = str_replace('Ext_Property_','', $class);
		if(!class_exists($class))
			throw new Exception('Invalid property class ' . $class);
		
		$this->_properties = call_user_func(array($class , 'getInstance'));
		
		if(!$this->_properties instanceof Ext_Property)
			throw new Exception('Invalid property implementation ' . $class);
	}
	
	/**
	 * Lazy loading for events
	 */
	protected function _initEvents()
	{
		if($this->_events !== false)
			return;
			
		if(is_null($this->_class))
			$this->_class = str_replace('Ext_Property_','', get_class($this->_properties));
		
		$this->_events = Ext_Factory::getEvents($this->_class);	
	}
	
	/**
	 * Get object events
	 * @return Ext_Events
	 */
	public  function getEvents()
	{
		$this->_initEvents();
		return $this->_events;
	}
	
	/**
	 * Get parent class
	 * @return string
	 */
	public function getExtends()
	{
		return  $this->_properties->getExtend();
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function get($name)
    {
        if($name === 'xtype')
            return $this->_properties->getXtype();

        if($name === 'ftype')
            return $this->_properties->getFtype();

        if($name === 'extend')
            return $this->_properties->getExtend();


        if(!$this->_properties->isValid($name))
            throw new Exception('Trying to get undefined property ' . get_class($this->_properties) . '->' . $name);

        if(!is_array($this->_data) || !isset($this->_data[$name]))
            return '';

        return $this->_data[$name];
    }

	public function set($name , $value)
    {
        if(!$this->_properties->isValid($name))
            throw new Exception('Trying to set undefined property ' . get_class($this->_properties) . '->' . $name);

        switch ($this->_properties->$name) {
            case Ext_Property::Boolean:
                if((is_string($value) && strlen($value)) || !is_string($value))
                    $value = Filter::filterValue('boolean', $value);
                break;
        }

        return $this->_data[$name] = $value;
    }

	public function __set($name , $value)
	{
        return $this->set($name , $value);
	}

	public function setValues(array $data){
	    foreach ($data as $k=>$v){
	        $this->set($k,$v);
        }
    }

	/**
	 * Set valid properties from config
	 * @param array $data
	 */
	public function importValues(array $data)
	{
		foreach($data as $name => $value)
		{
			if($this->_properties->isValid($name))
			{
				switch ($this->_properties->$name) {
					case Ext_Property::Boolean:
						if((is_string($value) && strlen($value)) || !is_string($value))
							$value = Filter::filterValue('boolean', $value);
						break;
				}
				$this->_data[$name] = $value;
			}
		}
	}

	public function __isset($key)
	{
		return isset($this->_data[$key]);
	}
	
	/**
	 * Check if property exists
	 * @param string $name
	 * @return boolean
	 */
	public function isValidProperty($name)
	{
		return $this->_properties->isValid($name);
	}
	
	/**
	 * Get as array
	 * @param boolean $exceptEmpty - ignore empty properties
	 * @return array
	 */
	public function __toArray($exceptEmpty = false)
	{
		$properties = $this->_properties->getList();
		$result = array();
		
		if(empty($properties))
			return array();

		foreach($properties as $name => $type)
		{
			
			if($exceptEmpty && (!isset($this->_data[$name]) || $this->_data[$name]===''))
				continue;
				
			if(!$this->_properties->isValid($name))	
				continue;	

			$result[$name] = '';
			
			$val =  '';
			
			if(isset($this->_data[$name]))
			{
				if(is_array($this->_data[$name]))
					if(empty($this->_data[$name]))
						continue;

				$val = $this->_data[$name];
			}

			/*
			 * Performance patch
			 * 1 time __toString call
			 */
			$stringVal = (string) $val;	
			
			if($val === ''){
				$result[$name] = $val;
				continue;
			}

			switch($type){
				case Ext_Property::Boolean: 
							if($val)
								$val = true;
							else 
								$val = false;
						break;
				case Ext_Property::Number:
				case Ext_Property::Numeric: 
						$val = floatval($val);
						break;
				case Ext_Property::String: 	
						$val = strval($val);
						break;
				case Ext_Property::Object:
						if(is_array($val)){
							$val = json_encode($val);
						}
						break;
			}
			$result[$name] = $val;
				
		}
		return $result;
	}
	
	/**
	 * Get properties as array of prepared strings
	 * @return array
	 */
	public function asStringList()
	{
		$properties = $this->_properties->getList();
		$result = [];

		$xtype = $this->_properties->getXtype();
		$ftype = $this->_properties->getFtype();

        if(strlen($xtype))
            $result[] = 'xtype:"'.$this->_properties->getXtype().'"';


		if(strlen($ftype))
			$result[] = 'ftype:"'.$this->_properties->getFtype().'"'; 

		if(empty($properties))
			return array();
			
		foreach($properties as $name => $type)
		{	
			if($name == 'isExtended')
				continue;
			
			if(!isset($this->_data) || !isset($this->_data[$name]))
				continue;

			if(!$this->_properties->isValid($name))	
				continue;
				
			if(is_array($this->_data[$name]))
			{
				if(empty($this->_data[$name]))
					continue;
					
				$val = json_encode($this->_data[$name]);
				
			}else{
				$val =  $this->_data[$name];	
			}				

			/*
			 * Perfomance patch
			 * 1 time __toString call
			 */
			$stringVal = (string) $val;		
			
			if($this->_properties->isBoolean($name) && !strlen($stringVal) && !is_bool($val))
				continue;	

			if(!is_bool($val) && !strlen($stringVal))
				continue;	

			$val =  $this->_data[$name];


			if(is_bool($val)){
				if($val)
					$val = 'true';
				else
					$val = 'false';
			}else{
				switch($type){
					case Ext_Property::Boolean:
						if($val)
							$val = 'true';
						else
							$val = 'false';
						break;
					case Ext_Property::Number:
					case Ext_Property::Numeric:
						$val = floatval($val);
						break;
					case Ext_Property::String:
						$trimed = trim($val);
						if(substr($trimed, 0 , strlen(self::JS_TOKEN)) == self::JS_TOKEN){
							$val = substr($trimed , strlen(self::JS_TOKEN));
						}else{
							if(strpos($val, '{') === 0)
								$val = $stringVal;
							else
								$val = '"'.$stringVal.'"';
						}
						break;
					case Ext_Property::Object;
						$val = $stringVal;
						break;
				}
			}
			$result[] = $name.":".$val;
		}
		return $result;
	}
	
	public function __toString()
	{	
		return "{\n".Utils_String::addIndent(implode(",\n", $this->asStringList()))."\n}";
	}
	
	/**
	 * Set Object xtype
	 * @param string $type
	 */
	public function setXType($type)
	{
		$this->_data['xtype'] = $type;
	}
	
	public function setFType($type)
	{
	  $this->_data['ftype'] = $type;
	}
	
	/**
	 * Get object xtype
	 * @return string
	 */
	public function getXtype()
	{
		if(!isset($this->_data['xtype']))
			return '';
		
		return $this->_data['xtype'];
	}
	
	public function getFType()
	{
	  if(!isset($this->_data['ftype']))
	      return '';
	  
	  return $this->_data['ftype'];
	}
}