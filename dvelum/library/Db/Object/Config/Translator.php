<?php 
class Db_Object_Config_Translator
{
	protected $_mainConfig = '';
	protected $_extTranslations = array();
	protected $_translation = false;
	/**
	 * @var Lang
	 */
	protected $_lang = false;
	/**
	 * @param string $configPath - path to translation Array config
	*/
	public function __construct($configPath)
	{
		$this->_mainConfig = $configPath;
	}

	/**
	 * Get object fields translation
	 * @param boolean $autoCreate , otional default - true
	 * @return Config_Abstract | boolean false
	 */
	public function getTranslation($autoCreate = true)
	{
		if($this->_translation)
			return $this->_translation;
		
		if(!file_exists($this->_mainConfig))
		{
			if(!$autoCreate)
				return false;				
			//create translation config
			if(!Config_File_Array::create($this->_mainConfig))
				return false;
		}

		$this->_translation = new Config_File_Array($this->_mainConfig);

		if(!empty($this->_extTranslations))
		{
			foreach ($this->_extTranslations as $path)
			{
				$extCfg = new Config_File_Array($path);
				foreach ($extCfg as $k=>$v)
					if(!$this->_translation->offsetExists($k))
						$this->_translation->set($k, $v);
			}
		}
		return $this->_translation;
	}
	/**
	 * Add external translations
	 * @param array $paths
	 */
	public function addTranslations(array $paths)
	{
		$this->_extTranslations = array_merge($this->_extTranslations, $paths);
	}
	/**
	 * Get Main config path
	 * @return string
	 */
	public function getMainConfig()
	{
		return $this->_mainConfig;
	}
	
	/**
	 * Translate Object config
	 * @param string $objectName
	 * @param array & $objectConfig
	 */
	public function translate($objectName , & $objectConfig)
	{
		$translation = $this->getTranslation();
		if($translation)
		{
			if(isset($translation[$objectName]['title']) && strlen($translation[$objectName]['title']))
				$objectConfig['title'] = $translation[$objectName]['title'];
			else
				$objectConfig['title'] = $objectName;
			 
			if(isset($translation[$objectName]['fields']) && is_array($translation[$objectName]['fields']))
				$fieldTranslates = $translation[$objectName]['fields'];
		}
		else
		{
			if(isset($dataLink[$objectName]['title']) && strlen($objectConfig[$objectName]['title']))
				$objectConfig['title'] = $objectConfig[$objectName]['title'];
			else
				$objectConfig['title'] = $objectName;
		}
		 
		foreach ($objectConfig['fields'] as $k => &$v)
		{
			if(isset($v['lazyLang']) && $v['lazyLang'])
			{
				if(!$this->_lang)
					$this->_lang = Lang::lang();
				
				if(isset($v['title']))
					$v['title'] = $this->_lang->get($v['title']);
				else 
					$v['title'] = '';
			}	
			elseif(isset($fieldTranslates[$k]) && strlen($fieldTranslates[$k]))
			{
				$v['title'] = $fieldTranslates[$k];
			}
			elseif(!isset($v['title']) || !strlen($v['title']))
			{
				$v['title'] = $k;
			}
		}
	}
}