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

use Dvelum\Config\ConfigInterface;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Exception;

/**
 * User Auth provider for LDAP.
 * @author Sergey Leschenko
 */
class Ldap extends AbstractAdapter
{
    protected $lc = false;

    /**
     * LDAP bind status
     * @var bool
     */
    private $bindStatus = false;

    /**
     * @param ConfigInterface $config - auth provider config
     */
    public function __construct(ConfigInterface $config)
    {
        if (!extension_loaded('ldap')) {
            throw new Exception('Cannot find php-ldap extension!');
        }

        parent::__construct($config);

        $this->lc = @ldap_connect($this->config->get('host'), $this->config->get('port'));
        if (!$this->lc) {
            throw new Exception('Cannot connect to LDAP server: ' . ldap_error($this->lc));
        }

        @ldap_set_option($this->lc, LDAP_OPT_PROTOCOL_VERSION, $this->config->get('protocolVersion'));
    }

    /**
     * Auth user
     * @param string $login
     * @param string $password
     * @return bool
     */
    public function auth($login, $password): bool
    {
        $domain = false;
        $l = explode('@', $login, 2);
        if (isset($l[1]) && !empty($l[1])) {
            $login = $l[0];
            $domain = $l[1];
        }
        if ($domain) {
            $this->config->set('baseDn', str_replace('%d', $domain, $this->config->get('baseDn')));
        }

        $sql = Model::factory('User')->getDbConnection()->select()
            ->from(Model::factory('User')->table())
            ->where('`login` =?', $login)
            ->where('`enabled` = 1');

        $userData = Model::factory('User')->getDbConnection()->fetchRow($sql);
        if (!$userData) {
            return false;
        }

        $authCfg = Model::factory('User_Auth')->query()->filters(
            ['type' => 'ldap', 'user' => $userData['id']]
        )->fetchAll();

        if (empty($authCfg)) {
            return false;
        }

        $authCfg = Orm\Record::factory('User_Auth', $authCfg[0]['id'])->get('config');
        $authCfg = json_decode($authCfg, true);

        $loginSearchFilter = (isset($authCfg['loginSearchFilter']))
            ? $authCfg['loginSearchFilter']
            : $this->config->get('loginSearchFilter');

        $value = null;

        foreach (array('%l', '%d') as $attr) {
            switch ($attr) {
                case '%l':
                    $value = $login;
                    break;
                case '%d':
                    $value = $domain;
                    break;
            }
            if ($value) {
                $loginSearchFilter = str_replace($attr, $value, $loginSearchFilter);
            }
        }

        $bind = @ldap_bind($this->lc, $this->config->get('firstBindDn'), $this->config->get('firstBindPassword'));
        if (!$bind) {
            throw new Exception('Cannot bind to LDAP server: ' . ldap_error($this->lc));
        }

        $res = @ldap_search(
            $this->lc,
            $this->config->get('baseDn'),
            $loginSearchFilter,
            array('dn')
        );
        if (!$res) {
            return false;
        }

        if (ldap_count_entries($this->lc, $res) === 0) {
            return false;
        }

        $userEntry = ldap_get_entries($this->lc, $res);
        $userEntry = $userEntry[0];

        $this->bindStatus = @ldap_bind($this->lc, $userEntry['dn'], $password);
        if (!$this->bindStatus) {
            return false;
        }

        if ($this->config->get('saveCredentials')) {
            $this->saveCredentials(array('dn' => $userEntry['dn'], 'password' => $password));
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
        \Dvelum\Store\Factory::get(\Dvelum\Store\Factory::SESSION, $this->config->get('adapter'))->set(
            'credentials',
            $credentials
        );
    }

    /**
     * Return credentials
     * @return bool|mixed
     */
    private function getCredentials()
    {
        if ($this->config->get('saveCredentials')) {
            return \Dvelum\Store\Factory::get(
                \Dvelum\Store\Factory::SESSION,
                $this->config->get('adapter')
            )->get('credentials');
        } else {
            return false;
        }
    }

    /**
     * Get LDAP connection resource
     * @return bool|resource
     */
    public function getLC()
    {
        if (!$this->bindStatus) {
            $credentials = $this->getCredentials();
            if (!$credentials) {
                throw new \Exception('No saved LDAP credentials! Do User_Auth_Ldap::auth($login, $password) first!');
            }

            $this->bindStatus = @ldap_bind($this->lc, $credentials['dn'], $credentials['password']);
        }
        if (!$this->bindStatus) {
            return false;
        }
        return $this->lc;
    }
}
