<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
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
 */

namespace Dvelum\App;

use Dvelum\App;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Request;

class Auth
{
    const AUTH_LOGIN = 'ulogin';
    const AUTH_PASSWORD = 'upassword';
    const AUTH_PROVIDER = 'uprovider';
    const AUTH_LANG = 'ulang';

    protected  $request;
    protected  $appConfig;

    public function __construct(Request $request, ConfigInterface $appConfig)
    {
        $this->request = $request;
        $this->appConfig = $appConfig;

        $this->checkLogout();
    }

    public function checkLogout()
    {
        return $this->request->get('logout' , 'boolean' , false);
    }

    public function logout()
    {
        App\Session\User::factory()->logout();
        session_destroy();
    }


    /**
     * Check user permissions and authentication
     * @return App\Session\User
     */
    public function auth() : App\Session\User
    {
        $user =  App\Session\User::factory();

        if(!$user->isAuthorized()){
            $login = $this->request->post(self::AUTH_LOGIN, 'login', false);
            $pass = $this->request->post(self::AUTH_PASSWORD , 'string' , false);
            $provider = $this->request->post(self::AUTH_PROVIDER , 'string' , $this->appConfig->get('default_auth_provider'));
            $language = $this->request->post(self::AUTH_LANG, 'string' , '');

            if(!empty($login) && !empty($pass)){
                // slow check
                sleep(1);
                $user = $this->login($login, $pass , $provider);

                // Trying fallback provider if it set
                if(!$user->isAuthorized() && !empty($this->appConfig->get('fallback_auth_provider'))){
                    $provider = $this->appConfig->get('fallback_auth_provider');
                    $user = $this->login($login , $pass , $provider);
                }

                if($user->isAuthorized()){
                    $ses = \Store::factory(\Store::Session);
                    $ses->set('auth' , true);
                    $ses->set('auth_id', $user->getId());
                }

            }else{
                $ses = \Store::factory(\Store::Session);

                if($ses->keyExists('auth') && $ses->get('auth') && $ses->keyExists('auth_id')){
                    $user->setId($ses->get('auth_id'));
                    $user->setAuthorized();
                }
            }
        }
        return $user;
    }

    /**
     * Login as user
     * @param string $login
     * @param string $password
     * @param string $provider
     * @throws \Exception
     * @return App\Session\User
     */
    public function login(string $login, string $password, string $provider = 'dvelum') : App\Session\User
    {
        $user = App\Session\User::factory();

        $providerCfg = Config::storage()->get('auth/' . $provider . '.php', false, true);

        if (!$providerCfg)
            throw new \Exception('Wrong auth provider config: ' . 'auth/' . $provider . '.php');

        $authProvider = \User_Auth::factory($providerCfg);

        if (!$authProvider->auth($login, $password))
            return $user;

        $data = $authProvider->getUserData();

        if(!empty($data)){
            $user->setId($data['id']);
            $user->setInfo($data);
            $user->setAuthProvider($authProvider);
            $user->setAuthorized();
        }

        return $user;
    }
}