<?php
class Api_Deploy
{
	/**
	 * @var Config_Abstract
	 */
	protected $_appConfig;
	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;
	
	public function __construct($appConfig , $db)
	{
		$this->_db = $db;
		$this->_appConfig = $appConfig;
	}
	
	/**
	 * Compile ignore arguments for console
	 * @param array $ignore
	 * @return string
	 */
	public function ignorePathsStr(array $ignore)
	{
		if(empty($ignore))
			return '';
		
		$str = '';
		foreach ($ignore as $value)
			$str.='! -path \'*'.$value.'*\' ';
			
		return $str;	
	}

	public function syncdbAction(){
		$data = array();
		$data['dbstat'] = Backend_Orm_Controller::getDbStats();
		Response::jsonSuccess($data);		
	}
	
	public function syncfilesAction()
	{
		$data = array();
		if($this->_appConfig['deploy_use_console']){
			$data['files'] = $this->_fsmap();
		}else{
			$data['files'] = $this->_fsmapByPhp();			
		}
		Response::jsonSuccess($data);		
	}

	public function syncFsAction($toFile = false)
	{
		if($toFile!==false){
			if($this->_appConfig['deploy_use_console']){
				return $this->_fsmap($toFile);
			}else{
				return $this->_fsmapByPhp($toFile);
			}
		}else{
			Response::jsonSuccess($this->_fsmap());	
		}
	}
	/**
	 * Create FS map using UNIX shell, faster then php code
	 * @param boolean $toFile - save to file
	 * @return boolean|array
	 */
	protected  function _fsmap($toFile = false)
	{
		ini_set('max_execution_time', 3600);
	
		$deployCfg = Config::factory(Config::File_Array, $this->_appConfig['configs'].'deploy.php');
		
	    $file = $this->_appConfig['tmp'].time();    
	    $path = $this->_appConfig['docroot'];

		$ignoreStr = $this->ignorePathsStr($deployCfg->get('ignore'));		
		
		$cmd = ' cd ' . $path . ' && find ./ -type f ' . $ignoreStr . ' -exec md5sum "{}" \; > ' . $file;	
		
		shell_exec($cmd);
		
		$data = file($file);
		unlink($file);
		$result = array();

		if(!empty($data))
		{
			foreach ($data as $value)
			{
				$value = explode(' ', str_replace(array('  ',"\n"), array(' '), $value));
				$result[md5($value[1])] = array('file'=>$value[1],'md5'=>$value[0]);
			}
		}

		if($toFile!==false)
			return Utils::exportArray($toFile, $result);
		else	
			return $result;
	}
	/**
	 * Create FS map using php functions
	 * @return mixed bulean|array
	 */
	protected function _fsmapByPhp($toFile = false)
	{
		ini_set('max_execution_time', 3600);
		
		$deployCfg = Config::factory(Config::File_Array, $this->_appConfig['configs'].'deploy.php');

		
		$list = File::scanFiles('.' , array(), $recursive = true);
		$ignore = $deployCfg->get('ignore');
		
		$result = array();
		
		foreach ($list as $k=>$lfile)
		{
			if(is_dir($lfile))
				continue;
				
			$good = true;
			$lfile = str_replace('\\', '/', $lfile);
			
			$fName = $lfile;
			if(is_dir($fName))
				$fName.='/';
			
			foreach ($ignore as $string)
			{
				if(strpos($fName , $string)!==false){
					$good = false;
					break;
				}	
			}
			if($good){
				$nameHash = md5($lfile); 
				$fileHash = md5_file($lfile);
				$result[$nameHash] = array('file'=>$lfile,'md5'=>$fileHash);
			}
		}
		
		if($toFile!==false)
			return Utils::exportArray($toFile, $result);
		else
			return $result;
		
	}
}