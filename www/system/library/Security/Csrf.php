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
 * Security_Csrf class handles creation and validation 
 * of tokens aimed at anti-CSRF protection.
 * @author Kirill Egorov
 * @package Security
 * @uses Utils, Store_Interface , Store_Session , Request
 */
class Security_Csrf
{
	/**
	 * A constant value, the name of the header parameter carrying the token
	 * @var string
	 */
	const HEADER_VAR = 'HTTP_X_CSRF_TOKEN';
	
	/**
	 * A constant value, the name of the token parameter being passed by POST request
	 * @var string
	 */
	const POST_VAR = 'xscrftoken';
	
	/**
	 * Token lifetime (1 hour 3600s)
	 * @var integer
	 */
	static protected $_lifetime = 3600;
	/**
	 * Limit of tokens count to perform cleanup
	 * @var integer
	 */
	static protected $_cleanupLimit = 300;
	
	/**
	 * Token storage
	 * @var Store_Interface
	 */
	static protected $_storage = false;
	
	/**
	 * Set token storage implementing the Store_interface
	 * @param Store $store
	 */
	static public function setStorage(Store_Interface $store)
	{
		static::$_storage = $store;
	}
	
	/**
	 * Set config options (storage , lifetime , cleanupLimit)
	 * @param array $options
	 * @throws Exception
	 */
	static public function setOptions(array $options)
	{
		if(isset($options['storage']))
			if($options['storage'] instanceof  Store_Interface)
				static::$_storage = $options['storage'];
			else 
				throw new Exception('invalid storage');
				
		if(isset($options['lifetime']))
			static::$_lifetime = intval($options['lifetime']);
			
		if(isset($options['cleanupLimit']))
			static::$_cleanupLimit = intval($options['cleanupLimit']);	
				
	}
	
	public function __construct()
	{
		if(!self::$_storage)
			self::$_storage = Store::factory(Store::Session , 'security_csrf');
	}
	
	/**
	 * Create and store token
	 * @return string
	 */
	public function createToken()
	{	
		/*
		 * Cleanup storage
		 */
		if(self::$_storage->getCount() > self::$_cleanupLimit)
			$this->cleanup();
		
		$token = Utils::hash(mt_rand(1, 200000000));
		self::$_storage->set($token , time());
		return $token;
	}
	
	/**
	 * Check if token is valid
	 * @param string $token
	 * @return boolean
	 */
	public function isValidToken($token)
	{
		if(!self::$_storage->keyExists($token))
			return false;
			
		if( time() < intval(self::$_storage->get($token)) + self::$_lifetime )
		{
			return true;
		}
		else
		{
			self::$_storage->remove($token);
			return false;
		}
	}
	
	/**
	 * Remove tokens with expired lifetime
	 */
	public function cleanup()
	{
		$tokens = self::$_storage->getData();
		$time = time();
		
		foreach ($tokens as $k=>$v)
			if(intval($v) + self::$_lifetime < $time)
				self::$_storage->remove($k);	
	}
	
	/**
	 * Invalidate (remove) token
	 * @param string $token
	 */
	public function removeToken($token)
	{
		self::$_storage->remove($token);
	}
	
	/**
	 * Check POST request for a token
	 * @param string $tokenVar - Variable name in the request
	 * @return boolean
	 */
	public function checkPost($tokenVar = Security_Csrf::POST_VAR)
	{
		$var = Request::post($tokenVar, 'string', false);
		if($var!==false && $this->isValidToken($var))
			return true;
		else
			return false;
	}
	
	/**
	 * Check HEADER for a token
	 * @param string $tokenVar - Variable name in the header
	 * @return boolean
	 */
	public function checkHeader($tokenVar = Security_Csrf::HEADER_VAR)
	{
		$var = Request::server($tokenVar, 'string', false);
		if($var!==false && $this->isValidToken($var))
			return true;
		else
			return false;
	} 	
}