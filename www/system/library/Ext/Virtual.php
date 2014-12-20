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
 * Class for objects that are generated without the Specialized logic
 * @author Kirill A Egorov 
 * @package Ext
 */
class Ext_Virtual extends Ext_Object
{
	protected $_class;

	public function __construct($class)
	{
		$this->_class = ucfirst($class);
		parent::__construct();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ext_Object::getClass()
	 */
	public function getClass()
	{
		return $this->_class;
	}
}