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
			
		
		if(!is_dir($dirPath))
			Response::jsonArray(array());
			
		$files = File::scanFiles($path, array('.dat') , false , File::Files_Dirs);
		
		$list = array();
		if(!empty($files))
		{
		    $dirs = array();
		    $pfiles = array();
		    
		    foreach($files as $k=>$fpath)
		    {
		      $text  = basename($fpath);
		      if($text ==='.svn')
		          continue;
		      
		      if(is_dir($fpath))
		        $dirs[] = $fpath;
		      else
		        $pfiles[] = $fpath;	      
		    }
		    
		    if(!empty($dirs))
		    {
		      sort($dirs);
		      foreach ($dirs as $k=>$fpath)
		      {
		        $text  = basename($fpath); 	
		        $obj = new stdClass();
		        $obj->id =str_replace($this->_configMain->get('docroot'), './', $fpath);
		        $obj->text = $text;
		        $obj->expanded = false;
		        $obj->leaf = false;
		        $list[] = $obj;
		      }
		    }
		    
		    if(!empty($pfiles))
		    {
		       sort($pfiles);
		       
		       foreach ($pfiles as $k=>$fpath)
		       {
		         $text  = basename($fpath);
		         $obj = new stdClass();
		         $obj->id =str_replace($this->_configMain->get('docroot'), './', $fpath);
		         $obj->text = $text;
		         $obj->leaf = true;
		         $list[] = $obj;
		       }
		    }		    
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