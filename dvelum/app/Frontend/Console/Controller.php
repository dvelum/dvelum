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

	public function __construct()
	{
		if(!defined('DVELUM_CONSOLE')){
			Response::redirect('/');
			exit;
		}

		parent::__construct();

		$this->_configs = Config::factory(Config::File_Array , $this->_configMain->get('configs') . 'cronjob.php');
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
    * Launch background task
    * @param string $name
    */
    protected function _launchTask($name)
    {
        $thread = Request::getInstance()->getPart(3);
        $timeLimit = intval(Request::getInstance()->getPart(2));

        if($thread)
            $threadName = $name . $thread;
        else
            $threadName = $name;

        $appCfg = $this->_configs->get($name);
        $appCfg['thread'] = $thread;

        $adapter = $appCfg['adapter'];


        if($timeLimit){
            $this->_cronConfig['time_limit'] = $timeLimit;
            $this->_cronConfig['intercept_limit'] = $timeLimit;
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
     * Launch job  using file lock
     * console command ./console.php /console/[task]/[time limit]/[thread]
     * @param string $name
     * @param string $method
     */
    protected function _launchJob($name, $method = 'run')
    {
    	$appCfg = $this->_configs->get($name);
    	$time = Request::getInstance()->getPart(2);
    	$thread = intval(Request::getInstance()->getPart(3));

    	$appCfg['thread'] = $thread;

    	if ($time) {
    		$this->_cronConfig['time_limit'] = $time;
    		$this->_cronConfig['intercept_timeout'] = $time;
    	}

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
        Response::redirect('/');
		Application::close();
    }
    /**
     * Remove obsolete Bgtask data
     */
    public function clearmemoryAction()
    {
    	$this->_launchTask('clearmemory');
    }

	/**
	 * Generate new version fo documentation
	 */
    public function gendocAction()
    {
        if(!$this->_configMain->get('development')){
        	echo 'Use development mode';
        }

		ini_set('memory_limit' , '256M');

		$part = intval(Request::getInstance()->getPart(2));

        $sysdocsCfg = Config::factory(Config::File_Array, $this->_configMain->get('configs') . 'sysdocs.php');
        $sysdocs = new Sysdocs_Generator($sysdocsCfg);
		$sysdocs->setAutoloaderPaths($this->_configMain->get('autoloader')['paths']);

		if($part === 'locale'){
			$sysdocs->migrateLocale();
		}else{
			$sysdocs->run();
		}

		Application::close();
    }

	/**
	 * Rebuild ORM objects
	 */
	public function ormMigrateAction()
	{
		$dbObjectManager = new Db_Object_Manager();
		foreach($dbObjectManager->getRegisteredObjects() as $object)
		{
			echo 'build ' . $object . ' : ';
			$builder = new Db_Object_Builder($object);
			if($builder->build()){
				echo 'OK';
			}else{
				echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
			}
			echo "\n";
		}
		Application::close();
	}

    // Demo actions
	public function sometaskAction()
	{
		$this->_launchTask('sometask');
	}

	public function somejobAction()
	{
		$this->_launchJob('somejob');
	}
}