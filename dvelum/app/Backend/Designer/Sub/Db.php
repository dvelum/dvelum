<?php
/**
 * @deprecated since 0.9.1
 */
class Backend_Designer_Sub_Db extends Backend_Designer_Sub
{
	/**
	 * Database connections config
	 * @var Config_File_Array
	 */
	protected $_connConfig;
	
	public function __construct(){
		parent::__construct();
		$this->_connConfig = Config::factory(Config::File_Array, $this->_config->get('connections'));
	}
	/**
	 * Get Db connections list
	 */
	public function listAction()
	{
		$result = array();
			foreach ($this->_connConfig as $index=>$data){
				$data['id'] = $index;
				$result[] = $data;
			}
				
		Response::jsonSuccess($result);		
	}
	/**
	 * Load database connection info
	 */
	public function loadAction(){
		$id = Request::post('id','integer', false);
		
		if($id === false || !$this->_connConfig->offsetExists($id))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$data = $this->_connConfig->get($id);
		unset($data['pass']);
		$data['id'] = $id;
		Response::jsonSuccess($data);	
	}
	
	/**
	 * Remove Database connection
	 */
	public function removeAction()
	{
		$id = Request::post('id','integer', false);
		
		if($id === false)
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$this->_connConfig->remove($id);
			
		if($this->_connConfig->save())
			Response::jsonSuccess();
		else 
			Response::jsonError($this->_lang->CANT_WRITE_FS);		
	}
	/**
	 * Test database connection
	 */
	public function testAction()
	{
			$pass = Request::post('pass', 'string', false);
			$host = Request::post('host', 'string', false);
			$user = Request::post('user', 'string', false);
			$base = Request::post('base', 'string', false);
			$id = Request::post('id', 'integer', false);
			$setpass = Request::post('setpass', 'boolean', false);
				
			$config = array(
					'username' => $user,
					'password' => $pass,
					'dbname' => $base,
					'host' => $host,
				    'charset' => 'UTF8'
			);

			if($id!==false && $id >=0 && !$setpass)
			{
				if(!$this->_connConfig->offsetExists($id))
					Response::jsonError($this->_lang->WRONG_REQUEST);
					
				$oldCfg = $this->_connConfig->get($id);
				$config['password']	 = $oldCfg['pass'];
			}

			try{
				$db = Zend_Db::factory('Mysqli' , $config);
				$db->query('SET NAMES UTF8');
				Response::jsonSuccess();
			}catch (Exception $e){
				Response::jsonError($this->_lang->CANT_CONNECT);
			}	
	}
	/**
	 * Save database connection config
	 */
	public function saveAction(){
			$name = Request::post('name', 'string', false);
			$pass = Request::post('pass', 'string', false);
			$host = Request::post('host', 'string', false);
			$user = Request::post('user', 'string', false);
			$base = Request::post('base', 'string', false);
			$id = Request::post('id', 'integer', false);
			$setpass = Request::post('setpass', 'boolean', false);
				
			$config = array(
					'user' => $user,
					'pass' => $pass,
					'base' => $base,
					'host' => $host,
					'name' => $name
			);
			
			if($id!==false && $id >=0 )
			{
				if(!$this->_connConfig->offsetExists($id))
					Response::jsonError($this->_lang->WRONG_REQUEST);
				if(!$setpass){
					$oldCfg = $this->_connConfig->get($id);
					$config['pass']	 = $oldCfg['pass'];
				}
			}else{
				$id = $this->_connConfig->getCount();
				while ($this->_connConfig->offsetExists($id))
					$id++;
			}
			
			$this->_connConfig->set($id, $config);	
			
			if($this->_connConfig->save())
				Response::jsonSuccess();
			else 
				Response::jsonError($this->_lang->CANT_WRITE_FS);			
	}
	public function connectionslistAction(){
		$data = array();
		foreach($this->_connConfig as $index => $v){
			$data[] = array('id'=>$index,'title'=>$v['name']);
		}
		Response::jsonSuccess($data);
	}
	public function tableslistAction(){
		$connectionId = Request::post('connId', 'integer', false);
		
		if($connectionId === false)
			Response::jsonError($this->_lang->WRONG_REQUEST);
		
		$config = $this->_connConfig->get($connectionId);
		$config = array(
				'username' => $config['user'],
				'password' => $config['pass'],
				'dbname' => $config['base'],
				'host' => $config['host'],
			    'charset' => 'UTF8'
		);
		
		try{
			$db = Zend_Db::factory('Mysqli' , $config);
		}catch (Exception $e){
			Response::jsonError($this->_lang->CANT_CONNECT);
		}
		$data = array();
		$list = $db->listTables();
		
		foreach($list as $v)
			$data[] = array('id'=>$v,'title'=>$v);
			
		Response::jsonSuccess($data);
	}
	public function fieldslistAction(){
		$connectionId = Request::post('connId', 'integer', false);
		$table = Request::post('table','string',false);
		
		if($connectionId === false || !$table)
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$config = $this->_connConfig->get($connectionId);
		$config = array(
				'username' => $config['user'],
				'password' => $config['pass'],
				'dbname' => $config['base'],
				'host' => $config['host'],
			    'charset' => 'UTF8'
		);
		
		try{
			$db = Zend_Db::factory('Mysqli' , $config);
		}catch (Exception $e){
			Response::jsonError($this->_lang->CANT_CONNECT);
		}
		
		$data = array();
		$desc = $db->describeTable($table);
		
		foreach ($desc as $v=>$k)
			$data[] = array('name'=>$v, 'type'=>$k['DATA_TYPE']);
		
		Response::jsonSuccess($data);
	}
}