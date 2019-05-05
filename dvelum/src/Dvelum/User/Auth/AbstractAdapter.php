<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
declare(strict_types=1);

namespace Dvelum\User\Auth;
/**
 * Abstract class of User authentification provider.
 * @author Sergey Leschenko
 */

use Dvelum\Config\ConfigInterface;

abstract class AbstractAdapter implements AdapterInterface
{
	protected $userData = null;
	protected $config = false;

	/**
	 * @param ConfigInterface $config - auth provider config
	 */
	public function __construct(ConfigInterface $config)
	{
		$this->config = $config;
	}

	/**
	 * Auth user
	 * @param string $login
	 * @param string $password
	 * @return boolean
	 */
	abstract public function auth($login, $password) : bool ;

	/**
	 * Get Dvelum user data (object User)
	 * @return array|null
	 */
	public function getUserData() :?array
	{
		return $this->userData;
	}
}
