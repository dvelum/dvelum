<?php

/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
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
 *
 */
declare(strict_types=1);

namespace Dvelum\App\Frontend\Controller;

use Dvelum\App\Frontend\Controller;
use Dvelum\App\Session\User;
use Dvelum\Config;
use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;

abstract class Authorised extends Controller
{
    /**
     * User instance
     * @var User
     */
    protected $user;

    protected $authorised = false;

    public function __construct(Request $request, Response $response)
    {
        $this->frontendConfig = Config::storage()->get('frontend.php');
        $this->lang = Lang::lang();
        parent::__construct($request, $response);

        if ($this->request->get('logout', 'boolean', false)) {
            User::factory()->logout();
            session_destroy();
            if (!$this->request->isAjax()) {
                $this->response->redirect($this->request->url(['index']));
                return;
            }
        }
        $this->checkAuth();
    }

    /**
     * Check user permissions and authentication
     */
    public function checkAuth()
    {
        $user = User::factory();
        $uid = false;

        if ($user->isAuthorized()) {
            $uid = $user->getId();
        }

        if (!$uid) {
            if ($this->request->isAjax()) {
                $this->response->error($this->lang->get('MSG_AUTHORIZE'));
                return;
            } else {
                $this->loginAction();
            }
        }
        /*
         * Check CSRF token
        */
        if ($this->frontendConfig->get('use_csrf_token') && $this->request->hasPost()) {
            $csrf = new \Dvelum\Security\Csrf();
            $csrf->setOptions(
                [
                    'lifetime' => $this->frontendConfig->get('use_csrf_token_lifetime'),
                    'cleanupLimit' => $this->frontendConfig->get('use_csrf_token_garbage_limit')
                ]
            );

            if (!$csrf->checkHeader() && !$csrf->checkPost()) {
                $this->errorResponse($this->lang->get('MSG_NEED_CSRF_TOKEN'));
                return;
            }
        }
        $this->user = $user;
        $this->authorised = true;
    }

    public function isAuthorised(): bool
    {
        return $this->authorised;
    }

    /**
     * Send JSON error message
     *
     * @return void
     */
    protected function errorResponse($msg)
    {
        if ($this->request->isAjax()) {
            $this->response->error($msg);
        } else {
            $this->response->redirect($this->request->url(['index']));
        }
    }

    /**
     * Show login form
     */
    protected function loginAction()
    {
        $template = \Dvelum\View::factory();
        $template->set('wwwRoot', $this->appConfig->get('wwwroot'));
        $template->resource = \Dvelum\Resource::factory();
        $this->response->put($template->render('public/backoffice_login.php'));
    }
}
