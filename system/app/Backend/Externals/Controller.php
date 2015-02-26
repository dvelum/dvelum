<?php
class Backend_Externals_Controller extends Backend_Controller
{
	public function indexAction()
	{
		$this->_resource->addInlineJs('
	        var externalsEnabled = '.intval($this->_configMain->get('allow_externals')).';
	    ');
		
		parent::indexAction();
	}
	
	public function listAction()
	{
		$modulesCfg = Config::factory(Config::File_Array, $this->_configMain->get('configs').'externals.php')->__toArray();			
		$vendors = File::scanFiles($this->_configMain->get('external_modules'),false,false,File::Dirs_Only);
		
		if(!$this->_configMain->get('allow_externals') || empty($vendors))
			Response::jsonSuccess(array());

	
		foreach ($vendors as $path)
		{
			$vendorName = basename($path);
			$modules = File::scanFiles($path,false,false,File::Dirs_Only);
			
			if(empty($modules))
			 	continue;
	
			foreach ($modules as $module)
			{					
				$moduleName = basename($module);
				$uid = $vendorName.'/'.$moduleName;

				if(!file_exists($module . '/config.ini'))
					continue;
					
				$cfg = parse_ini_file($module . '/config.ini' , true);
					
				if(!isset($cfg['INFO']))
					continue;

				$info = $cfg['INFO'];
					
				if(!isset($modulesCfg[$uid]))
					$modulesCfg[$uid] = array('active'=>false);		
					
				$modulesCfg[$uid]['title'] = $info['title'];
				$modulesCfg[$uid]['description'] = $info['description'];
				$modulesCfg[$uid]['author'] = $info['author'];
				$modulesCfg[$uid]['version'] = $info['version'];
				$modulesCfg[$uid]['id'] = $uid;				
			}			
		}	
		Response::jsonSuccess(array_values($modulesCfg));
	}
	
	public function updateAction()
	{
		$this->_checkCanEdit();
	
		$data = Request::post('data' , 'raw' , false);
	
		if($data === false)
			Response::jsonError($this->_lang->INVALID_VALUE);
	
		$data = json_decode($data , true);
	
		if(!isset($data[0]))
			$data = array($data);
	
		$modulesCfg = Config::factory(Config::File_Array, $this->_configMain->get('configs').'externals.php');
		$modulesCfg->removeAll();
	
		foreach($data as $v)		
			$modulesCfg->set($v['id'], array('active'=>$v['active']));
		
	
		if($modulesCfg->save())
			Response::jsonSuccess();
		else
			Response::jsonError($this->_lang->CANT_WRITE_FS);
	}
}