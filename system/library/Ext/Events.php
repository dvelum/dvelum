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
 * Main class for extjs  object events description
 * @author Kirill A Egorov
 * @package Ext
 * @subpackage Ext_Events
 */
class Ext_Events
{
	static protected $_instances = array();
	
	static public function getInstance()
	{
		$class = get_called_class();
		if(!isset(self::$_instances[$class]))
			self::$_instances[$class] = new $class();
		
		return self::$_instances[$class];
	}
	
	protected function __construct()
	{
		$this->_initConfig();
	}
	
	/**
	 * Check if event exists
	 * @param string $name
	 * @return boolean
	 */
	public function isValid($name)
	{
		return isset($this->$name);
	}
	
	/**
	 * Get property list
	 * @return array();
	 */
	public function getList()
	{
		return get_object_vars($this);
	}
	
	public function __toArray()
	{
		return $this->getList();
	}
	
	protected function _initConfig()
	{
	
	}
}