<?php
class Backend_Orm_Manager
{
	const ERROR_EXEC = 1;
	const ERROR_FS = 2;	
	const ERROR_DB = 3;
	const ERROR_FS_LOCALISATION = 4;
	const ERROR_INVALID_OBJECT = 5;
	const ERROR_INVALID_FIELD = 6;
	const ERROR_HAS_LINKS = 7;
		
	/**
	 * Remove object from ORM
	 * @param string $name
	 * @param boolean $deleteTable - optional, default true
	 * @return integer
	 */
	public function removeObject($name , $deleteTable = true)
	{	
		//$assoc = Db_Object_Expert::getAssociatedStructures($name);
		//if(!empty($assoc))
		//	return self::ERROR_HAS_LINKS;

		$objectConfig = Db_Object_Config::getInstance($name);
		$manyToMany = $objectConfig->getManyToMany();

		if(!empty($manyToMany))
		{
			$linkedFields = [];
			foreach($manyToMany as $object=>$fields)
			{
				foreach($fields as $fieldName=>$cfg){
					$linkedFields[] = $fieldName;
				}
			}

			if(!empty($linkedFields))
			{
				foreach($linkedFields as $field)
				{
					$relatedObject = $objectConfig->getRelationsObject($field);
					$result = $this->removeObject($relatedObject , $deleteTable);

					if($result!==0){
						return $result;
					}
				}
			}
		}

			
		$localisations = $this->getLocalisations();
		$langWritePath = Lang::storage()->getWrite();
		$objectsWrite = Config::storage()->getWrite();

		foreach ($localisations as $file){
			if(file_exists($langWritePath . $file) && !is_writable($langWritePath . $file))
				return self::ERROR_FS_LOCALISATION;
		}

		$path = $objectsWrite . Registry::get('main' , 'config')->get('object_configs') . $name . '.php';

		try{
		  $cfg = Db_Object_Config::getInstance($name);
		}catch (Exception $e){
		  return self::ERROR_FS;
		}
		
		$builder = new Db_Object_Builder($name);
		
		if($deleteTable && !$cfg->isLocked() && !$cfg->isReadOnly()){
		  if(!$builder->remove()){
			return self::ERROR_DB;
		  }
		}
		
		if(!@unlink($path))
			return self::ERROR_FS;
		
		$localisationKey = strtolower($name);
		foreach ($localisations as $file) 
		{		
			$cfg = Lang::storage()->get($file);
			if($cfg->offsetExists($localisationKey)){
				$cfg->remove($localisationKey);
				$cfg->save();
			}			
		}			 
		return 0;
	}
	/**
	 * Get list of localization files
	 */
	public function getLocalisations()
	{
		$paths = Lang::storage()->getPaths();
		$dirs = [];

		foreach($paths as $path)
		{
			if(!is_dir($path)){
				continue;
			}
			$data =  File::scanFiles($path,false,false,File::Dirs_Only);
			foreach($data as $k=>&$v){
				if(!file_exists($v . '/objects.php')){
					unset($data[$k]);
					continue;
				}
				$v = str_replace($path , '',$v) .'/objects.php';
			}
			$dirs = array_merge($dirs,$data);
		}
		return array_unique($dirs);
	}
	
	/**
	 * Get field config
	 * @param string $object
	 * @param string $field
	 * @return false|array
	 */
	public function getFieldConfig($object , $field)
	{
		try {
			$cfg = Db_Object_Config::getInstance($object);
		}catch (Exception $e){
			return false;
		}
		 
		if(!$cfg->fieldExists($field))
			return false;
		 
		$fieldCfg = $cfg->getFieldConfig($field);
		$fieldCfg['name'] = $field;
		
		if(isset($fieldCfg['db_default']) && $fieldCfg['db_default']!==false){
		  $fieldCfg['set_default'] = true;
		}else{
		  $fieldCfg['set_default'] = false;
		}
		 
		if(!isset($fieldCfg['type']) || empty($fieldCfg['type']))
			$fieldCfg['type'] = '';
			
		if(isset($fieldCfg['link_config']) && !empty($fieldCfg['link_config']))
			foreach ($fieldCfg['link_config'] as $k=>$v)
				$fieldCfg[$k] = $v;
		
		return $fieldCfg;
	}
	/**
	 * Get index config
	 * @param string $object
	 * @param string $index
	 * @return boolean
	 */
	public function getIndexConfig($object , $index)
	{	
		try {
			$cfg = Db_Object_Config::getInstance($object);
		}catch (Exception $e){
			return false;
		}	
		if(!$cfg->indexExists($index))
			return false;
			
		$data = $cfg->getIndexConfig($index);
		$data['name'] = $index;
		return $data;
	}
	
	/**
	 * Remove object field
	 * @param string $objectName
	 * @param string $fieldName
	 * @return bool  - 0 - success or error code
	 */
	public function removeField($objectName , $fieldName)
	{
		$localisations = $this->getLocalisations();

		try{
			$objectCfg = Db_Object_Config::getInstance($objectName);
		}catch (Exception $e){
			return self::ERROR_INVALID_OBJECT;
		}
		
		if(!$objectCfg->fieldExists($fieldName))
			return self::ERROR_INVALID_FIELD;
		
		$localisations = $this->getLocalisations();

		$objectCfg->removeField($fieldName);
		 
		if(!$objectCfg->save())
			return self::ERROR_FS;
		
		$localisationKey = strtolower($objectName);
		foreach ($localisations as $file)
		{
			$cfg = Lang::storage()->get($file);
			if($cfg->offsetExists($localisationKey))
			{
				$cfgArray = $cfg->get($localisationKey);
				if(isset($cfgArray['fields']) && isset($cfgArray['fields'][$fieldName]))
				{
					unset($cfgArray['fields'][$fieldName]);
					$cfg->set($localisationKey, $cfgArray);
					if(!$cfg->save()){
						return self::ERROR_FS_LOCALISATION;
					}
				}
			}			
		}	
		return 0;
	}
	
	/**
	 * Rename object field
	 * @param Db_Object_Config $cfg
	 * @param string $oldName
	 * @param string $newName
	 * @return integer 0 on success or error code
	 */
	public function renameField(Db_Object_Config $cfg , $oldName , $newName)
	{
		$localisations = $this->getLocalisations();
		$langWritePath = Lang::storage()->getWrite();
		foreach ($localisations as $file)
			if(file_exists($langWritePath . $file) && !is_writable($langWritePath . $file))
				return self::ERROR_FS_LOCALISATION;

		if(!$cfg->renameField($oldName, $newName))
			return self::ERROR_EXEC;
		
		$localisationKey = strtolower($cfg->getName());
		foreach ($localisations as $file)
		{
			$cfg = Lang::storage()->get($file,true,true);
			if($cfg->offsetExists($localisationKey))
			{
				$cfgArray = $cfg->get($localisationKey);
				if(isset($cfgArray['fields']) && isset($cfgArray['fields'][$oldName]))
				{
					$oldCfg = $cfgArray['fields'][$oldName];
					unset($cfgArray['fields'][$oldName]);
					$cfgArray['fields'][$newName] = $oldCfg;
					$cfg->set($localisationKey, $cfgArray);
					$cfg->save();
				}
			}
		}
		return 0;
	}
	/**
	 * Rename Db_Object
	 * @param string $path - configs path
	 * @param string $oldName
	 * @param string $newName
	 * @return integer 0 on success or error code
	 */
	public function renameObject($path , $oldName , $newName)
	{		
	   /*
		* Check fs write permissions for associated objects
		*/
		$assoc = Db_Object_Expert::getAssociatedStructures($oldName);
		if(!empty($assoc))
			foreach ($assoc as $config)
				if(!is_writable(Config::storage()->getPath($path).strtolower($config['object']).'.php'))
					return self::ERROR_FS_LOCALISATION;
		
	   /*
		* Check fs write permissions for localisation files
		*/
		$localisations = $this->getLocalisations();
		$langWritePath = Lang::storage()->getWrite();
		foreach ($localisations as $file)
			if(file_exists($langWritePath . $file) && !is_writable($langWritePath . $file))
				return self::ERROR_FS_LOCALISATION;

		$localisationKey = strtolower($oldName);
		$newLocalisationKey = strtolower($newName);
		
		foreach ($localisations as $file)
		{
			$cfg = Lang::storage()->get($file,true,true);
			if($cfg->offsetExists($localisationKey))
			{
				$cfgArray = $cfg->get($localisationKey);
				$cfg->remove($localisationKey);
				$cfg->set($newLocalisationKey, $cfgArray);
				$cfg->save();				
			}
		}
		
		$newFileName = Config::storage()->getWrite(). $path . $newName . '.php';
		$oldFileName = Config::storage()->getPath($path) . $oldName . '.php';

		if(!@rename($oldFileName, $newFileName))
			return self::ERROR_FS;
		
		
		if(!empty($assoc))
		{
			foreach ($assoc as $config)
			{
				$object = $config['object'];
				$fields = $config['fields'];
				
				$oConfig = Db_Object_Config::getInstance($object);
				
				foreach ($fields as $fName=>$fType)				
					if($oConfig->isLink($fName))
						if(!$oConfig->setFieldLink($fName, $newName))
							return self::ERROR_EXEC;
									
				if(!$oConfig->save())
					return self::ERROR_FS;
			}
		}
		return 0;
	}
}