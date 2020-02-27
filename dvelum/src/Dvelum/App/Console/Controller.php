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
use Dvelum\Config;
use Dvelum\Log\LogInterface;
use Dvelum\Orm\Model;
use Dvelum\App\Router;
use Dvelum\Request;
use Dvelum\Response;
use PHPUnit\Framework\Exception;
use Psr\Log\LogLevel;

class Controller extends App\Controller implements Router\RouterInterface
{
    /**
     * Logs adapter
     * @var LogInterface
     */
    protected $log = false;
    /**
     * Cron User
     * @var App\Session\User
     */
    protected $user;

    /**
     * Launcher configuration
     * @var array
     */
    protected $consoleConfig;
    /**
     * Action routes
     * @var array $actions
     */
    protected $actions;

    public function __construct(Request $request, Response $response)
    {
        throw new Exception('not implemented yet');
        if (!defined('DVELUM_CONSOLE')) {
            $this->response->redirect('/');
            exit;
        }

        parent::__construct($request, $response);

        $this->consoleConfig = Config::storage()->get('console.php');
        // Prepare action routes
        $data = Config::storage()->get('console_actions.php')->__toArray();
        foreach ($data as $action => $config){
            $this->actions[strtolower($action)] = $config;
        }
        $log = $this->consoleConfig->get('log');

        if ($log['enabled']) {
            switch ($log['type']) {
                case 'file' :
                    $this->log = new \Dvelum\Log\File($log['logFile']);
                    break;
            }
        }
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
            $this->logMessage('Cron  cant\'t authorize');
        }
    }

    /**
     * Log message
     * @param string $text
     */
    protected function logMessage($text)
    {
        if ($this->log) {
            $this->log->log(LogLevel::ERROR, get_called_class() . ' :: '. $text);
        }
    }

    /**
     * Run action
     * @param Request $request
     * @param Response $response
     */
    public function route(Request $request, Response $response): void
    {
        $this->response = $response;
        $this->request = $request;
        $this->indexAction();
    }

    public function indexAction()
    {
        $action = strtolower($this->request->getPart(0));

        if (empty($action) || !isset($this->actions[$action])) {
            $this->response->put('Undefined Action');
            return;
        }

        $actionConfig = $this->actions[$action];
        $adapterCls = $actionConfig['adapter'];

        if (!class_exists($adapterCls)) {
            trigger_error('Undefined Action Adapter ' . $adapterCls);
        }

        $adapter = new $adapterCls($actionConfig);

        if (!$adapter instanceof \Dvelum\App\Console\ActionInterface) {
            trigger_error($adapterCls . ' is not instance of ActionInterface');
        }

        $params = $this->request->getPathParts(1);
        $config = [];

        if(isset($actionConfig['config'])){
            $config = $actionConfig['config'];
        }

        $adapter->init($this->appConfig, $params , $config);
        $result = $adapter->run();

        echo '[' . $action . ' : ' . $adapter->getInfo() . ']' . PHP_EOL;

        if ($result) {
            exit(0);
        } else {
            exit(1);
        }
    }

    /**
     * Find url
     * @param string $module
     * @return string
     */
    public function findUrl(string $module): string
    {
        return '';
    }
}