<?php
class Backend_Compiler_Controller extends Backend_Controller
{
	/**
	 * @var Config_File_Array
	 */
    protected $_packagesConfig;
    
    public function __construct(){
        parent::__construct();
        $this->_autoloaderConfig = $this->_configMain->get('autoloader');
        $this->_packagesConfig = Config::factory(Config::File_Array, $this->_autoloaderConfig['packagesConfig']);
    }
    
    public function indexAction(){
    	 $res = Resource::getInstance();   
    	 $res->addJs('/js/lib/extjs4/ux/RowExpander.js'  , 1); 
	     $res->addJs('/js/app/system/crud/compiler.js'  , 2);
	     $res->addJs('/js/app/system/SearchPanel.js'     , 3);
	     $this->_resource->addInlineJs('
        	var canEdit = '.((integer)$this->_user->canEdit($this->_module)).';
        	var canDelete = '.((integer)$this->_user->canDelete($this->_module)).';
        ');
    }

	/**
     * Helps to get a data list of packages
     * @return void
     */
	public function listpackagesAction()
	{
		$result = array();
		$list = $this->_packagesConfig->get('packages');
		
		$packagesPath = $this->_packagesConfig->get('path');
		
		foreach($list as $name => $data)
		{
			$valid = false;
			
			$filename = $packagesPath . $name . '.php';
			
			if(isset($data['checksum']))
				$valid = $this->_isValidPackage($data);

			if($valid && !isset($data['fchecksum']) || !file_exists($filename) || md5_file($filename) !== $data['fchecksum'])
				$valid = false;
					
			$result[] = array(
					'id' => $name , 
					'name' => $name , 
					'active' => $data['active'] , 
					'size' => file_exists($filename) ? Utils::formatFileSize(filesize($filename)) : '0 Kb' , 
					'files_count' => sizeof($data['paths']) , 
					'valid' => $valid
			);
		}		
		Response::jsonSuccess($result);
	}
    
    /**
     * Chackes whether a package is valid or not
     * @param array $item
     * @return boolean
     */
    protected function _isValidPackage($item)
    {
    	$s = $this->_compilePackage($item);
    	
    	if ($item['checksum'] === md5($s))
    		return true;
    	else
    		return false;
    }
    
    /**
     * Add a new package
     * @return void 
     */
    public function addpackageAction()
    {
    	$this->_checkCanEdit();
    	
    	$name = Request::post('name', 'string', '');
    	if(!strlen($name))
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	
    	/*
    	 * Returning a reference from a function
    	 */
   		$data = & $this->_packagesConfig->dataLink();
    	
    	if(isset($data['packages'][$name]))
    		Response::jsonError($this->_lang->KEY_DUPLICATE);
    	
    	$data['packages'][$name] = array('paths'=>array(),'active'=>false);
    	
    	if($this->_packagesConfig->save())
    		Response::jsonSuccess();
    	else
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    }
    
    /**
     * Updates an existing packages
     * @return void
     */
    public function updatepackagesAction()
    {
		$this->_checkCanEdit();
				
		$updateData = Request::post('data' , 'raw' , '');
		$updateData = json_decode($updateData , true);
		
		/*
    	 * Returning a reference from a function
    	 */
		$data = & $this->_packagesConfig->dataLink();
		
		foreach($updateData as $v)
		{
			if(!isset($data['packages'][$v['id']]))
				Response::jsonError($this->_lang->WRONG_REQUEST);
			
			if($v['id'] != $v['name'])
			{
				if(isset($data['packages'][$v['name']]))
					Response::jsonError($this->_lang->KEY_DUPLICATE);
				
				$data['packages'][$v['name']] = $data['packages'][$v['id']];
				unset($data['packages'][$v['id']]);
			}
			$data['packages'][$v['name']]['active'] = $v['active'];
		}
		
		if(!$this->_packagesConfig->save())
			Response::jsonError($this->_lang->CANT_WRITE_FS);
			
		if($this->buildmapAction()===false)
			Response::jsonError($this->_lang->CANT_WRITE_FS);
			
		Response::jsonSuccess();
    }
    
    /**
     * Helps to get a data list of package's records
     * @return void
     */
    public function listrecordsAction()
    {
		$packageName = Request::post('package' , 'str' , false);
		$packages = $this->_packagesConfig->get('packages');
		
		if(!$packageName || !isset($packages[$packageName]))
			Response::jsonError($this->_lang->WRONG_REQUEST);
		
		$paths = $this->_configMain['autoloader']['paths'];
		foreach ($paths as &$item)
		    $item.='/'; 
		
		$data = array();
		foreach($packages[$packageName]['paths'] as $k => $v) 
		{	
			$data[] = array(
					'id' => $k , 
					'title' => $v , 
					'class_name' => str_replace('/' , '_' , substr(str_replace($paths , '' , $v) , 0 , -4))
			);
		}
		Response::jsonSuccess($data);
    }

	/**
     * Adds records to an existing package
     * @return void
     */
	public function addrecordsAction()
	{
		$this->_checkCanEdit();
		
		$packageName = Request::post('package' , 'str' , false);
		$pathsToAdd = Request::post('paths' , 'array' , false);
		
		/*
    	 * Returning a reference from a function
    	 */
		$packagesConfigData = & $this->_packagesConfig->dataLink();
		
		if(!$packageName || empty($pathsToAdd) || !isset($packagesConfigData['packages'][$packageName]))
			Response::jsonError($this->_lang->WRONG_REQUEST);
		
		$docRoot = $this->_configMain['docroot'];
		
		sort($pathsToAdd);
		
		foreach($pathsToAdd as $v)
		{
			if(!is_file($docRoot . '/' . $v))
				Response::jsonError($this->_lang->WRONG_REQUEST);
			$packagesConfigData['packages'][$packageName]['paths'][] = './' . $v;
		}

		if(!$this->_packagesConfig->save())
			Response::jsonError($this->_lang->CANT_WRITE_FS);
			
			
		if($this->buildmapAction()===false)
			Response::jsonError($this->_lang->CANT_WRITE_FS);
				
		Response::jsonSuccess();
	}
	
    /**
     * Save package items sort order
     * @return void
     */
    public function saveorderAction()
    {
    	$this->_checkCanEdit();
    	
    	$packageName = Request::post('name','str',false);
    	$order = Request::post('order','array',false);
    	   	
    	/*
    	 * Returning a reference from a function
    	 */
   		$packagesConfigData = & $this->_packagesConfig->dataLink();
    	
    	if(!$packageName || empty($order) || !isset($packagesConfigData['packages'][$packageName]))
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    		
    	$packagesConfigData['packages'][$packageName]['paths']= $order;
    	$packagesConfigData['packages'][$packageName]['checksum'] = '';
    		  		
    	if($this->_packagesConfig->save())
    		Response::jsonSuccess();
    	else
    		Response::jsonError($this->_lang->CANT_WRITE_FS);	
    }
    
    /**
     * Removes a rocord from a package
     * @return void
     */
    public function removerecordAction()
    {
    	$this->_checkCanDelete();
    	
    	$value = Request::post('value','str',false);
    	$packageName = Request::post('package','str',false);
    	
    	/*
    	 * Returning a reference from a function
    	 */
   		$packagesConfigData = & $this->_packagesConfig->dataLink();
    	
    	if(!$value || !$packageName || !isset($packagesConfigData['packages'][$packageName]))
    		Response::jsonError($this->_lang->WRONG_REQUEST);

    	foreach ($packagesConfigData['packages'][$packageName]['paths'] as $k=>$v)
    	{
    		if($v === $value)
    		{
    			unset($packagesConfigData['packages'][$packageName]['paths'][$k]);
    			break;
    		}
    	}	
    	
    	if(!$this->_packagesConfig->save())
    		Response::jsonError($this->_lang->CANT_WRITE_FS);

    	
    	if($this->buildmapAction()===false)
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    	
    	Response::jsonSuccess();
    }
    
    /**
     * Removes a package
     * @return void
     */
    public function removepackageAction()
    {
    	$this->_checkCanDelete();

    	$name = Request::post('name', 'string', '');
    	/*
    	 * Returning a reference from a function
    	 */
   		$data = & $this->_packagesConfig->dataLink();	
    	
    	if(!strlen($name) || !isset($data['packages'][$name]))
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	
    	unset($data['packages'][$name]);
    	
    	if(is_file($data['path'].$name.'.php'))
    		if(!unlink($data['path'].$name.'.php'))
	    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    	
    	if(!$this->_packagesConfig->save())
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    		 		
    	if($this->buildmapAction()===false)
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    		
    	Response::jsonSuccess();
    }
    
	/**
	 * Files list
	 * @return void
	 */
	public function fslistAction()
	{
		$srcDirs = $this->_configMain['autoloader']['paths'];
		$list = array();
		
		foreach ($srcDirs as $dir)
			$list = array_merge($list , File::scanFiles($dir , array('.php') , true));
		
		foreach($list as $k => &$fpath)
		{
			if(strpos($fpath , '/.svn')){
				unset($list[$k]);
				continue;
			}
		}
		unset($fpath);
		
		$existingList = array();
		
		foreach($this->_packagesConfig->get('packages') as $v)
			$existingList = array_merge($existingList , $v['paths']);
		
		$diff = array_diff($list , $existingList);		
		
		$tree = Utils::fileListToTree($diff);	
		$data = $this->_fillChilds($tree);
		
		Response::jsonArray($data);
	}
    
    /**
     * Compile JS Lang file (used current lang)
     * @return void
     */
	public function langAction()
    {
    	$langPath = $this->_configMain->get('lang_path');
    	$jsPath = $this->_configMain->get('js_lang_path');
    	
        $lManager = new Backend_Localization_Manager($this->_configMain);
        $langs = $lManager->getLangs(false);
 
    	foreach ($langs as $lang)
    	{   	
    	    $langFile =  $langPath .  $lang .'.php';
    		$name = $lang;   		
    		$dictionary = Config::factory(Config::File_Array, $langFile, false);
    		Lang::addDictionary($name, $dictionary);
    		
    		$filePath = $jsPath . $lang .'.js';	
    		   		
    		$dir = dirname($lang);
    		if(!empty($dir) && $dir!=='.' && !is_dir($jsPath.'/'.$dir))
    		{
    		    mkdir($jsPath.'/'.$dir , 0755 , true);
    		}

    		if(strpos($name , '/')===false){
    		  $varName = 'appLang';
    		}else{
    		  $varName = basename($name).'Lang';
    		}
    		
    		if(!@file_put_contents($filePath, 'var '.$varName.' = '.Lang::lang($name)->getJsObject().';'))
    			Response::jsonError($this->_lang->CANT_WRITE_FS . ' '.$filePath);
    		
    	}
    	Response::jsonSuccess();
    }
    /**
     * Rebuild package
     */
    public function rebuildpackageAction()
    {
    	$this->_checkCanEdit();
    	
    	$name = Request::post('name','string', false);
    	if($name===false)
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    		   		
    	$dest = $this->_packagesConfig->get('path');   		
   		/*
    	 * Returning a reference from a function
    	 */
   		$data = & $this->_packagesConfig->dataLink();		
   		
   		if(!isset($data['packages'][$name]))
   			Response::jsonError($this->_lang->WRONG_REQUEST);

   		$s = $this->_compilePackage($data['packages'][$name]);	
   		$data['packages'][$name]['checksum'] = md5($s);
   				
    	if(Utils::exportCode($dest . $name .'.php', $s) === false)
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    	
    	$data['packages'][$name]['fchecksum'] = md5_file($dest . $name .'.php');
    	
    	if(!$this->_packagesConfig->save())
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    	    	
    	if($this->buildmapAction()===false)
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    	
    	Response::jsonSuccess();
    }
    
    public function buildmapAction()
    {      
        $paths = $this->_configMain['autoloader']['paths'];
		foreach ($paths as &$item)
		    $item.='/'; 
		
    	return Utils::createClassMap($paths ,  $this->_autoloaderConfig['map'] , $this->_autoloaderConfig['mapPackaged'] , $this->_packagesConfig);
    }

    public function rebuildmapAction()
    {
        if($this->buildmapAction() === false)
            Response::jsonError($this->_lang->CANT_WRITE_FS);

        Response::jsonSuccess();
    }
    
    /**
     * Rebuild all packages
     */
    public function rebuildallAction()
    {
    	$this->_checkCanEdit();
    	
    	$dest = $this->_packagesConfig->get('path');
   		
    	/*
    	 * Returning a reference from a function
    	 */
   		$data = & $this->_packagesConfig->dataLink();
    	
    	if($this->_packagesConfig->get('all_in_one'))
    	{
    		$s='';
    		foreach ($data['packages'] as $item)
    		{
    			if(!$item['active'])
    				continue;
    			$s.= $this->_compilePackage($item);
    		} 
    		Utils::exportCode($dest . $this->_packagesConfig->get('main_package').'.php', $s); 		
    	}
    	else
    	{
    		foreach ($data['packages'] as $name => $item)
    		{
    			$s = $this->_compilePackage($item);
    			$data['packages'][$name]['checksum'] = md5($s);
    
    			if(Utils::exportCode($dest . $name .'.php', $s) === false)
    				Response::jsonError($this->_lang->CANT_WRITE_FS);
    			
    			$data['packages'][$name]['fchecksum'] = md5_file($dest . $name .'.php');   			 
    		}
    	}
    	
    	if($this->buildmapAction()===false)
    		Response::jsonError($this->_lang->CANT_WRITE_FS);

    	if(!$this->_packagesConfig->save())
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    	else
    		Response::jsonSuccess();
    }
    
    protected function _compilePackage($item)
    {
    	$s ='';
    	
    	foreach ($item['paths'] as $path)
    		if(is_file($path)) 		
    			$s.=str_replace(array('<?php' , '?>'), ' ',php_strip_whitespace($path));
    		
    	return $s;	
    }
    
	/**
     * Fill childs data array for tree panel
     * @param Tree $tree
     * @param mixed $root
     * @return array
     */
    protected function _fillChilds(Tree $tree , $root = 0 )
    {
		$expanded = array(
				'system' , 
				'system/app' , 
				'system/library'
		);
		
		$result = array();
		$childs = $tree->getChilds($root);
		
		if(empty($childs))
			return array();
		
		foreach($childs as $k => $v)
		{
			$obj = new stdClass();
			$obj->id = $v['id'];
			$obj->text = $v['data'];
			$obj->expanded = false;
			
			$cld = array();
			
			if(strpos($obj->text , '.php'))
			{
				$obj->leaf = true;
			}
			else
			{
				if($tree->hasChilds($v['id']))
				{
					$cld = $this->_fillChilds($tree , $v['id']);
					if(empty($cld))
						continue;
				}
				else
				{
					continue;
				}
				
				$obj->leaf = false;
				$obj->expanded = in_array($v['id'] , $expanded , true);
			}
			
			$obj->checked = false;
			$obj->allowDrag = false;
			
			$obj->children = $cld;
			$result[] = $obj;
		}
		return $result;     
    }
}