<?php
/**
 * User Auth provider for Kerberos.
 * @author Sergey Leschenko
 */

class User_Auth_Kerberos extends User_Auth_Abstract
{
	/**
	 * @param Config_Abstract $config - auth provider config
	 */
	public function __construct(Config_Abstract $config)
	{
		if(!extension_loaded('krb5'))
			throw new Exception('Cannot find php-krb5 extension!');

		parent::__construct($config);
	}

	/**
	 * Auth user
	 * @param string $login
	 * @param string $password
	 * @return boolean
	 */
	public function auth($login,$password)
	{
		$realm = false;
		$l = explode('@',$login,2);
		if(isset($l[1]) && !empty($l[1])) {
			$login = $l[0];
			$realm = $l[1];
		}

		if(!$realm)
			$realm = $this->config->get('defaultRealm');

		$principal = $login.'@'.$realm;

		$sql = Model::factory('User')->getSlaveDbConnection()->select()
			->from(Model::factory('User')->table())
			->where('`login` =?' , $login)
			->where('`enabled` = 1');

		$userData = Model::factory('User')->getSlaveDbConnection()->fetchRow($sql);
		if(!$userData)
			return false;

		$authCfg = Model::factory('User_Auth')->getList(
			false,
			array('type'=>'kerberos', 'user'=>$userData['id'])
		);

		if(empty($authCfg))
			return false;

		$authCfg = Db_Object::factory('User_Auth',$authCfg[0]['id'])->get('config');
		$authCfg = json_decode($authCfg,true);

		$principal = (isset($authCfg['principal']))
			? $authCfg['principal']
			: $principal;

		$ticket = new KRB5CCache();
		try {
			$ticket->initPassword($principal, $password);
		}catch(Exception $e){
			return false;
		}

		if(!$ticket)
			return false;

		if($this->config->get('saveCredentials'))
			$this->saveCredentials(array('principal'=>$principal,'password'=>$password));

		$this->userData = $userData;
		return true;
	}

	/**
	 * Save credentials
	 * @param array $credentials
	 */
	private function saveCredentials($credentials)
	{
		Store::factory(Store::Session, $this->config->get('adapter'))->set('credentials',$credentials);
	}

	/**
	 * Return credentials
	 * @return bool|mixed
	 */
	private function getCredentials()
	{
		if($this->config->get('saveCredentials'))
			return Store::factory(Store::Session, $this->config->get('adapter'))->get('credentials');
		else
			return false;
	}
}
