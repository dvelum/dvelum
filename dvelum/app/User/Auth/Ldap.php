<?php
/**
 * User Auth provider for LDAP.
 * @author Sergey Leschenko
 */

class User_Auth_Ldap extends User_Auth_Abstract
{
	protected $lc = false;

	/**
	 * LDAP bind status
	 * @var bool
	 */
	private $bindStatus = false;

	/**
	 * @param Config_Abstract $config - auth provider config
	 */
	public function __construct(Config_Abstract $config)
	{
		if(!extension_loaded('ldap'))
			throw new Exception('Cannot find php-ldap extension!');

		parent::__construct($config);

		$this->lc = @ldap_connect($this->config->get('host'),$this->config->get('port'));
		if(!$this->lc)
			throw new Exception('Cannot connect to LDAP server: '.ldap_error($this->lc));

		@ldap_set_option($this->lc, LDAP_OPT_PROTOCOL_VERSION, $this->config->get('protocolVersion'));
	}

	/**
	 * Auth user
	 * @param string $login
	 * @param string $password
	 * @return boolean
	 */
	public function auth($login,$password)
	{
		$domain = false;
		$l = explode('@',$login,2);
		if(isset($l[1]) && !empty($l[1])) {
			$login = $l[0];
			$domain = $l[1];
		}
		if($domain)
			$this->config->set('baseDn',str_replace('%d',$domain,$this->config->get('baseDn')));

		$sql = Model::factory('User')->getSlaveDbConnection()->select()
			->from(Model::factory('User')->table())
			->where('`login` =?' , $login)
			->where('`enabled` = 1');

		$userData = Model::factory('User')->getSlaveDbConnection()->fetchRow($sql);
		if(!$userData)
			return false;

		$authCfg = Model::factory('User_Auth')->getList(
			false,
			array('type'=>'ldap', 'user'=>$userData['id'])
		);

		if(empty($authCfg))
			return false;

		$authCfg = Db_Object::factory('User_Auth',$authCfg[0]['id'])->get('config');
		$authCfg = json_decode($authCfg,true);

		$loginSearchFilter = (isset($authCfg['loginSearchFilter']))
			? $authCfg['loginSearchFilter']
			: $this->config->get('loginSearchFilter');

		foreach(array('%l','%d') as $attr)
		{
			switch($attr) {
				case '%l':
					$value = $login;
					break;
				case '%d':
					$value = $domain;
					break;
			}
			if($value)
				$loginSearchFilter = str_replace($attr, $value, $loginSearchFilter);
		}

		$bind = @ldap_bind($this->lc,$this->config->get('firstBindDn'),$this->config->get('firstBindPassword'));
		if(!$bind)
			throw new Exception('Cannot bind to LDAP server: '.ldap_error($this->lc));

		$res = @ldap_search(
			$this->lc,
			$this->config->get('baseDn'),
			$loginSearchFilter,
			array('dn')
		);
		if(!$res)
			return false;

		if(ldap_count_entries($this->lc,$res) === 0)
			return false;

		$userEntry = ldap_get_entries($this->lc,$res);
		$userEntry = $userEntry[0];

		$this->bindStatus = @ldap_bind($this->lc,$userEntry['dn'],$password);
		if(!$this->bindStatus)
			return false;

		if($this->config->get('saveCredentials'))
			$this->saveCredentials(array('dn'=>$userEntry['dn'],'password'=>$password));

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

	/**
	 * Get LDAP connection resource
	 * @return bool|resource
	 */
	public function getLC()
	{
		if(!$this->bindStatus) {
			$credentials = $this->getCredentials();
			if(!$credentials)
				throw new Exception('No saved LDAP credentials! Do User_Auth_Ldap::auth($login, $password) first!');

			$this->bindStatus = @ldap_bind($this->lc,$credentials['dn'],$credentials['password']);
		}
		if(!$this->bindStatus)
			return false;
		return $this->lc;
	}
}
