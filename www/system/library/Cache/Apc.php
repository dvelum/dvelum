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
 *  APC cache adapter
 *  @package Cache
 */
class Cache_Apc extends Cache_Abstract implements Cache_Interface
{
	const DEFAULT_LIFETIME = 0;
	const DEFAULT_KEY_PREFIX = '';
	const DEFAULT_NORMALIZE_KEYS = false;

	public function __construct(array $options)
	{
		$this->_options = $options;
		
		if(!isset($options['normalizeKeys']))
			$this->_options['normalizeKeys'] = self::DEFAULT_NORMALIZE_KEYS;
		
		if(!isset($options['keyPrefix']))
			$this->_options['keyPrefix'] = self::DEFAULT_KEY_PREFIX;
		
		if(!isset($options['defaultLifeTime']))
			$this->_options['defaultLifeTime'] = self::DEFAULT_LIFETIME;
	}

	/**
	 * (non-PHPdoc) 
	 * @see Cache_Interface::save()
	 */
	public function save($data , $key , $specificLifetime = false)
	{	
		if($specificLifetime === false)
			$specificLifetime = $this->_options['defaultLifeTime'];
			
		// cache id may need prefix
		$id = $this->_id($key);
		
		$result = apc_store($id , $data , $specificLifetime);
		$this->_opCounter['save'] ++;
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache_Interface::load()
	 */
	public function load($key)
	{
		// cache id may need prefix
		$key = $this->_id($key);
		
		$data = apc_fetch($key);
		
		$this->_opCounter['load'] ++;
		
		return $data;
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache_Interface::clean()
	 */
	public function clean()
	{
		apc_clear_cache('user');
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache_Interface::remove()
	 */
	public function remove($key)
	{
		$key = $this->_id($key);
		$this->_opCounter['remove'] ++;
		return apc_delete($key);
	}
}