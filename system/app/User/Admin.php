<?php
/**
 * @deprecated
 *
 */
class User_Admin extends User
{
	/**
     * @return User
     */
	static public function getInstance()
	{
		return User::getInstance();
	}
}