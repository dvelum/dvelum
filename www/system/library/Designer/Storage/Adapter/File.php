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
 * File adapter for Designer_Storage
 * @author Kirill A Egorov 2012
 */
class Designer_Storage_Adapter_File extends Designer_Storage_Adapter_Abstract
{
	protected $_configPath = '';

	/**
	 * @param array $config, optional
	 */
	public function __construct($config = false)
	{
		parent::__construct($config);
		if($config)
			$this->setConfigsPath($config->get('configs'));
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Designer_Storage_Adapter_Abstract::load()
	 */
	public function load($id)
	{
		if(!is_file($id))
			throw new Exception('Invalid file path' . $id );
		return $this->_unpack(file_get_contents($id));
	}

	/**
	 * (non-PHPdoc)
	 * @see Designer_Storage_Adapter_Abstract::save()
	 */
	public function save($id , Designer_Project $obj)
	{
		$result = @file_put_contents($id , $this->_pack($obj));
		
		if($result!==false)
			return true;
		else 
			return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Designer_Storage_Adapter_Abstract::delete()
	 */
	public function delete($id)
	{
		$id = $this->_configPath . $id;
		return unlink($id);
	}
	
	/**
	 * Set path to configs directory
	 * @param string $path
	 */
	public function setConfigsPath($path)
	{
		$this->_configPath = $path;
	}
}