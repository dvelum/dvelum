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
		$node = Request::post('node', 'string', '');
		$paths = Config::storage()->getPaths();
		$cfgPath = $this->_config->get('configs');

		$list = array();
		$ret = array();

		// In accordance with configs merge priority
		rsort($paths);

		foreach($paths as $path) {
			$nodePath = str_replace('//', '/', $path.$cfgPath.$node);

			if(!file_exists($nodePath))
				continue;

			$items = File::scanFiles($nodePath , array('.dat'), false, File::Files_Dirs);

			if(!empty($items))
			{
				foreach ($items as $p){
					if(DIRECTORY_SEPARATOR == '\\') {
						$p = str_replace('\\', '/', $p);
						$p = str_replace('//', '/', $p);
					}

					$baseName = basename($p);

					if(!isset($list[$baseName])){
						$obj = new stdClass();
						$obj->id = str_replace($path.$cfgPath, '/', $p);
						$obj->text = $baseName;

						if(is_dir($p))
						{
							$obj->expanded = false;
							$obj->leaf = false;
						}
						else
						{
							$obj->leaf = true;
						}
						$list[$baseName] = $obj;
					}
				}
			}
		}

		ksort($list);
		foreach($list as $p)
			$ret[] = $p;

		Response::jsonArray($ret);
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

		$newPath = Config::storage()->getWrite() . $this->_config->get('configs');
		
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

		$writePath = Config::storage()->getWrite();
		$configsPath = $this->_config->get('configs');
		$actionsPath = $this->_config->get('actionjs_path');
		
		if(strlen($path)){
			$savePath =  $writePath . $configsPath . $path . DIRECTORY_SEPARATOR . $name.'.designer.dat';
			$actionFilePath = $actionsPath . str_replace($configsPath, '', $path) . DIRECTORY_SEPARATOR . $name.'.js';		
		}else {
			$savePath = $writePath . $configsPath . $name . '.designer.dat';
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