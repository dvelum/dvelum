<?php
/**
 * Factory class for auth providers
 * @author Sergey Leschenko
 */

class User_Auth
{
	/**
	 * Factory method of User_Auth instantiation
	 * @param Config_Abstract $config — auth provider config
	 * @return User_Auth_Abstract
	 */
	static public function factory(Config_Abstract $config)
	{
		$providerAdapter = $config->get('adapter');

		if (!class_exists($providerAdapter))
			throw new Exception('Unknown auth adapter ' . $providerAdapter);

		return new $providerAdapter($config);
	}
}
