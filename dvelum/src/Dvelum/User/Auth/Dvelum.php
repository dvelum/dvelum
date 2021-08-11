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
 * User Auth provider for internal Dvelum authentification (object User)
 * @author Sergey Leschenko
 */
use Dvelum\Orm\Model;

class Dvelum extends AbstractAdapter
{
	/**
	 * Auth user
	 * @param string $login
	 * @param string $password
	 * @return bool
	 */
	public function auth($login,$password) : bool
	{
		$sql = $this->orm->model('User')->getDbConnection()->select()
			->from($this->orm->model('User')->table())
			->where('`login` =?' , $login)
			->where('`enabled` = 1');

		$data = $this->orm->model('User')->getDbConnection()->fetchRow($sql);

		if(empty($data) || !password_verify($password , $data['pass']))
			return false;

		$this->userData = $data;
		return true;
	}
}
