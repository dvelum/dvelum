<?php
class Backend_Designer_Code 
{
	/**
	 * Get controller url
	 * @param string $controllerName
	 * @return string
	 */
	static public function getControllerUrl($controllerName)
	{
		$appCfg = Registry::get('main' , 'config');
		$designerConfig = Config::factory(Config::File_Array, $appCfg->get('configs').'designer.php');		
    	$templates = $designerConfig->get('templates');	
		
		if(!class_exists($controllerName))
			return '';
		$reflector = new ReflectionClass($controllerName);
		
		if(!$reflector->isSubclassOf('Backend_Controller') && !$reflector->isSubclassOf('Frontend_Controller')){
			return array();
		}

		$url = array();
		
		$manager = new Backend_Modules_Manager();
		
		if($reflector->isSubclassOf('Backend_Controller'))
		{
			$url[] = $templates['adminpath'];
			$url[] = $manager->getModuleName($controllerName);
		}
		elseif ($reflector->isSubclassOf('Frontend_Controller'))
		{			
			if($appCfg['frontend_router_type'] == 'module')
			{		
				$module = self::_moduleByClass($controllerName);
				if($module!==false)
				{
					$urlcode = Model::factory('Page')->getCodeByModule($module);
					if($urlcode!==false)
						$url[] = $urlcode;
				}
					
			}
			elseif ($appCfg['frontend_router_type'] == 'path')
			{
					$paths = explode('_',str_replace(array('Frontend_'), '', $controllerName));
					$pathsCount = count($paths)-1;
					if($paths[$pathsCount]==='Controller')
						$paths = array_slice($paths, 0 ,$pathsCount);
						
					$url = array_merge($url , $paths);		
			}
			elseif($appCfg['frontend_router_type'] == 'config')
			{
			  $urlCode = self::_moduleByClass($controllerName);
			  if($urlCode!==false){
			    $url[] = $urlCode;
			  }
			}
		}
		$url[]='';
		
		Request::setDelimetr($templates['urldelimeter']);
		Request::setRoot($templates['wwwroot']);
		$url = Request::url($url , false);	
		Request::setDelimetr($appCfg['urlDelimetr']);
		Request::setRoot($appCfg['wwwroot']);
		return $url;
	}
	/**
	 * Get possible actions from Controller class.
	 * Note! Code accelerator (eaccelerator, apc, xcache, etc ) should be disabled to get comment line.
	 * Method returns only public methods that ends with "Action" 
	 * @param string $controllerName
	 * @return array like array(
	 * 		array(
	 * 			'name' => action name without "Action" postfix
	 * 			'comment'=> doc comment
	 * 		)
	 * )
	 */
	static public function getPossibleActions($controllerName)
	{				
		$manager = new Backend_Modules_Manager();
		$appCfg = Registry::get('main' , 'config');
		$designerConfig = Config::factory(Config::File_Array, $appCfg->get('configs').'designer.php');		
		
		$templates = $designerConfig->get('templates');	
			
		$reflector = new ReflectionClass($controllerName);
		
		if(!$reflector->isSubclassOf('Backend_Controller') && !$reflector->isSubclassOf('Frontend_Controller')){
			return array();
		}
		
		$actions = array();
		$methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
		
		$url = array();
		
		if($reflector->isSubclassOf('Backend_Controller'))
		{
			$url[] = $templates['adminpath'];
			$url[] = $manager->getModuleName($controllerName);
		}
		elseif($reflector->isSubclassOf('Frontend_Controller'))
		{
			
			if($appCfg['frontend_router_type'] == 'module')
			{
				
				$module = self::_moduleByClass($controllerName);
				if($module !== false)
				{
					$urlcode = Model::factory('Page')->getCodeByModule($module);
					if($urlcode !== false)
						$url[] = $urlcode;
				}
			}
			elseif($appCfg['frontend_router_type'] == 'path')
			{
				$paths = explode('_' , str_replace(array('Frontend_') , '' , $controllerName));
				$pathsCount = count($paths) - 1;
				
				if($paths[$pathsCount] === 'Controller')
					$paths = array_slice($paths , 0 , $pathsCount);
				
				$url = array_merge($url , $paths);
			}
		    elseif($appCfg['frontend_router_type'] == 'config')
			{
			  $urlCode = self::_moduleByClass($controllerName);
			  if($urlCode!==false){
			    $url[] = $urlCode;
			  }
			}
		}
		
		if(!empty($methods))
		{
			Request::setDelimetr($templates['urldelimeter']);
			Request::setRoot($templates['wwwroot']);
			foreach ($methods as $method)
			{
				if(substr($method->name , -6) !== 'Action')
					continue;
					
				$actionName = substr($method->name , 0 , -6);
				$paths = $url;
				$paths[] = $actionName;
					
				$actions[] = array(
					'name'=>$actionName,
					'code'=>$method->name,
					'url'=>Request::url($paths , false),
					'comment'=>self::_clearDocSymbols($method->getDocComment()) 
				);
			}
			
			Request::setDelimetr($appCfg['urlDelimetr']);
			Request::setRoot($appCfg['wwwroot']);
		}
		return $actions;
	}
	
	static protected function _moduleByClass($class)
	{
		$modules = Config::factory(Config::File_Array, Registry::get('main' , 'config')->get('frontend_modules'));
		$moduleName = false;
		if(!empty($modules)){
			foreach($modules as $k=>$config){
				if($config['class']===$class){
					return $k;
				}
			}
		}
		return false;
	}
	/**
	 * Clear string from comment symbols
	 * @param string $string
	 */
	static protected function _clearDocSymbols($string)
	{
		return str_replace(array('/*','*/','*') , '' , $string);
	}
}