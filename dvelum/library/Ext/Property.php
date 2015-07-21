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
 * Main class for extjs property sets
 * @author Kirill A Egorov
 * @package Ext
 * @subpackage Ext_Property
 */
abstract class Ext_Property
{
	/*
	 * Data types constants
	 */
	const Numeric = 1;
	const Number = 1;
	const String = 2;
	const Object = 3;
	const Boolean = 4;

	static protected $_instances = array();
	static public $extends = '';
	static public $xtype = '';
	static public $ftype = '';

    public $isExtended = self::Boolean;

	static public function getInstance()
	{
		$class = get_called_class();
		if(!isset(self::$_instances[$class]))
			self::$_instances[$class] = new $class();

		return self::$_instances[$class];
	}

	/**
	 * Check if property exists
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

	/**
	 * Get object parent class
	 * @return string
	 */
	public function getExtend()
	{
		return static::$extend;
	}

	/**
	 * Get object default xtype
	 * @return string
	 */
	public function getXtype()
	{
		return static::$xtype;
	}

	public function getFtype()
	{
	   return static::$ftype;
	}

	/**
	 * Check if property value should be boolean
	 * @param string $property - property name
	 * @return boolean
	 */
	public function isBoolean($property)
	{
		if(isset($this->$property) && $this->$property === self::Boolean)
			return true;
		else
			return false;
	}
}