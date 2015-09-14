<?php
/**
 * Frontend modules manager
 */
class Modules_Manager
{
	protected $_config;
	protected $_distConfig;
	protected $_mainconfigKey = 'backend_modules';
	/**
	 * @var Config_Abstract
	 */
	protected $_appConfig;
	/**
	 * @var Config_File_Array
	 */
	protected $_modulesLocale;

	static protected $_classRoutes = false;

	public function __construct()
	{
		$this->_appConfig = Registry::get('main' , 'config');
		$configPath =  $this->_appConfig->get($this->_mainconfigKey);
		$this->_config = Config::storage()->get($configPath , false , true);

		$this->_distConfig = new Config_File_Array(Config::storage()->getApplyTo() . $configPath);

		$locale = Lang::lang()->getName();
		$this->_modulesLocale = Lang::storage()->get($locale.'/modules/'.basename($configPath));
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
		$data = $this->_config->get($name);
		$data['title'] = '';

		if(isset($this->_modulesLocale[$name]))
			$data['title'] = $this->_modulesLocale[$name];

		return $data;
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
		if(isset($cfg['class']) && !empty($cfg['class']))
			return $cfg['class'];
		else
			return '';
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
	 * @return array:
	 */
	public function getList()
	{
		$data = $this->_config->__toArray();
		foreach ($data as $module=>&$cfg)
		{
			if($this->_distConfig->offsetExists($module)){
				$cfg['dist'] = true;
			}else{
				$cfg['dist'] = false;
			}

			if(!isset($cfg['in_menu']))
				$cfg['in_menu'] = true;

			if($this->_modulesLocale->offsetExists($module)){
				$cfg['title'] = $this->_modulesLocale->get($module);
			}else{
				$cfg['title'] = $module;
			}

		}unset($cfg);
		return $data;
	}

	/**
	 * Remove module
	 * @param string $name
	 */
	public function removeModule($name)
	{
		if($this->_config->offsetExists($name))
			$this->_config->remove($name);

		if($this->_modulesLocale->offsetExists($name))
			$this->_modulesLocale->remove($name);

		if(!$this->_modulesLocale->save())
			return false;

		return $this->save();
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
		return  $this->save();
	}

	/**
	 * Update module data
	 * @param $name
	 * @param array $data
	 * @return boolean
	 */
	public function updateModule($name , array $data)
	{
		if($name !== $data['id']){
			$this->_modulesLocale->remove($name);
			$this->_config->remove($name);
		}

		if(isset($data['title'])){
			$this->_modulesLocale->set($data['id'] , $data['title']);
			if(!$this->_modulesLocale->save()){
				return false;
			}
			unset($data['title']);
		}
		$this->_config->set($data['id'] , $data);
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

	/**
	 * Get list of Controllers
	 * @return array
	 */
	public function getControllers()
	{
		$backendConfig = Config::storage()->get('backend.php');
		$autoloadCfg = $this->_appConfig->get('autoloader');
		$systemControllers = $backendConfig->get('system_controllers');

		$paths = $autoloadCfg['paths'];
		$dir = $this->_appConfig->get('backend_controllers_dir');

		$data = array();

		foreach($paths as $path){
			if(!is_dir($path.'/'.$dir)){
				continue;
			}
			$folders = File::scanFiles($path.'/'.$dir,false,true,File::Dirs_Only);

			if(empty($folders))
			 	continue;

			foreach ($folders as $item)
			{
				$name = basename($item);

				if(file_exists($item.'/Controller.php'))
				{
					$name = str_replace($path.'/', '', $item.'/Controller.php');
					$name = Utils::classFromPath($name);

					/*
					 * Skip system controller
					 */
					if(in_array($name, $systemControllers , true))
						continue;

					$data[$name] = array('id'=>$name,'title'=>$name);
				}
			}
		}
		return array_values($data);
	}

	/**
	 * Get list of controllers without modules
	 */
	public function getAvailableControllers()
	{
		$list = $this->getControllers();

		/*
         * Hide registered controllers
         */
		$registered = array_flip(Utils::fetchCol('class' , $this->getConfig()->__toArray()));
		foreach($list as $k=>$v){
			if(isset($registered[$v['id']])){
				unset($list[$k]);
			}
		}
		return array_values($list);
	}
}