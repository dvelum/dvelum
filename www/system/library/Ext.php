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
 * ExtJS 4 Wrapper Package
 * @author Kirill A Egorov 2011
 * @package Ext
 */
class Ext
{
	/**
	 * Calc Property classname by Object class
	 * @param string $class - Ext object class
	 * @return string
	 */
	static public function getPropertyClass($class)
	{		
		return 	'Ext_Property_' . $class;
	}
	
	/**
	 * Calc Evemt classname by object class
	 * @param string $class
	 * @return string
	 */
	static public function getEventClass($class)
	{
		if(!strlen($class))
			return 'Ext_Events'; 
	
		$name = 'Ext_Events_' . $class;
		
		if(class_exists($name))
			return 	$name;
		else
			return 'Ext_Events';	
	}
}