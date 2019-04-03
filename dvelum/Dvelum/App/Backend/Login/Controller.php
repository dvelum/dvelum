<?php

declare(strict_types=1);

namespace Dvelum\App\Backend\Login;

use Dvelum\App;
use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;

class Controller extends App\Controller{

    const AUTH_LOGIN = 'ulogin';
    const AUTH_PASSWORD = 'upassword';
    const AUTH_PROVIDER = 'uprovider';

    protected $lang;

    public function __construct(Request $request, Response $response){
        parent::__construct($request, $response);

        $this->lang = Lang::lang();
        $this->init();
    }

    public function init(){
        $action = $this->request->getPart(2);
        if(!$action){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $methodName = $action.'Action';
        if(method_exists($this,$methodName))
            $this->$methodName();
    }

    public function loginAction(){
        $login = $this->request->post(self::AUTH_LOGIN, 'login', false);
        $pass = $this->request->post(self::AUTH_PASSWORD, 'string', false);
        $provider = $this->request->post(self::AUTH_PROVIDER, 'string', $this->appConfig->get('default_auth_provider'));

        if(empty($login) || empty($pass)){
            $this->response->error($this->lang->get('WRONG_USERNAME_PASSWORD'));
        }

        $auth = new App\Auth($this->request, $this->appConfig);

        // slow check
        sleep(1);
        $user = $auth->login($login, $pass, $provider);

        // Trying fallback provider if it set
        if(!$user->isAuthorized() && !empty($this->appConfig->get('fallback_auth_provider'))){
            $provider = $this->appConfig->get('fallback_auth_provider');
            $user = $auth->login($login, $pass, $provider);
        }

        if($user->isAuthorized()){
            $ses =  \Dvelum\Store\Factory::get( \Dvelum\Store\Factory::SESSION);
            $ses->set('auth', true);
            $ses->set('auth_id', $user->getId());
            $this->response->success();
            return;
        }

        $this->response->error($this->lang->get('WRONG_USERNAME_PASSWORD'));
    }

    public function logoutAction(){
        $auth = new App\Auth($this->request, $this->appConfig);

        $auth->logout();
        if (!$this->request->isAjax()) {
            $this->response->redirect($this->request->url([$this->appConfig->get('adminPath')]));
        }
    }
}
