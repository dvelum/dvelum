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
 * Cache Backend Memcached
 * Simple Memcache realisation, based on Zend_Cache
 * @author Kirill Egorov
 */
class Cache_Memcache extends Cache_Abstract implements Cache_Interface
{
	const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT =  11211;
    const DEFAULT_PERSISTENT = true;
    const DEFAULT_WEIGHT  = 1;
    const DEFAULT_TIMEOUT = 1;
    const DEFAULT_RETRY_INTERVAL = 15;
    const DEFAULT_STATUS = true;
    
    const DEFAULT_COMPRESSION = false;
    const DEFAULT_LIFETIME = 0;
    const DEFAULT_KEY_PREFIX = '';
    const DEFAULT_NORMALIZE_KEYS = false;
    	
	protected $_opCounter = array(
		'load'=>0,
		'save'=>0,
		'remove'=>0
	);
	
	/**
	 * @var Memcache
	 */
	protected $_memcache = null;
	protected $_options = array();
	
	/**
	 * 
	 * @param array $options
	 * 	
	 *		'servers' => array(
	 *		    array(
	 *				'host' => self::DEFAULT_HOST,
	 *				'port' => self::DEFAULT_PORT,
	 *				'persistent' => self::DEFAULT_PERSISTENT,
	 *				'weight'  => self::DEFAULT_WEIGHT,
	 *				'timeout' => self::DEFAULT_TIMEOUT,
	 *				'retry_interval' => self::DEFAULT_RETRY_INTERVAL,
	 *				'status' => self::DEFAULT_STATUS,
	 *		    )
	 *		 ),
	 *		'compression' => self::DEFAULT_COMPRESSION,
	 *		'normalizeKeys'=>sef::DEFAULT_NORMALIZE_KEYS,
	 *		'defaultLifeTime=> self::DEFAULT_LIFETIME
	 *		'keyPrefix'=>self:DEFAULT_KEY_PREFIX
	 *
	 */
	public function __construct(array $options)
	{		
		$this->_options = $options;
        $this->_memcache = new Memcache();
	
		foreach ($this->_options['servers'] as $server) 
		{
			if (!isset($server['port'])) 
				$server['port'] = self::DEFAULT_PORT;
			
			if (!isset($server['persistent']))
				$server['persistent'] = self::DEFAULT_PERSISTENT;
						
			if (!isset($server['weight'])) 
				$server['weight'] = self::DEFAULT_WEIGHT;
			
			if (!isset($server['timeout'])) 
				$server['timeout'] = self::DEFAULT_TIMEOUT;
						
			if (!isset($server['retry_interval'])) 
				$server['retry_interval'] = self::DEFAULT_RETRY_INTERVAL;
			
			if (!isset($server['status'])) 
				$server['status'] = self::DEFAULT_STATUS;
		
			if(!isset($options['compression']))
				$this->_options['compression'] = self::DEFAULT_COMPRESSION;			
			
			if(!isset($options['normalizeKeys']))
				$this->_normalizeKeys = self::DEFAULT_NORMALIZE_KEYS;
			else
				$this->_normalizeKeys = $options['normalizeKeys'];
			
			if(!isset($options['keyPrefix']))
				$this->_keyPrefix = self::DEFAULT_KEY_PREFIX;
			else
				$this->_keyPrefix = $options['keyPrefix'];
				
			if(!isset($options['defaultLifeTime']))
				$this->_options['defaultLifeTime'] = self::DEFAULT_LIFETIME;
					
			$this->_memcache->addServer(
					    $server['host'], 
					    $server['port'], 
					    $server['persistent'],
						$server['weight'], 
					    $server['timeout'],
						$server['retry_interval']
			);		 
		}
	}
	
	/**
	 * Save some string data into a cache record
	 * @param  string $data Datas to cache
	 * @param  string $id Cache id
	 * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
	 * @return boolean True if no problem
	 */
	public function save($data, $id, $specificLifetime = false)
	{
		$serialized = false;
		
		if(!is_string($data)){				
		     $data = serialize($data);
		     $serialized = true;
		}

		if($specificLifetime === false)
			$specificLifetime = $this->_options['defaultLifeTime'];
				
		if ($this->_options['compression']){
			$flag = MEMCACHE_COMPRESSED;
		} else {
			$flag = 0;
		}
		
		$id = $this->_id($id); // cache id may need prefix	
		
		// ZF-8856: using set because add needs a second request if item already exists
		$result = @$this->_memcache->set($id, array($data,$serialized), $flag, $specificLifetime);
		$this->_opCounter['save']++;
		return $result;
	}
	
	/**
	 * Remove a cache record
	 * @param  string $id Cache id
	 * @return boolean True if no problem
	 */
	public function remove($id)
	{
		$id = $this->_id($id);
		$this->_opCounter['remove']++;
		return $this->_memcache->delete($id);
	}
	
	/**
	 * Clean some cache records
	 * @return boolean True if no problem
	 */
	public function clean()
	{
		return $this->_memcache->flush();		
	}
	
	/**
	 * Load data from cache
	 * @param  string  $id  Cache id
	 * @return mixed|false Cached datas
	 */
	public function load($id)
	{
		$id = $this->_id($id);// cache id may need prefix
	
		$data = $this->_memcache->get($id);	
		$this->_opCounter['load']++;	
		if ($data===false || !isset($data[0])) {			
			// no cache available
			return false;
		}
		
		if($data[1])
			return unserialize($data[0]);
		else
			return $data[0];
	}
	
	/**
	 * Get Memcache object link
	 * @return Memcache
	 */
	public function getHandler()
	{
		return $this->_memcache;
	}
}