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

namespace Dvelum\App\Frontend\Console;

use Dvelum\App;
use Dvelum\Config;
use Dvelum\Log;
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
     * Cron jobs configuration
     * @var ConfigInterface
     */
    protected $configs;
    /**
     * Launcher configuration
     * @var array
     */
    protected $cronConfig;
    /**
     * Action routes
     * @var array $actions
     */
    protected $actions;

    public function __construct(Request $request, Response $response)
    {
        if(!defined('DVELUM_CONSOLE')){
            $this->response->redirect('/');
            exit;
        }

        parent::__construct( $request, $response);

        $this->configs = Config::storage()->get('cronjob.php');

        // Prepare action routes
        $actions = Config::storage()->get('console.php');

        foreach($actions as $k=>$v){
            $this->actions[strtolower($k)] = $v;
        }

        $this->cronConfig = $this->configs->get('config');

        if($this->cronConfig['log_file'])
            $this->log = new Log\File($this->cronConfig['log_file']);

        $this->authorize();
    }

    /**
     * Authorize as system user
     */
    protected function authorize()
    {
        $userId = $this->cronConfig['user_id'];
        if($this->cronConfig['user_id'] && Model::factory('User')->getCount(array('id'=>$userId))){
            $curUser = \User::getInstance();
            $curUser->setId($userId);
            $curUser->setAuthorized();
            $this->user = $curUser;
        }else{
            $this->logMessage('Cron  cant\'t authorize');
        }
    }
    /**
     * Log message
     * @param string $text
     */
    protected function logMessage($text)
    {
        if($this->log)
            $this->log->log(get_called_class() . ':: ' . $text);
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

        if(isset($params[1]))
            $thread = $params[1];

        if($thread)
            $threadName = $name . $thread;
        else
            $threadName = $name;

        $appCfg = $this->configs->get($name);
        $appCfg['thread'] = $thread;
        $appCfg['params'] = $params;

        $adapter = $appCfg['adapter'];

        if(isset($params[0])){
            $this->cronConfig['time_limit'] = intval($params[0]);
            $this->cronConfig['intercept_limit'] = intval($params[0]);
        }

        $lock = new \Cron_Lock($this->cronConfig);

        if($this->log)
            $lock->setLogsAdapter($this->log);

        if (!$lock->launch($threadName))
            exit();

        $appCfg['lock'] = $lock;

        $bgStorage = new \Bgtask_Storage_Orm(Model::factory('Bgtask'), Model::factory('Bgtask_Signal'));

        $tManager = \Bgtask_Manager::getInstance();
        $tManager->setStorage($bgStorage);

        if($this->log)
            $tManager->setLogger($this->log);

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
        $appCfg = $this->configs->get($name);

        $appCfg['params'] = $params;
        $appCfg['thread'] = 0;

        if (isset($params[0])) {
            $this->cronConfig['time_limit'] =  intval($params[0]);
            $this->cronConfig['intercept_timeout'] =  intval($params[0]);
        }

        if(isset($params[1]))
            $appCfg['thread'] = $params[1];

        $lock = new \Cron_Lock($this->cronConfig);

        if($this->log)
            $lock->setLogsAdapter($this->log);

        $adapter = $appCfg['adapter'];

        $config = Config\Factory::config(Config\Factory::Simple,$name . '_job');
        $config->setData($appCfg);
        $config->set('lock', $lock);

        $o = new $adapter($config);

        if (!$o->$method())
            $msg = '1 ' . $name . '_job' . ': error';
        else
            $msg = '0 ' . $name . '_job' . ': ' . $o->getStatString();

        $this->logMessage($msg);

        echo $msg . "\n";

        $lock->finish();
    }

    /**
     * Run action
     * @param Request $request
     * @param Response $response
     */
    public function route(Request $request , Response $response) : void
    {
        $this->response = $response;
        $this->request = $request;
        $this->indexAction();
    }

    public function indexAction()
    {
        $action = strtolower($this->request->getPart(1));

        if(!empty($action) && isset($this->actions[$action]))
        {
            $adapterCls = $this->actions[$action];
            if(!class_exists($adapterCls)){
                trigger_error('Undefined Action Adapter ' . $adapterCls);
            }
            $adapter = new $adapterCls;
            if(!$adapter instanceof \Console_Action){
                trigger_error($adapterCls.' is not instance of Console_Action');
            }
            $params = $this->request->getPathParts(1);
            $adapter->init($this->appConfig, $params);
        }else{
            echo 'Undefined Action';
        }
        $this->request;
    }

    public function taskAction()
    {
        $action = $this->request->getPart(1);

        if($this->configs->offsetExists($action)){
            $params = $this->request->getPathParts(2);
            $this->launchTask($action, $params);
        }else{
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

        if($this->configs->offsetExists($action)){
            $params = $this->request->getPathParts(2);
            $this->launchJob($action, $params , 'run');
        }else{
            echo 'Undefined Job';
        }
        $this->response->send();
    }
}