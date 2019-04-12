<?php
/**
 * Factory class for auth providers
 * @author Sergey Leschenko
 */

use Dvelum\Config\ConfigInterface;

class User_Auth
{
	/**
	 * Factory method of User_Auth instantiation
	 * @param ConfigInterface $config — auth provider config
     * @throws \Exception
	 * @return User_Auth_Abstract
	 */
	static public function factory(ConfigInterface $config) : User_Auth_Abstract
	{
		$providerAdapter = $config->get('adapter');

		if (!class_exists($providerAdapter))
			throw new \Exception('Unknown auth adapter ' . $providerAdapter);

		return new $providerAdapter($config);
	}
}
