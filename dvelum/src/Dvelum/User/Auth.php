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

namespace Dvelum\User;

/**
 * Factory class for auth providers
 * @author Sergey Leschenko
 */

use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Orm;
use Dvelum\User\Auth\AbstractAdapter;
use Dvelum\User\Auth\AdapterInterface;

class Auth
{
    /**
     * Factory method of User_Auth instantiation
     * @param ConfigInterface $config — auth provider config
     * @return AbstractAdapter
     * @throws \Exception
     */
    static public function factory(ConfigInterface $config, Orm $orm): AdapterInterface
    {
        $providerAdapter = $config->get('adapter');

        if (!class_exists($providerAdapter)) {
            throw new \Exception('Unknown auth adapter ' . $providerAdapter);
        }

        return new $providerAdapter($config, $orm);
    }
}
