<?php
/**
 * Factory class for auth providers
 * @author Sergey Leschenko
 */

use Dvelum\Config;

class User_Auth
{
	/**
	 * Factory method of User_Auth instantiation
	 * @param Config\Config $config — auth provider config
     * @throws \Exception
	 * @return User_Auth_Abstract
	 */
	static public function factory(Config\Config $config) : User_Auth_Abstract
	{
		$providerAdapter = $config->get('adapter');

		if (!class_exists($providerAdapter))
			throw new \Exception('Unknown auth adapter ' . $providerAdapter);

		return new $providerAdapter($config);
	}
}
