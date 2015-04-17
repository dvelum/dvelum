<?php
class Backend_Designer_Sub_Fs extends Backend_Designer_Sub
{
	/**
	 * Designer config
	 * @var Config_File_Array
	 */
	protected $_config;
	
	protected $_module = 'Designer';
	 
	/**
	 * Files list
	 */
	public function fslistAction()
	{
		$path = Request::post('node', 'string', '');
		//$path = str_replace('.','', $path);
			
		$dirPath = $this->_config->get('configs');
						
		if($path === '')
			$path = $dirPath . $path;

		$filesPath  = substr($dirPath,0,-1).$path;
			
		
		if(!is_dir($filesPath))
			Response::jsonArray(array());

		$files = File::scanFiles($filesPath, array('.dat') , false , File::Files_Dirs);

		/**
		 * This is inline fix for windows
		 */
		if(DIRECTORY_SEPARATOR == '\\')
		{
			foreach ($files as &$v)
			{
				$v = str_replace('\\', '/', $v);
				$v = str_replace('//', '/', $v);
			}
			unset($v);
		}

		if(empty($files))
			Response::jsonArray(array());

		$list = array();

		foreach($files as $k=>$fpath)
		{
			$text  = basename($fpath);

			$obj = new stdClass();
			$obj->id = str_replace($dirPath, '/', $fpath);
			$obj->text = $text;

			if(is_dir($fpath))
			{
				$obj->expanded = false;
				$obj->leaf = false;
				$list[] = $obj;
			}
			else
			{
				$obj->leaf = true;
				$obj->id = $fpath;//str_replace($dirPath, './', $fpath);
			}
			$list[] = $obj;
		}
		Response::jsonArray($list);	
	}
	/**
	 * Create config subfolder
	 */
	public function fsmakedirAction()
	{
		
		$name = Request::post('name', 'string', '');
		$path = Request::post('path', 'string', '');
		
		$name = str_replace(array(DIRECTORY_SEPARATOR), '' , $name);
		
		if(!strlen($name))
			Response::jsonError($this->_lang->WRONG_REQUEST . ' [code 1]');
		
		$newPath = $this->_config->get('configs');
		
		if(strlen($path))
		{
			if(!is_dir($newPath. $path))
				Response::jsonError($this->_lang->WRONG_REQUEST  . ' [code 2]');
				
			$newPath.= $path . DIRECTORY_SEPARATOR;
		}
		 
		$newPath.= DIRECTORY_SEPARATOR . $name;			
			
		if(@mkdir($newPath, 0775))
			Response::jsonSuccess();
		else
			Response::jsonError($this->_lang->CANT_WRITE_FS . ' ' .$newPath);	
	}
	/**
	 * Create new report
	 */
	public function fsmakefileAction()
	{
		
		$name = Request::post('name', 'string', '');
		$path = Request::post('path', 'string', '');

		if(!strlen($name))
			Response::jsonError($this->_lang->WRONG_REQUEST . ' [code 1]');
		
		$configsPath = $this->_config->get('configs');
		$actionsPath = $this->_config->get('actionjs_path');
		
		if(strlen($path)){
			$savePath =  $path . DIRECTORY_SEPARATOR . $name.'.designer.dat';	
			$actionFilePath = $actionsPath . str_replace($configsPath, '', $path) . DIRECTORY_SEPARATOR . $name.'.js';		
		}else {
			$savePath = $configsPath . $name . '.designer.dat';
			$actionFilePath = $actionsPath . $name . '.js';
		}
		
		if(file_exists($savePath))
			Response::jsonError($this->_lang->FILE_EXISTS);

		$obj = new Designer_Project();	
		$obj->actionjs = $actionFilePath;

		if($this->_storage->save($savePath, $obj))
			Response::jsonSuccess(array('file'=>$savePath));
		else
			Response::jsonError($this->_lang->CANT_WRITE_FS.' '.$savePath);	
	}
	
}