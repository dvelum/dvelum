<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2016  Kirill A Egorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Orm\Object\Config;

class Translator
{
	protected $_mainConfig = '';
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
	 * @return Config_Abstract | boolean false
	 */
	public function getTranslation()
	{
		if(!$this->_translation){
			$this->_translation = \Lang::storage()->get($this->_mainConfig, true, true);
		}
		return $this->_translation;
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
					$this->_lang = \Lang::lang();
				
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