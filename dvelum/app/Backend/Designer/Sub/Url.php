<?php
class Backend_Designer_Sub_Url extends Backend_Designer_Sub
{ 
	/**
	 * Files list
	 */
	public function fslistAction()
	{
		$path = Request::post('node', 'string', '');
		$path = str_replace('.','', $path);
			
		$dirPath = $this->_config->get('controllers');
				
		if(!is_dir($dirPath))
			Response::jsonArray(array());
			
		if(!strlen($path)){
			$files = array($dirPath.'Backend',$dirPath.'Frontend');
		}else{	
			$files = File::scanFiles($dirPath . $path, array('.php') , false , File::Files_Dirs);
		}
		if(empty($files))
			Response::jsonArray(array());
			
		sort($files);	
		$list = array();

		foreach($files as $k=>$fpath)
		{
			$text  = basename($fpath);
			if($text ==='.svn')
				continue;
		
			$obj = new stdClass();
			$obj->id = str_replace($dirPath, '', $fpath);
			$obj->text = $text;

			if($obj->text ==='Controller.php')
			{
				$controllerName =  str_replace(array($dirPath , DIRECTORY_SEPARATOR) , array('','_') , substr($fpath,0,-4));
				$obj->url = Backend_Designer_Code::getControllerUrl($controllerName); 
			}else{
				$obj->url = '';
			}
			
			if(is_dir($fpath))
			{
				$obj->expanded = false;
				$obj->leaf = false;
			} 
			else
			{
				if($text!=='Controller.php')
					continue;
					
				$obj->leaf = true;
			}
			$list[] = $obj;	
		}
		Response::jsonArray($list);	
	}
	
	
	public function actionsAction()
	{
		$controller = Request::post('controller', 'string', '');
		if(!strlen($controller))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		if(strpos($controller,'.php' )!==false)
			$controller = Utils::classFromPath($controller);

		$actions = Backend_Designer_Code::getPossibleActions($controller);
		Response::jsonSuccess($actions);
	}
	
	
	public function imgdirlistAction()
	{
		$path = Request::post('node', 'string', '');
		$path = str_replace('.','', $path);
			
		$dirPath = $this->_configMain->get('docroot');
				
		if(!is_dir($dirPath.$path))
			Response::jsonArray(array());
					
		$files = File::scanFiles($dirPath . $path, false, false , File::Dirs_Only);

		if(empty($files))
			Response::jsonArray(array());
			
		sort($files);	
		$list = array();

		foreach($files as $k=>$fpath)
		{
			$text  = basename($fpath);
			if($text ==='.svn')
				continue;
		
			$obj = new stdClass();
			$obj->id = str_replace($dirPath, '', $fpath);
			$obj->text = $text;
			$obj->url = '/' . $obj->id;
			
			if(is_dir($fpath))
			{
				$obj->expanded = false;
				$obj->leaf = false;
			} 
			else
			{
				$obj->leaf = true;
			}
			$list[] = $obj;	
		}	
		Response::jsonArray($list);	
	}
	
	public function imglistAction()
	{
		$templates = $this->_config->get('templates');
		
		$dirPath = $this->_configMain->get('docroot');
		$dir = Request::post('dir', 'string', '');

		if(!is_dir($dirPath . $dir))
			Response::jsonArray(array());
			
		$files = File::scanFiles($dirPath . $dir, array('.jpg','.png','.gif','.jpeg') , false , File::Files_Only);	
		
		if(empty($files))
			Response::jsonArray(array());
			
		sort($files);	
		$list = array();

		foreach($files as $k=>$fpath)
		{
		    // ms fix
		    $fpath = str_replace('\\' , '/', $fpath);
		    
			$text  = basename($fpath);
			if($text ==='.svn')
				continue;
			
			$list[] = array(
				'name'=>$text,
				'url'=>str_replace($dirPath .'/', $this->_configMain->get('wwwroot'), $fpath),
			    'path'=>str_replace($dirPath .'/', $templates['wwwroot'], $fpath),
			);
		}
		Response::jsonSuccess($list);
	}
}