<?php
/**
 * @todo Rewrite the code!
 * @author Andrew Zamotaev
 * 
 */
class Backend_Orm_Backup
{
	const ERROR_CANT_WRITE = 1;
	const ERROR_SQL_FAIL = 2;
	const ERROR_CANT_EXTRACT_ZIP = 3;
	const ERROR_EMPTY_BACKUP = 4;
	
	/**
	 * Flag, whether update package contains sql file dump.sql
	 * true to collect sql files to $_sqlPaths and remove them from coping
	 * @var boolean
	 */
	public $sql = false;
	public $execSql = false;
	
	protected $_sqlPaths = array();
	
	protected $_docRoot;
	protected $_mysqlExec;
	protected $_backupDir;
	
	protected $_errorStatus = 0;
	protected $_errors = array();
	
	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;
	
	public function __construct(Zend_Db_Adapter_Abstract $db)
	{
		$config = Registry::get('main' , 'config');
		$this->_db = $db;
		
		$this->_docRoot = $config->get('docroot');
		if(Utils::isWindows())
			$this->_docRoot .= '/';
			
		$this->_mysqlExec = $config->get('mysqlExecPath');
		$this->_backupDir = $config->get('backups');
	}
	
	public function update($from)
	{
		if(substr($from, -4) == '.zip')
			return $this->_updateFromZip($from);
		else
			return $this->_updateFromSystem($from);
	}
	
	protected function _updateFromZip($from)
	{
		$files = File::getZipItemsList($from);
		
		if(empty($files))
		{
			$this->_errorStatus = self::ERROR_EMPTY_BACKUP;
			return false;
		}
		
		$filesWLocalRoot = array();
		foreach ($files as $key => $file)
		{
			$filesWLocalRoot[] = $this->_docRoot . $file;
			
			if($this->sql)
			{
				if (strrchr($file, '.') === '.sql')
				{
					$this->_sqlPaths[] = $file;
					unset($files[$key]);
				}
			}
		}
		
		$permCheck = File::checkWritePermission($filesWLocalRoot);
		
		if($permCheck !== true)
		{
			$this->_errorStatus = self::ERROR_CANT_WRITE;
			$this->_errors = array_unique($permCheck);
			return false;
		}
		
		if($this->execSql && !empty($this->_sqlPaths))
		{
			if(!File::unzipFiles($from, $this->_docRoot, $this->_sqlPaths))
			{
				$this->_errorStatus = self::ERROR_CANT_EXTRACT_ZIP;
				return false;
			}
			
			foreach ($this->_sqlPaths as $path)
			{
				$curPath = $this->_docRoot . $path;
				if(!$this->_restoreSql($curPath))
				{
					$this->_errorStatus = self::ERROR_SQL_FAIL;
					return false;
				}
				if(file_exists($curPath))
					unlink($curPath);
			}
		}
			
		if(!File::unzipFiles($from, $this->_docRoot, $files))
		{
			$this->_errorStatus = self::ERROR_CANT_EXTRACT_ZIP;
			return false;
		}
		
		return true;
	}
	
	protected function _updateFromSystem($from)
	{
		$files = File::scanFiles($from);
		
		if(empty($files))
		{
			$this->_errorStatus = self::ERROR_EMPTY_BACKUP;
			return false;
		}
		
		$pathsToCheck = array();
		
		if($this->sql)
		{
			foreach ($files as $key => $file)
			{
				if (strrchr($file, '.') === '.sql')
				{
					$this->_sqlPaths[] = $file;
					unset($files[$key]);
				}
				
				$pathsToCheck[] = str_replace($from, $this->_docRoot, $file);
			}
		}
		
		$permCheck = File::checkWritePermission($pathsToCheck);
		
		if($permCheck !== true)
		{
			$this->_errorStatus = self::ERROR_CANT_WRITE;
			$this->_errors = $permCheck;
			return false;
		}
		
		if($this->execSql && !empty($this->_sqlPaths))
		{
			foreach ($this->_sqlPaths as $path)
			{
				if(!$this->_restoreSql($path))
				{
					$this->_errorStatus = self::ERROR_SQL_FAIL;
					return false;
				}
				if(file_exists($path))
					unlink($path);
			}
		}
		
		if(!File::copyFiles($this->_docRoot, $files, $from))
		{
			$this->_errorStatus = self::ERROR_CANT_WRITE;
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public function getErrors(){
		return $this->_errors;
	}
	
	public function getErrorStatus(){
		return $this->_errorStatus;
	}
	
	/**
     * restore sql
     * @param string $path
     * @throws Exception
     * @return boolean
     */
    protected function _restoreSql($path)
    {
    	$dbConfig = $this->_db->getConfig();
    	
    	$cmd = $this->_mysqlExec . ' -u' . $dbConfig['username'] . ' -p' . $dbConfig['password'] 
    			. ' -t ' . $dbConfig['dbname'] . ' < ' . $path;
    	
    	if(system($cmd) === false)
    	{
    		$this->_errorStatus = self::ERROR_SQL_FAIL;
    		return false;
    	}
    	return true;
    }
}