<?php
/**
 * Frontend modules manager
 */
class Backend_Modules_Manager
{
	protected $_config;
	protected $_mainconfigKey = 'backend_modules';
	/**
	 * @var Config_File_Array
	 */
	protected $_modulesLocale;
	static protected $_classRoutes = false;

	public function __construct()
	{
		$applicationConfig = Registry::get('main' , 'config');
		$configPath =   $applicationConfig->get($this->_mainconfigKey);
		$this->_config = Config::factory(Config::File_Array , $configPath);
		$locale = Lang::lang()->getName();
		$this->modulesLocale = new Config_File_Array($applicationConfig->get('lang_path').$locale.'/modules/'.basename($configPath));
	}

	/**
	 * Get registered modules
	 * @return array
	 */
	public function getRegisteredModules()
	{
		$data = $this->_config->__toArray();
		return array_keys($data);
	}

	/**
	 * Check if module exists
	 * @param string $name
	 * @return boolean
	 */
	public function isValidModule($name)
	{
		return $this->_config->offsetExists($name);
	}

	/**
	 * Get module configuration
	 * @param string $name
	 * @return array
	 */
	public function getModuleConfig($name)
	{
		return $this->_config->get($name);
	}

	/**
	 * Get Module class
	 * @param string $name
	 * @return boolean false | string
	 */
	public function getModuleController($name)
	{
		if(!$this->isValidModule($name))
			return false;

		$cfg = $this->_config->get($name);
		return $cfg['class'];
	}

	/**
	 * Get module name for class
	 * @param string $class
	 * @return string
	 */
	public function getModuleName($class)
	{
		return Utils_String::formatClassName(strtolower(str_replace(array('Backend_','_Controller'), '', $class)));
	}

	/**
	 * Get module name for controller
	 * @param string $controller
	 * @return boolean false | string
	 */
	public function getControllerModule($controller)
	{
		if(!self::$_classRoutes){
		    $config = $this->_config->__toArray();
			foreach ($config as $module=>$cfg)
				self::$_classRoutes[$cfg['class']] = $module;
		}

		if(!isset(self::$_classRoutes[$controller]))
			return false;
		else
			return self::$_classRoutes[$controller];
	}

	/**
	 * Get modules list
	 * @return multitype:
	 */
	public function getList()
	{
		$data = $this->_config->__toArray();
		foreach ($data as $module=>&$cfg)
		{
			if(!isset($cfg['in_menu']))
				$cfg['in_menu'] = true;

			if($this->modulesLocale->offsetExists($module)){
				$cfg['title'] = $this->modulesLocale->get($module);
			}else{
				$cfg['title'] = $module;
			}

		}unset($cfg);
		return $data;
	}

	/**
	 * Remove modules
	 */
	public function removeAll()
	{
		$this->_config->removeAll();
		$this->resetCache();
	}

	/**
	 * Remove module
	 * @param string $name
	 */
	public function removeModule($name)
	{
		if($this->_config->offsetExists($name))
			$this->_config->remove($name);
		$this->resetCache();
	}

	/**
	 * Add module
	 * @param string $name
	 * @param array $config
	 */
	public function addModule($name , array $config)
	{
		$this->_config->set($name , $config);
		$this->resetCache();
	}

	/**
	 * Update module data
	 * @param $name
	 * @param array $data
	 * @return boolean
	 */
	public function updateModule($name , array $data)
	{
		if(isset($data['title'])){
			$this->modulesLocale->set($name , $data['title']);
			if(!$this->modulesLocale->save()){
				return false;
			}
			unset($data['title']);
		}
		$cfg = array_merge($this->getModuleConfig($name),$data);
		$this->_config->set($name , $cfg);
		$this->resetCache();
		return $this->save();
	}

	/**
	 * Save modules config
	 * @return boolean
	 */
	public function save()
	{
		$this->resetCache();
		return $this->_config->save();
	}
	/**
	 * Reset modules cache
	 */
	public function resetCache()
	{
		self::$_classRoutes = false;
		Config::resetCache();
	}
	/**
	 * Get configuration object
	 * @return Config_Abstract
	 */
	public function getConfig()
	{
	  return $this->_config;
	}
}