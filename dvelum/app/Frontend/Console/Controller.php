<?php
class Frontend_Console_Controller extends Frontend_Controller
{
	/**
	 * Logs adapter
	 * @var Log
	 */
	protected $_log = false;
	/**
	 * Cron User
	 * @var User
	 */
	protected $_user;
	/**
	 * Cronjobs configuration
	 * @var Config_Abstract
	 */
	protected $_configs;
	/**
	 * Launcher configuration
	 * @var array
	 */
	protected $_cronConfig;
	/**
	 * Action routes
	 * @var array $_actions
	 */
	protected $_actions;

	public function __construct()
	{
		if(!defined('DVELUM_CONSOLE')){
			Response::redirect('/');
			exit;
		}

		parent::__construct();

		$this->_configs = Config::storage()->get('cronjob.php');

        // Prepare action routes
		$actions = Config::storage()->get('console.php');
		foreach($actions as $k=>$v){
			$this->_actions[strtolower($k)] = $v;
		}

		$this->_cronConfig = $this->_configs->get('config');

		if($this->_cronConfig['log_file'])
			$this->_log = new Log_File($this->_cronConfig['log_file']);

		$this->_authorize();
	}

	/**
	 * Authorize as system user
	 */
	protected function _authorize()
	{
	    $userId = $this->_cronConfig['user_id'];
		if($this->_cronConfig['user_id'] && Model::factory('User')->getCount(array('id'=>$userId))){
			$curUser = User::getInstance();
			$curUser->setId($userId);
			$curUser->setAuthorized();
			$this->_user = $curUser;
		}else{
			$this->_logMessage('Cron  cant\'t authorize');
		}
	}
	/**
	 * Log message
	 * @param string $text
	 */
	protected function _logMessage($text)
	{
		if($this->_log)
			$this->_log->log(get_called_class() . ':: ' . $text);
	}

   /**
    * Launch background task using file lock
    * console command ./console.php /console/task/[task name]/[time limit]/[thread]
    * @param string $name
    * @param array $params
    */
    protected function _launchTask($name, $params)
    {
        $thread = 0;

        if(isset($params[1]))
            $thread = $params[1];

        if($thread)
            $threadName = $name . $thread;
        else
            $threadName = $name;

        $appCfg = $this->_configs->get($name);
        $appCfg['thread'] = $thread;
        $appCfg['params'] = $params;

        $adapter = $appCfg['adapter'];

        if(isset($params[0])){
            $this->_cronConfig['time_limit'] = intval($params[0]);
            $this->_cronConfig['intercept_limit'] = intval($params[0]);
        }

        $lock = new Cron_Lock($this->_cronConfig);

        if($this->_log)
            $lock->setLogsAdapter($this->_log);

        if (!$lock->launch($threadName))
            exit();

        $appCfg['lock'] = $lock;

        $bgStorage = new Bgtask_Storage_Orm(Model::factory('Bgtask'), Model::factory('Bgtask_Signal'));

        $tManager = Bgtask_Manager::getInstance();
        $tManager->setStorage($bgStorage);

        if($this->_log)
            $tManager->setLogger($this->_log);

        $tManager->launch(Bgtask_Manager::LAUNCHER_SILENT, $adapter, $appCfg);

        $lock->finish();
    }

    /**
     * Launch job using file lock
     * console command ./console.php /console/job/[job name]/[time limit]/[thread]
     * @param string $name
     * @param array $params - job params
     * @param string $method
     */
    protected function _launchJob($name, array $params, $method = 'run')
    {
    	$appCfg = $this->_configs->get($name);

        $appCfg['params'] = $params;
    	$appCfg['thread'] = 0;

    	if (isset($params[0])) {
    		$this->_cronConfig['time_limit'] =  intval($params[0]);
    		$this->_cronConfig['intercept_timeout'] =  intval($params[0]);
    	}

        if(isset($params[1]))
            $appCfg['thread'] = $params[1];

    	$lock = new Cron_Lock($this->_cronConfig);

    	if($this->_log)
    	   $lock->setLogsAdapter($this->_log);

    	$adapter = $appCfg['adapter'];

    	$config = new Config_Simple($name . '_job');
    	$config->setData($appCfg);
    	$config->set('lock', $lock);

    	$o = new $adapter($config);

    	if (!$o->$method())
    		$msg = '1 ' . $name . '_job' . ': error';
    	else
    		$msg = '0 ' . $name . '_job' . ': ' . $o->getStatString();

    	$this->_logMessage($msg);

    	echo $msg . "\n";

    	$lock->finish();
    }

    public function indexAction()
    {
        $request = Request::getInstance();
		$action = strtolower($request->getPart(1));

		if(!empty($action) && isset($this->_actions[$action]))
        {
            $adapterCls = $this->_actions[$action];
            if(!class_exists($adapterCls)){
                trigger_error('Undefined Action Adapter ' . $adapterCls);
            }
            $adapter = new $adapterCls;
            if(!$adapter instanceof Console_Action){
                trigger_error($adapterCls.' is not instance of Console_Action');
            }
            $params = $request->getPathParts(2);
            $adapter->init($this->_configMain, $params);
        }else{
            echo 'Undefined Action';
        }
        Application::close();
    }

    public function taskAction()
    {
        $request = Request::getInstance();
        $action = $request->getPart(2);

        if($this->_configs->offsetExists($action)){
            $params = $request->getPathParts(3);
            $this->_launchTask($action, $params);
        }else{
            echo 'Undefined Task';
        }
        Application::close();
    }

    /**
     * Launch Cron Job
     */
    public function jobAction()
    {
        $request = Request::getInstance();
        $action = $request->getPart(2);

        if($this->_configs->offsetExists($action)){
            $params = $request->getPathParts(3);
            $this->_launchJob($action, $params , 'run');
        }else{
            echo 'Undefined Job';
        }
        Application::close();
    }
}