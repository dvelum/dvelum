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
 * @author Kirill A Egorov
 * @package Ext
 */
class Ext_Factory
{
	/**
	 * Create object
	 * @param string $class (Grid , Panel, Window , etc)
	 * @return Ext_Object
	 */
	static public function object($class , array $config = [])
	{	
		$class = ucfirst($class);
		
		if(strpos($class, 'Ext_')===0)
		{
			$className = $class;
			$class = substr($class,4);		
		}else{
			$className = 'Ext_'.$class;
		}
			
		if(class_exists($className))
			$o = new $className();
		else
			$o = new Ext_Virtual($class);
					
		if(!empty($config))
		{
			foreach ($config as $k=>$v)
			{
				if($o->isValidProperty($k))
				{
					try {
						$o->$k = $v;	
					}catch (Exception $e){
						/*
						 * ignore wrong properties
						 */	
					}
				}
			}
		}
		
		return $o;
	}
	
	/**
	 * Get events for object
	 * @param string $objectClass
	 * @return Ext_Events
	 */
	static public function getEvents($objectClass)
	{
		$objectClass = ucfirst($objectClass);
		
		if(strpos($objectClass, 'Ext_Events')===0){
			$objectClass = substr($objectClass,10);		
		}elseif(strpos($objectClass, 'Ext_')===0){		
			$objectClass = substr($objectClass,4);
		}
		
		return call_user_func(array(Ext::getEventClass($objectClass),'getInstance'));
	}
	
	/**
	 * Copy properties from Ext_Object
	 * @param Ext_Object $from
	 * @param Ext_Object $to
	 */
	static public function copyProperties(Ext_Object $from ,  Ext_Object $to)
	{
		$properties = $from->getConfig()->__toArray();
		
		if(empty($properties))
			return;	
		
		foreach ($properties as $k=>$v)
		{
			if($to->isValidProperty($k))
			{
				try {
						$to->$k = $v;
				}catch (Exception $e){
					/*
					 * ignore wrong properties
					 */	
				}
			}
		}
	}
}