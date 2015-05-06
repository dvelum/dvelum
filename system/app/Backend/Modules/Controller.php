<?php
class Backend_Modules_Controller extends Backend_Controller{
    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
     public function indexAction()
     {    
         $res = Resource::getInstance();  
         $res->addJs('/js/app/system/FilesystemWindow.js'  , 1);
         //$this->_resource->addJs('/js/lib/ext_ux/CTemplate.js' ,1);
         //$this->_resource->addJs('/js/lib/ext_ux/ComponentColumn.js' ,2);
	     $res->addJs('/js/app/system/crud/modules.js'  , 2);
	       $this->_resource->addInlineJs('
        	var canEdit = '.((integer)$this->_user->canEdit($this->_module)).';
        	var canDelete = '.((integer)$this->_user->canDelete($this->_module)).';
        ');
    }
    
    /**
     * Get moduiles list
     */
    public function listAction()
    {
        $manager = new Backend_Modules_Manager();
		$data = $manager->getList();
		
		foreach ($data as $k=>&$item)
		{
		  $item['related_files']= '';
		  $classFile = './system/app/'.str_replace('_', '/', $item['class']).'.php';
		  
		  if(file_exists($classFile))
		    $item['related_files'].= $classFile.'</br>';	
		  
		  if(!empty($item['designer']))
		  {
		    $item['related_files'].=$item['designer'].'</br>';
		    $crudJs = './js/app/system/crud/' . strtolower($manager->getModuleName($item['class'])) . '.js';
		    if(file_exists($crudJs)){
		      $item['related_files'].=$crudJs.'</br>';
		    }
		    
		    $actionJs = './js/app/actions/' . strtolower($manager->getModuleName($item['class'])) . '.js';
		    if(file_exists($actionJs)){
		      $item['related_files'].=$actionJs.'</br>';
		    }
		  }
		}
		
		Response::jsonSuccess(array_values($data));  
    }
    
    /**
     * Update modules list
     */
    public function updateAction()
    {
      $this->_checkCanEdit();
		
      $data = Request::post('data' , 'raw' , false);
      
      if($data === false)
      	Response::jsonError($this->_lang->INVALID_VALUE);
      
      $data = json_decode($data , true);
      
      if(!isset($data[0]))
      	$data = array($data);
      
      $manager = new Backend_Modules_Manager();
      $manager->removeAll();
      
      foreach($data as $v)
      {
        if(isset($v['related_files']))
          unset($v['related_files']);
        
      	$name = $manager->getModuleName($v['class']);
      	$manager->addModule($name , $v);
      }
      
      if($manager->save())
      	Response::jsonSuccess();
      else
      	Response::jsonError($this->_lang->CANT_WRITE_FS);
    }
    
    /**
     * Get list of available controllers
     */
    public function controllersAction()
    {
        $appPath = $this->_configMain['application_path'];
        $folders = File::scanFiles($this->_configMain['backend_controllers'],false,true,File::Dirs_Only);
        $data = array();
        
        $systemControllers = $this->_configBackend->get('system_controllers');
        
        if(!empty($folders))
        {
        	foreach ($folders as $item)
        	{
        		$name = basename($item);
        		/*
        		 * Skip system controller
        		 */
        		if(in_array($name, $systemControllers , true))
        			continue;
        		
        		if(file_exists($item.'/Controller.php'))
        		{
        			$name = str_replace($appPath, '', $item.'/Controller.php');
        			$name = Utils::classFromPath($name);
        			$data[] = array('id'=>$name,'title'=>$name);
        		}
        	}
        }
        Response::jsonSuccess($data);  			
    }
    
	/**
	 * Inerface projects list
	 */
	public function fslistAction()
	{
		$path = Request::post('node', 'string', '');
		
		$config = Config::factory(Config::File_Array, $this->_configMain['configs'] . 'designer.php');

		$dirPath = $config->get('configs');
		$filesPath  = substr($dirPath,0,-1).$path;

		$list = array();
		
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
			} 
			else
			{
				$obj->leaf = true;
				$obj->id = str_replace($dirPath, './', $fpath);
			}
			$list[] = $obj;	
		}	
		
		Response::jsonArray($list);	
	}
	
	/**
	 * Get list of registered Db_Object's
	 */
	public function objectsAction()
	{
		$manager = new Db_Object_Manager();
		$list = $manager->getRegisteredObjects();
		$data = array();
		
		$systemObjects = $this->_configBackend['system_objects'];
		
		foreach ($list as $key)
			if(!in_array(ucfirst($key), $systemObjects) && !class_exists('Backend_'.Utils_String::formatClassName($key).'_Controller'))
				$data[]= array('id'=>$key , 'title'=>Db_Object_Config::getInstance($key)->getTitle());
		
		Response::jsonSuccess($data);
	}
	
	/**
	 * Create new module
	 */
	public function createAction()
	{				
		$this->_checkCanEdit();
		
		$object = Request::post('object', 'string', false);
		
		if(!$object)
			Response::jsonError($this->_lang->WRONG_REQUEST);
		
		$object = Utils_String::formatClassName($object);
		
		$class = 'Backend_' . $object . '_Controller';
				
		if(class_exists($class))
			Response::jsonError($this->_lang->FILL_FORM , array('id'=>'name','msg'=>$this->_lang->SB_UNIQUE));
		
		$designerConfig = Config::factory(Config::File_Array, $this->_configMain['configs'] . 'designer.php');
				
		$projectFile = $designerConfig->get('configs') . strtolower($object) . '.designer.dat';
		
		if(file_exists($projectFile))
			Response::jsonError($this->_lang->FILE_EXISTS . '(' . $projectFile . ')');
		
		$actionFile = $designerConfig->get('actionjs_path') . strtolower($object) . '.js';
		
		if(file_exists($actionFile))
			Response::jsonError($this->_lang->FILE_EXISTS . '(' . $actionFile . ')');

		$objectConfig = Db_Object_Config::getInstance($object);

		// Check ACL permissions
		$acl = $objectConfig->getAcl();
		if($acl){
			if(!$acl->can(Db_Object_Acl::ACCESS_CREATE , $object)  || 	!$acl->can(Db_Object_Acl::ACCESS_VIEW , $object)){
				Response::jsonError($this->_lang->get('ACL_ACCESS_DENIED'));
			}
		}

		$manager = new Db_Object_Manager();
		
		if(!$manager->objectExists($object))
			Response::jsonError($this->_lang->FILL_FORM , array('id'=>'object','msg'=>$this->_lang->INVALID_VALUE));
			
	    $codeGenadApter = $this->_configBackend->get('modules_codegen');
	    $codeGen = new $codeGenadApter();
		try{
			if($objectConfig->isRevControl())
				$codeGen->createVcModule($object,  $projectFile , $actionFile);
			else
				$codeGen->createModule($object,  $projectFile , $actionFile);
			
		}catch (Exception $e){
			Response::jsonError($e->getMessage());
		}

		
		$userInfo = User::getInstance()->getInfo();	
		$per = Model::factory('Permissions');
		
		if(!$per->setGroupPermissions($userInfo['group_id'], $object , 1 , 1 , 1 , 1))
			Response::jsonError($this->_lang->CANT_EXEC);
		
		Response::jsonSuccess(
				array(
						'class'=>$class,
						'name'=>$object , 
						'active'=>true,
						'dev'=>false,
						'title'=>$objectConfig->getTitle(),
						'designer'=> $projectFile
				)
		);
	}
	
	/**
	 * Delete module
	 */
	public function deletemoduleAction()
	{
	  $this->_checkCanEdit();
	  $module = Request::post('id', 'string', false);
	  $removeRelated = Request::post('delete_related', 'boolean', false);
	  
	  $manager = new Backend_Modules_Manager();
	  $moduleName = $manager->getModuleName($module);
	  
	  if(!$module || !strlen($module) || !$manager->isValidModule($moduleName))
	      Response::jsonError($this->_lang->WRONG_REQUEST);
	  
      $filesToDelete = array();
      
	  if($removeRelated)
	  {    	  
      	  $item = $manager->getModuleConfig($moduleName);
      	  
          $classFile = './system/app/'.str_replace('_', '/', $item['class']).'.php';
      	  if(file_exists($classFile))
      	    $filesToDelete[] = $classFile;
      		   	
      	  if(!empty($item['designer']))
      	  {
      	    if(file_exists($item['designer']))
      	      $filesToDelete[] = $item['designer'];
   	    
      	    $crudJs = './js/app/system/crud/' . strtolower($manager->getModuleName($item['class'])) . '.js';
      	    if(file_exists($crudJs)){
      	       $filesToDelete[]=$crudJs;
      	    }
      	    
      	    $actionJs = './js/app/actions/' . strtolower($manager->getModuleName($item['class'])) . '.js';
      	    if(file_exists($actionJs)){
      	       $filesToDelete[]=$actionJs;
      	    }       		  		    
      	 }
	  }
	  
	  // check before deleting
	  if(!empty($filesToDelete))
	  {
	    $err = array();
	    foreach ($filesToDelete as $file){
	      if(!is_writable($file))
	        $err[] = $file;
	    }
	    
	    if(!empty($err))
	      Response::jsonError($this->_lang->CANT_WRITE_FS . "\n<br>".implode(",\n<br>", $err));
	  }
	  
	  $manager->removeModule($moduleName);
	  
	  if(!$manager->save())
	    Response::jsonError($this->_lang->CANT_WRITE_FS.' '.$manager->getConfig()->getName());
	  
	  // try to delete
	  if(!empty($filesToDelete))
	  {
	    $err = array();
	    foreach ($filesToDelete as $file){
	      if(!unlink($file))
	        $err[] = $file;
	    }
	    
	    if(!empty($err))
	      Response::jsonError($this->_lang->CANT_WRITE_FS . "\n<br>".implode(",\n<br>", $err));
	  }
	  Response::jsonSuccess();
	}
}