<?php
/**
 * Abstract class of User authentification provider.
 * @author Sergey Leschenko
 */

abstract class User_Auth_Abstract
{
	protected $userData = false;
	protected $config = false;

	/**
	 * @param Config_Abstract $config - auth provider config
	 */
	function __construct(Config_Abstract $config)
	{
		$this->config = $config;
	}

	/**
	 * Auth user
	 * @param string $login
	 * @param string $password
	 * @return boolean
	 */
	abstract public function auth($login,$password);

	/**
	 * Get Dvelum user data (object User)
	 * @return array|boolean
	 */
	public function getUserData()
	{
		return $this->userData;
	}
}
