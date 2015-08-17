<?php
/**
 * User Auth provider for internal Dvelum authentification (object User)
 * @author Sergey Leschenko
 */

class User_Auth_Dvelum extends User_Auth_Abstract
{
	/**
	 * Auth user
	 * @param string $login
	 * @param string $password
	 * @return boolean
	 */
	public function auth($login,$password)
	{
		$sql = Model::factory('User')->getSlaveDbConnection()->select()
			->from(Model::factory('User')->table())
			->where('`login` =?' , $login)
			->where('`enabled` = 1');

		$data = Model::factory('User')->getSlaveDbConnection()->fetchRow($sql);

		if(empty($data) || !password_verify($password , $data['pass']))
			return false;

		$this->userData = $data;
		return true;
	}
}
