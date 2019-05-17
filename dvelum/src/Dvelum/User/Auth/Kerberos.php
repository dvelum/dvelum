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

use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config\ConfigInterface;
use \Exception;

/**
 * User Auth provider for Kerberos.
 * @author Sergey Leschenko
 */
class Kerberos extends AbstractAdapter
{
    /**
     * @param ConfigInterface $config - auth provider config
     */
    public function __construct(ConfigInterface $config)
    {
        if (!extension_loaded('krb5')) {
            throw new Exception('Cannot find php-krb5 extension!');
        }

        parent::__construct($config);
    }

    /**
     * Auth user
     * @param string $login
     * @param string $password
     * @return bool
     */
    public function auth($login, $password): bool
    {
        $realm = false;
        $l = explode('@', $login, 2);
        if (isset($l[1]) && !empty($l[1])) {
            $login = $l[0];
            $realm = $l[1];
        }

        if (!$realm) {
            $realm = $this->config->get('defaultRealm');
        }

        $principal = $login . '@' . $realm;

        $sql = Model::factory('User')->getSlaveDbConnection()->select()
            ->from(Model::factory('User')->table())
            ->where('`login` =?', $login)
            ->where('`enabled` = 1');

        $userData = Model::factory('User')->getSlaveDbConnection()->fetchRow($sql);
        if (!$userData) {
            return false;
        }

        $authCfg = Model::factory('User_Auth')->getList(
            false,
            array('type' => 'kerberos', 'user' => $userData['id'])
        );

        if (empty($authCfg)) {
            return false;
        }

        $authCfg = Orm\Record::factory('User_Auth', $authCfg[0]['id'])->get('config');
        $authCfg = json_decode($authCfg, true);

        $principal = (isset($authCfg['principal']))
            ? $authCfg['principal']
            : $principal;

        $ticket = new \KRB5CCache();
        try {
            $ticket->initPassword($principal, $password);
        } catch (Exception $e) {
            return false;
        }

        if (!$ticket) {
            return false;
        }

        if ($this->config->get('saveCredentials')) {
            $this->saveCredentials(array('principal' => $principal, 'password' => $password));
        }

        $this->userData = $userData;
        return true;
    }

    /**
     * Save credentials
     * @param array $credentials
     */
    private function saveCredentials($credentials)
    {
        \Dvelum\Store\Factory::get(\Dvelum\Store\Factory::SESSION, $this->config->get('adapter'))->set('credentials',
            $credentials);
    }

    /**
     * Return credentials
     * @return bool|mixed
     */
    private function getCredentials()
    {
        if ($this->config->get('saveCredentials')) {
            return \Dvelum\Store\Factory::get(\Dvelum\Store\Factory::SESSION,
                $this->config->get('adapter'))->get('credentials');
        } else {
            return false;
        }
    }
}
