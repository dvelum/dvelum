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

namespace Dvelum\App\Console;

use Dvelum\App;
use Dvelum\Orm\Model;
use Dvelum\Request;
use Dvelum\Response;

class PlatformController extends Controller
{
    /**
     * Cron User
     * @var App\Session\User
     */
    protected $user;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->authorize();
    }

    /**
     * Authorize as system user
     */
    protected function authorize()
    {
        $userId = $this->consoleConfig->get('user_id');
        if ($userId && Model::factory('User')->query()->filters(['id' => $userId])->getCount()) {
            $curUser = App\Session\User::factory();
            $curUser->setId($userId);
            $curUser->setAuthorized();
            $this->user = $curUser;
        } else {
            $this->response->error('Cron  cant\'t authorize');
        }
    }
}