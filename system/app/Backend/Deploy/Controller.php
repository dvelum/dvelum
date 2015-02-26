<?php
class Backend_Deploy_Controller extends Backend_Controller
{
    protected $_deployConfig = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->_deployConfig = Config::factory(Config::File_Array, $this->_configMain['configs'] . 'deploy.php');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Backend_Controller::indexAction()
	 */
	public function indexAction()
	{
        $res = Resource::getInstance();   
        $this->_resource->addInlineJs('
        	var canEdit = '.((integer)$this->_user->canEdit($this->_module)).';
        	var canDelete = '.((integer)$this->_user->canDelete($this->_module)).';
        ');
        
        $res->addJs('/js/app/system/SearchPanel.js'  , 0);   
        $res->addJs('/js/app/system/crud/deploy/application.js'  , 1);
        $res->addJs('/js/app/system/crud/deploy/servers.js'  , 1);
        $res->addJs('/js/app/system/crud/deploy/monitor.js'  , 1);
        $res->addJs('/js/app/system/crud/deploy/history.js'  , 1);
        $res->addJs('/js/app/system/crud/deploy/db.js'  , 1);
        $res->addJs('/js/app/system/crud/deploy/files.js'  , 1);
        $res->addJs('/js/app/system/orm/dataGrid.js'  , 1);
	    $res->addJs('/js/app/system/crud/deploy.js'  , 2);	  		
	}
	
	
	public function lastimprintAction()
	{
		$dataDir = $this->_deployConfig->get('datadir');
		$date = '---';	
		if(file_exists($dataDir.'lastfsupdate'))
			$date = Filter::filterString(file_get_contents($dataDir . 'lastfsupdate'));
			
		Response::jsonSuccess(array('date'=>$date));
	}
	
	/*
	 * Make FS structure map_serversConfig
	 */
	public function imprintAction()
	{
		$this->_checkCanEdit();
		
		$dataDir = $this->_deployConfig->get('datadir');
		$file = $dataDir.'map.php';	
		$api = new Api_Deploy($this->_configMain , $this->_db);
		$api->syncFsAction($file);
		
		$date = date('Y-m-d H:i:s');
		
		if(!$api->syncFsAction($file) || !@file_put_contents($dataDir . 'lastfsupdate' , $date))
			Response::jsonError($this->_lang->CANT_WRITE_FS);
			
		Response::jsonSuccess(array('date'=>$date));
	}
	
	/**
	 * Severs actions router
	 */
	public function serversAction()
	{	
		$subAction = Filter::filterValue('pagecode' , Request::getInstance()->getPart(3));
		if(strlen(!$subAction))
			Response::jsonError($this->_lang->WRONG_REQUEST);
				
		$subAction = 'servers'.ucfirst($subAction).'Action';
		
		if(!method_exists($this, $subAction))
			Response::jsonError($this->_lang->WRONG_REQUEST . $this->_lang->INVALID_ACTION);
			
		if(!file_exists($this->_deployConfig->get('datadir').'servers.php'))
		  Response::jsonError($this->_lang->get('CANT_LOAD') . ' ' . $this->_deployConfig->get('datadir').'servers.php');
		
		$this->_serversConfig = Config::factory(Config::File_Array,$this->_deployConfig->get('datadir').'servers.php');
			
		$this->$subAction();	
	}
		
	/**
	 * View actions router
	 */
	public function viewAction()
	{		
		$subAction = Filter::filterValue('pagecode' , Request::getInstance()->getPart(3));
		if(strlen(!$subAction))
			Response::jsonError($this->_lang->WRONG_REQUEST);
				
		$subAction = 'view'.ucfirst($subAction).'Action';
		
		if(!method_exists($this, $subAction))
			Response::jsonError($this->_lang->WRONG_REQUEST . $this->_lang->INVALID_ACTION);
			
		$this->_serversConfig = Config::factory(Config::File_Array,$this->_deployConfig->get('datadir').'servers.php');
			
		$this->$subAction();	
	}
	
	/**
	 * Get servers list
	 */
	public function serversListAction()
	{	
		$list = $this->_serversConfig->get('list');
		$data = array();
		
		if(!empty($list))
			foreach ($list as $k=>$v)
				$data[] = array('id'=>$k , 'title'=>$v['name']);
		
		Response::jsonSuccess($data);
	}
	
	/**
	 * Get server configuration
	 */
	public function serversConfigAction()
	{
		$serverId = Request::post('id', 'pagecode', false);
		if(!$serverId)
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$list = $this->_serversConfig->get('list');
		
		if(!isset($list[$serverId]))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$data = $list[$serverId];
		$data['id'] = $serverId;	
		Response::jsonSuccess($data);		
	}
	
	/**
	 * Save server config
	 */
	public function serversSaveAction()
	{
		$this->_checkCanEdit();
		
		$serverId = Request::post('id', 'pagecode', '');
		
		if(!strlen($serverId))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$data = Request::postArray();
		unset($data['id']);
		
		if(!empty($data))
			foreach ($data as $k=>&$v)	
				$v = Filter::filterValue('string', $v);
			
		$list = $this->_serversConfig->get('list');
		$list[$serverId] = $data;
		$this->_serversConfig->set('list', $list);
		
		if(!$this->_serversConfig->save())
			Response::jsonError($this->_lang->CANT_WRITE_FS);
		else 
			Response::jsonSuccess();		
	}
	
	/**
	 * Remove server from list
	 */
	public function serversRemoveAction()
	{
		$this->_checkCanDelete();	
		
		$serverId = Request::post('id', 'pagecode', '');
		
		if(!strlen($serverId))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$list = $this->_serversConfig->get('list');
		unset($list[$serverId]);

		$this->_serversConfig->set('list', $list);
		

		if(!$this->_serversConfig->save()){
			Response::jsonError($this->_lang->CANT_WRITE_FS);
		}else {
			if(is_dir($this->_deployConfig->get('datadir').$serverId))
				File::rmdirRecursive($this->_deployConfig->get('datadir').$serverId , true);
			Response::jsonSuccess();
		}		
	}
	
	/**
	 * Get deploy history 
	 */
	public function viewHistoryAction()
	{
		$serverId = Request::post('server_id', 'pagecode', '');
		$data = array();
		
		if(!strlen($serverId))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		if(!is_dir($this->_deployConfig->get('datadir') . $serverId))
			Response::jsonArray($data);

		$list = File::scanFiles($this->_deployConfig->get('datadir') . $serverId, false, false, File::Dirs_Only);
	
		if(!empty($list))
		{
			foreach ($list as $k=>$name)
			{
				$name = basename($name);
				$data[] = array('id'=>$name , 'title'=>$name);
			}	
		}
		
		Response::jsonSuccess($data);		
	}
	
	/**
	 * Request server state
	 * Start background task
	 */
	public function viewSyncAction()
	{
		$serverId = Request::post('server_id', 'pagecode', '');
		$data = array();
		
		if(!strlen($serverId))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$list = $this->_serversConfig->get('list');
		
		if(!isset($list[$serverId]))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		Application::getDbConnection()->getProfiler()->setEnabled(false);	
		$bgStorage = new Bgtask_Storage_Orm(Model::factory('Bgtask'),Model::factory('Bgtask_Signal'));
        $logger = new Bgtask_Log_File($this->_configMain['task_log_path'] . 'sync_'.$serverId.'_'.date('d_m_Y__H_i_s'));
        $tm = Bgtask_Manager::getInstance();
        $tm->setStorage($bgStorage);
        $tm->setLogger($logger);
        $cfg = $list[$serverId];
        $cfg['id'] = $serverId;
        $logger->log('Before launch');
        $tm->launch(Bgtask_Manager::LAUNCHER_JSON, 'Task_Deploy_Sync' , $cfg);    	
	}
	
	/**
	 * Request server db state
	 * Start background task
	 */
	public function viewDbsyncAction()
	{
		$serverId = Request::post('server_id', 'pagecode', '');
		$data = array();
		
		if(!strlen($serverId))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$list = $this->_serversConfig->get('list');
		
		if(!isset($list[$serverId]))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		Application::getDbConnection()->getProfiler()->setEnabled(false);		
		$bgStorage = new Bgtask_Storage_Orm(Model::factory('Bgtask'),Model::factory('Bgtask_Signal'));
        $logger = new Bgtask_Log_File($this->_configMain['task_log_path'] . 'syncdb_'.$serverId.'_'.date('d_m_Y__H_i_s'));
        $tm = Bgtask_Manager::getInstance();
        $tm->setStorage($bgStorage);
        $tm->setLogger($logger);
        $cfg = $list[$serverId];
        $cfg['id'] = $serverId;
        $tm->launch(Bgtask_Manager::LAUNCHER_JSON, 'Task_Deploy_Dbsync' , $cfg);  	
	}
	
	/**
	 * Create archive
	 */
	public function viewMakeAction()
	{
		$serverId = Request::post('server_id', 'pagecode', '');
		$update = Request::post('update_files', 'array', array());
		$delete = Request::post('delete_files', 'array', array());
		
		if(!strlen($serverId))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		if(empty($update) && empty($delete))
			Response::jsonError($this->_lang->WRONG_REQUEST);
					
		$bgStorage = new Bgtask_Storage_Orm(Model::factory('bgtask'),Model::factory('Bgtask_Signal'));
        $logger = new Bgtask_Log_File($this->_configMain['task_log_path'] . 'archive_'.$serverId.'_'.date('d_m_Y__H_i_s'));
        $tm = Bgtask_Manager::getInstance();
        $tm->setStorage($bgStorage);
        $tm->setLogger($logger);
        
        $cfg = array(
        	'server'=>$serverId,
        	'files'=>$update,
        	'files_delete'=>$delete
        );
        
        $tm->launch(Bgtask_Manager::LAUNCHER_JSON, 'Task_Deploy_Archive' , $cfg);  		
	}
	
	/**
	 * Get files info
	 */
	public function viewFilesAction()
	{
		$serverId = Request::post('server_id', 'pagecode', '');
		$data = array();
		
		if(!strlen($serverId))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$serverDir = $this->_deployConfig->get('datadir').$serverId.'/';	
		
		$date = '---';
		
		if(file_exists($serverDir.'lastfsupdate'))
			$date = Filter::filterString(file_get_contents($serverDir.'lastfsupdate'));
		
		$data = array(
			'updated'=>array(),
			'deleted'=>array(),
			'date'=>$date
		);
			
		$dataDir = $this->_deployConfig->get('datadir');
		
		$repoMapfile = $dataDir.'map.php';	
		$serverMapfile = $serverDir.'map.php';

		
		if(file_exists($repoMapfile) && file_exists($serverMapfile))
		{
			$repoMap = include $repoMapfile;						
			$liveMap = include $serverMapfile;
			
			$validated = $this->_validateStructure($repoMap, $liveMap);	

			$data['updated'] = $this->_fillChilds(Utils::fileListToTree($validated['send']));
			$delete = array();
			
			if(!empty($validated['remove']))
					foreach ($validated['remove'] as $file)
						$delete[] = array('id'=>$file);
			
			$data['deleted'] = $delete;
		}

		Response::jsonSuccess($data);	
	}
	
	public function viewDbAction()
	{
		$serverId = Request::post('server_id', 'pagecode', '');
		$data = array();
		
		if(!strlen($serverId))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$serverDir = $this->_deployConfig->get('datadir').$serverId.'/';	
		$info = array();
		$date = '---';
		
		if(file_exists($serverDir.'lastfsupdate'))
			$date = Filter::filterString(@file_get_contents($serverDir.'lastdbupdate'));
				
		if(file_exists($serverDir.'db.php'))	
			$info = include $serverDir.'db.php';
			
		$data = array(
			'info'=>$info,
			'date'=>$date
		);	
		Response::jsonSuccess($data);	
	}
	
	
	protected function _validateStructure(array $repoMap , array $liveMap)
	{
		$result = array();	
		$delete = array();	
		
		foreach ($repoMap as $key=>$data)			
			if(!isset($liveMap[$key]) || ($data['md5']!=$liveMap[$key]['md5']))	
				$result[] = $data['file'];	

		foreach ($liveMap as $k=>$data)			
			if(!isset($repoMap[$k]))
				$delete[] = $data['file'];
					
		return array('send'=>$result,'remove'=>$delete);
	}

	/**
     * Fill childs data array for tree panel
     * @param Tree $tree
     * @param mixed $root
     * @return array
     */
	protected function _fillChilds(Tree $tree , $root = 0)
	{
		$result = array();
		$childs = $tree->getChilds($root);
		
		if(empty($childs))
			return array();
		
		foreach($childs as $k => $v)
		{
			$row = $v['data'];
			$obj = new stdClass();
			
			$obj->id = $v['id'];
			$obj->text = $v['data'];
			$obj->expanded = false;
			$obj->checked = false;
			$obj->allowDrag = false;
			
			if(strpos($obj->text , '.') && !$tree->hasChilds($v['id']))
			{
				$obj->leaf = true;
			}
			else
			{
				$obj->leaf = false;
			}
			
			$cld = array();
			if($tree->hasChilds($v['id']))
				$cld = $this->_fillChilds($tree , $v['id']);
			
			$obj->children = $cld;
			$result[] = $obj;
		}
		return $result;
	}

}