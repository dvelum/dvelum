<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
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
use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Model;
use Dvelum\App\Router;
use Dvelum\Request;
use Dvelum\Response;

class Controller extends App\Controller implements Router\RouterInterface
{
    /**
     * Logs adapter
     * @var \Log
     */
    protected $log = false;
    /**
     * Cron User
     * @var \User
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
        if (!defined('DVELUM_CONSOLE')) {
            $this->response->redirect('/');
            exit;
        }

        parent::__construct($request, $response);

        $this->consoleConfig = Config::storage()->get('console.php');
        // Prepare action routes
        $actions = Config::storage()->get('console_actions.php');

        foreach ($actions as $k => $v) {
            $this->actions[strtolower($k)] = $v;
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
            $curUser = \User::getInstance();
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
            $this->log->log(get_called_class() . ':: ' . $text);
        }
    }

    /**
     * Launch background task using file lock
     * console command ./console.php /console/task/[task name]/[time limit]/[thread]
     * @param string $name
     * @param array $params
     */
    protected function launchTask($name, $params)
    {
        $thread = 0;

        if (isset($params[1])) {
            $thread = $params[1];
        }

        if ($thread) {
            $threadName = $name . $thread;
        } else {
            $threadName = $name;
        }

        $appCfg = $this->tasks->get($name);
        $appCfg['thread'] = $thread;
        $appCfg['params'] = $params;

        $adapter = $appCfg['adapter'];

        if (isset($params[0])) {
            $this->cronConfig['time_limit'] = intval($params[0]);
            $this->cronConfig['intercept_limit'] = intval($params[0]);
        }

        $lock = new \Cron_Lock($this->cronConfig);

        if ($this->log) {
            $lock->setLogsAdapter($this->log);
        }

        if (!$lock->launch($threadName)) {
            exit();
        }

        $appCfg['lock'] = $lock;

        $bgStorage = new \Bgtask_Storage_Orm(Model::factory('Bgtask'), Model::factory('Bgtask_Signal'));

        $tManager = \Bgtask_Manager::getInstance();
        $tManager->setStorage($bgStorage);

        if ($this->log) {
            $tManager->setLogger($this->log);
        }

        $tManager->launch(\Bgtask_Manager::LAUNCHER_SILENT, $adapter, $appCfg);

        $lock->finish();
    }

    /**
     * Launch job using file lock
     * console command ./console.php /console/job/[job name]/[time limit]/[thread]
     * @param string $name
     * @param array $params - job params
     * @param string $method
     */
    protected function launchJob($name, array $params, $method = 'run')
    {
        $appCfg = $this->tasks->get($name);

        $appCfg['params'] = $params;
        $appCfg['thread'] = 0;

        if (isset($params[0])) {
            $this->consoleConfig['time_limit'] = intval($params[0]);
            $this->consoleConfig['intercept_timeout'] = intval($params[0]);
        }

        if (isset($params[1])) {
            $appCfg['thread'] = $params[1];
        }

        $lock = new \Cron_Lock($this->cronConfig);

        if ($this->log) {
            $lock->setLogsAdapter($this->log);
        }

        $adapter = $appCfg['adapter'];

        $config = Config\Factory::config(Config\Factory::Simple, $name . '_job');
        $config->setData($appCfg);
        $config->set('lock', $lock);

        $o = new $adapter($config);

        if (!$o->$method()) {
            $msg = '1 ' . $name . '_job' . ': error';
        } else {
            $msg = '0 ' . $name . '_job' . ': ' . $o->getStatString();
        }

        $this->logMessage($msg);

        echo $msg . "\n";

        $lock->finish();
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

        switch ($actionConfig['type']) {
            case 'action' :
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
                break;
            case 'job' :
                break;
            case 'task' :
                break;
        }
    }

    public function taskAction()
    {
        $action = $this->request->getPart(1);

        if ($this->configs->offsetExists($action)) {
            $params = $this->request->getPathParts(2);
            $this->launchTask($action, $params);
        } else {
            echo 'Undefined Task';
        }
        $this->response->send();
    }

    /**
     * Launch Cron Job
     */
    public function jobAction()
    {
        $action = $this->request->getPart(1);

        if ($this->configs->offsetExists($action)) {
            $params = $this->request->getPathParts(2);
            $this->launchJob($action, $params, 'run');
        } else {
            echo 'Undefined Job';
        }
        $this->response->send();
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