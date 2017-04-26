<?php

namespace Dvelum\App\Backend;

use Dvelum\App;
use Dvelum\Config;
use Dvelum\Request;
use Dvelum\Orm\Model;

class Auth
{
    const AUTH_LOGIN = 'ulogin';
    const AUTH_PASSWORD = 'upassword';
    const AUTH_PROVIDER = 'uprovider';
    const AUTH_LANG = 'ulang';

    protected  $request;
    protected  $appConfig;

    public function __construct(Request $request, Config\Adapter $appConfig)
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