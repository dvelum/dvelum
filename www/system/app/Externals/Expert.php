<?php
class Externals_Expert
{
	protected $_appConfig;
	protected $_externalsConfig;
	protected $_externalsPath;
	protected $_configs = false;
	protected $_cache = false;
	protected $_cachedData = false;
	protected $_key = '';
	
	static protected $_defaultCache = false;

	public function __construct(Config_Abstract $appConfig , Config_Abstract $externalsConfig)
	{
		$this->_appConfig = $appConfig;
		$this->_externalsConfig = $externalsConfig;	
		$this->_externalsPath = $this->_appConfig->get('external_modules');
			
		if(static::$_defaultCache)
			$this->_cache = static::$_defaultCache;
			
		$this->_readConfigs();	
	}
	
	/**
	 * Set default caching adapter
	 * @param Cache_Abstract $cache
	 */
	static public function setDefaultCache(Cache_Abstract $cache)
	{
		static::$_defaultCache = $cache;
	}
	
	/**
	 * Set caching adapter
	 * @param Cache_Abstract $cache
	 */
	public function setCache(Cache_Abstract $cache)
	{
		$this->_cache = $cache;
	}
	
	protected function _readConfigs()
	{
		// classes objects translations langs
		$data = $this->_externalsConfig->__toArray();		
		$classes = array();
		
		if($this->_cache)
		{
			$this->_key = md5('externals_'.serialize($data));
			$cachedData = $this->_cache->load($this->_key);
			if($cachedData)
			{
				$this->_configs = $cachedData['config'];
				$this->_cachedData = $cachedData['data'];
				return;
			}
		}
		
		foreach ($data as $path=>$config)
		{
			if(!$config['active'])
				continue;
			
			$config = parse_ini_file($this->_externalsPath . $path . '/config.ini' , true);
			$this->_configs[$path] = parse_ini_file($this->_externalsPath . $path . '/config.ini' , true);	
		}
	}
	
	protected function _saveCache()
	{
		if(!$this->_cache)
			return;
		
			$this->_cache->save(
				array(
					'config'=>$this->_configs,
					'data'=>$this->_cachedData
			) , $this->_key);
		
	}

	/**
	 * Check for externals
	 */
	public function hasExternals()
	{
		return count($this->_externalsConfig);
	}
	
	/**
	 * Check if external object exists
	 * @param string $name
	 * @return boolean
	 */
	public function hasObject($name)
	{
		$objects = $this->getObjects();
		return isset($objects[$name]);
	}
	
	/**
	 * Get Object config path
	 * @param string $name
	 * @return Ambigous <string>|boolean
	 */
	public function getObjectPath($name)
	{
		$objects = $this->getObjects();
		if(isset($objects[$name]))
			return $objects[$name];
		else
			return false;
	}
	
	/**
	 * Get external object translation paths
	 * @param string $lang
	 * @return array();
	 */
	public function getTranslations($lang)
	{		
		if(isset($this->_cachedData['translations'][$lang]))
			return $this->_cachedData['translations'][$lang];
		
		$translations = array();

		foreach ($this->_configs as $path=>$config)
			if(isset($config['TRANSLATIONS'][$lang]))
				$translations[] = $this->_externalsPath . $path . '/' . $config['TRANSLATIONS'][$lang];
						
		$this->_cachedData['translations'][$lang] = $translations;
		$this->_saveCache();		
				
		return $translations;
	}
	
	/**
	 * Get external dictionaries
	 * @return array
	 */
	public function getDictionaries()
	{	
		return $this->_getExternalList('dictionaries');	
	}
	
	/**
	 * Get external language files
	 * @return array
	 */
	public function getLangs($language)
	{
		if(isset($this->_cachedData['langs'][$language]))
		return $this->_cachedData['langs'][$language];
		
		$langs = array();
			
		foreach ($this->_configs as $path=>$config)
			if(isset($config['LANGS'][$language]))
				foreach ($config['LANGS'][$language] as $name=>$srcPath)
					$langs[$name] = $this->_externalsPath . $path . '/' . $srcPath;
					
		$this->_cachedData['langs'][$language] = $langs;
		$this->_saveCache();				
					
		return $langs;
	}
	
	/**
	 * Get external templates
	 * @return array
	 */
	public function getTemplates()
	{
		return $this->_getExternalList('templates');
	}
	
	/**
	 * Get external themes list
	 * @return array
	 */
	public function getThemes()
	{
		return $this->_getExternalList('themes');
	}
	
	/**
	 * Get external objects
	 * @return array
	 */
	public function getObjects()
	{
		return $this->_getExternalList('objects');	
	}
	
	/**
	 * Get external classes
	 * @return array
	 */
	public function getClasses()
	{
		return $this->_getExternalList('classes');	
	}
	
	protected function _getExternalList($name)
	{
		$lName = strtolower($name);	
		if(isset($this->_cachedData[$lName]))
			return $this->_cachedData[$lName];
			
		$uName = strtoupper($name);		
		$data = array();
			
		foreach ($this->_configs as $path=>$config)
			if(isset($config[$uName]))
				foreach ($config[$uName] as $name=>$srcPath)
					$data[$name] = $this->_externalsPath . $path . '/' . $srcPath;
		
		$this->_cachedData[$lName] = $data;
		$this->_saveCache();
			
		return $data;
	}
	
	/**
	 * Get external backend controllers
	 * @return array
	 */
	public function getBackendControllers()
	{
		$classes = $this->getClasses();
		$data = array();
		
		foreach ($classes as $name=>$path)
		{
			$nameParts = explode('_',$name);
			if($nameParts[0]==='Backend' && $nameParts[(count($nameParts)-1)] === 'Controller')
			{				
			    array_shift($nameParts);
			    array_pop($nameParts);
			    $vName = implode('_',$nameParts);
			
				$data[$name] = array(
					'id'=>$name,
					'title'=>$vName
				);
			}
		}
		return $data;
	}
	
	/**
	 * Get external frontend controllers
	 * @return array
	 */
	public function getFrontendControllers()
	{
		$classes = $this->getClasses();
		$data = array();
		
		foreach ($classes as $name=>$path)
		{
			$nameParts = explode('_',$name);
			if($nameParts[0]==='Frontend' && $nameParts[(count($nameParts)-1)] === 'Controller')
			{	
				 array_shift($nameParts);
			     array_pop($nameParts);
			     $vNname = implode('_', $nameParts);
			     $data[$name] = array(
					'id'=>$name,
					'title'=>$vNname
				);
			}	
		}		if($this->_cache)
			$this->_key = md5('externals_'.serialize($data));
		return $data;
	}
	
	/**
	 * Get controllers list for tree 
	 * @return array
	 */
	public function getControllersTree()
	{
		$data = array();
			
		foreach ($this->_configs as $path=>$config)
		{
			if(isset($config['CLASSES']))
			{
				foreach ($config['CLASSES'] as $name=>$srcPath)
				{
					if(substr($name,-10)=='Controller'){
						if(strpos($name, 'Frontend') === 0)
							$data[$config['INFO']['namespace']][$config['INFO']['name']]['Frontend'][] = $name;
						elseif(strpos($name, 'Backend') === 0)
							$data[$config['INFO']['namespace']][$config['INFO']['name']]['Backend'][] = $name;
					}
				}
			}
		}

		$objRoot ='';
		
		if(!empty($data))
		{
			foreach($data as $vendor=>$module)
			{
				$vendorItems = array();
				foreach ($module as $name=>$item)
				{
					$moduleItems = array();
					
					if(isset($item['Frontend']))
					{
						$obj = new stdClass();
						$obj->id = $this->_externalsPath . $vendor.'/'.$name.'/frontend/';
						$obj->text = 'Frontend';
						$obj->expanded = false;
						$obj->leaf = false;

						$classList = array();
						foreach ($item['Frontend'] as $class)
						{
							$obj2 = new stdClass();
							$obj2->id = $class;
							$obj2->text = $class;
							$obj2->leaf = true;
							$obj2->url = Backend_Designer_Code::getControllerUrl($class);
							$classList[] = $obj2;
						}
						
						$obj->children =  $classList;						
						$moduleItems[] = $obj;
					}

					if(isset($item['Backend']))
					{
						$obj = new stdClass();
						$obj->id = $this->_externalsPath . $vendor.'/'.$name.'/backend/';
						$obj->text = 'Backend';
						$obj->expanded = false;
						$obj->leaf = false;

						$classList = array();
						foreach ($item['Backend'] as $class)
						{
							$obj2 = new stdClass();
							$obj2->id = $class;
							$obj2->text = $class;
							$obj2->leaf = true;
							$obj2->url = Backend_Designer_Code::getControllerUrl($class);
							$classList[] = $obj2;
						}
						
						$obj->children =  $classList;					
						$moduleItems[] = $obj;
					}

					$obj = new stdClass();
					$obj->id = $this->_externalsPath . $vendor.'/'.$name;
					$obj->text = $name;
					$obj->expanded = false;
					$obj->leaf = false;
					$obj->children =  $moduleItems;
					$vendorItems[] = $obj;
				}
		
				$obj = new stdClass();
				$obj->id = $this->_externalsPath . $vendor;
				$obj->text = $vendor;
				$obj->expanded = false;
				$obj->leaf = false;
				$obj->children = $vendorItems;
			}
				
			$objRoot = new stdClass();
			$objRoot->id = 'externalroot';
			$objRoot->text = Lang::lang()->get('EXTERNAL');
			$objRoot->expanded = false;
			$objRoot->leaf = false;
			$objRoot->children = array($obj);		
		}
		return $objRoot;
	}
	
	
	/**
	 * Get external IDE projects
	 * @return array
	 */
	public function getProjects()
	{
		$data = array();
			
		foreach ($this->_configs as $path=>$config)
		{
			if(isset($config['PROJECTS']))
			{
				foreach ($config['PROJECTS'] as $name=>$srcPath)
				{
					$filePath  = $this->_externalsPath . $path . '/' . $srcPath;
					$data[$config['INFO']['namespace']][$config['INFO']['name']][] = array('name'=>$name,'path'=>$filePath);
				}
			}
		}
		$result = array();
		if(!empty($data))
		{		
			foreach($data as $vendor=>$module)
			{
				$vendorItems = array();
				foreach ($module as $name=>$item)
			    {
					$moduleItems = array();
					foreach ($item as $config)
					{
						$obj = new stdClass();
						$obj->id = $config['path'];
						$obj->text = $config['name'];
						$obj->leaf = true;
						$moduleItems[] = $obj;
					}
						
					$obj = new stdClass();
					$obj->id = $this->_externalsPath . $vendor.'/'.$name;
					$obj->text = $name;
					$obj->expanded = false;
					$obj->leaf = false;
					$obj->children =  $moduleItems;
					$vendorItems[] = $obj;
				}
				
				$obj = new stdClass();
				$obj->id = $this->_externalsPath . $vendor;
				$obj->text = $vendor;
				$obj->expanded = false;
				$obj->leaf = false;
				$obj->children = $vendorItems;
			}
			
			$objRoot = new stdClass();
			$objRoot->id = 'externalroot';
			$objRoot->text = Lang::lang()->get('EXTERNAL');
			$objRoot->expanded = false;
			$objRoot->leaf = false;
			$objRoot->children = array($obj);
			$result[] = $objRoot;
			
		}	
		return $result;
	}
	
	/**
	 * Get external Blocks
	 * @return array
	 */
	public function getBlocks()
	{
		$classes = $this->getClasses();	
		$blocks = array();
			
		foreach ($classes as $class=>$path)		
			if(strpos($class, 'Block_') ===0)
				$blocks[$class] = $path;
			
		return $blocks;
	}
	
}