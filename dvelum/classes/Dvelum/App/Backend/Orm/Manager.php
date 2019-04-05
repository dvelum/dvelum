<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
declare(strict_types=1);
namespace Dvelum\App\Backend\Orm;

use Dvelum\File;
use Dvelum\Orm;
use Dvelum\Lang;
use Dvelum\Config;
use Dvelum\Orm\Record\Config\Translator;
use \Exception;

class Manager
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

		$objectConfig = Orm\Record\Config::factory($name);
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

		foreach ($localisations as $file)
		{
			if(file_exists($langWritePath . $file) && !is_writable($langWritePath . $file)){
                return self::ERROR_FS_LOCALISATION;
            }

            $localeName = basename(dirname($file));
            $translator = $this->getTranslator($localeName, $name);
            if(!$translator->removeObjectTranslation($name, true)){
                return self::ERROR_FS_LOCALISATION;
            }
		}

        $path = $objectsWrite . Config::storage()->get('orm.php')->get('object_configs') . $name . '.php';

		try{
		  $cfg = Orm\Record\Config::factory($name);
		}catch (\Exception $e){
		  return self::ERROR_FS;
		}
		
		$builder = Orm\Record\Builder::factory($name);
		
		if($deleteTable && !$cfg->isLocked() && !$cfg->isReadOnly()){
		  if(!$builder->remove()){
			return self::ERROR_DB;
		  }
		}
		
		if(!@unlink($path))
			return self::ERROR_FS;
		
		$localisationKey = strtolower($name);
        $langStorage = Lang::storage();

		foreach ($localisations as $file)
		{		
			$cfg = $langStorage->get($file);
			if($cfg->offsetExists($localisationKey)){
				$cfg->remove($localisationKey);
                $langStorage->save($cfg);
			}

            $localeName = basename(dirname($file));
            $translator = $this->getTranslator($localeName, $name);
            $translator->removeObjectTranslation($name, true);
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
			$data =  File::scanFiles($path, false, false, \File::Dirs_Only);
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
			$cfg = Orm\Record\Config::factory($object);
		}catch (\Exception $e){
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
	 * @return false|array
	 */
	public function getIndexConfig($object , $index)
	{	
		try {
			$cfg = Orm\Record\Config::factory($object);
		}catch (\Exception $e){
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
		try{
			$objectCfg = Orm\Record\Config::factory($objectName);
		}catch (\Exception $e){
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
            $localeName = basename(dirname($file));

            $translator = $this->getTranslator($localeName, $objectName);
            $translation = $translator->getTranslation($objectName);

            unset($translation['fields'][$fieldName]);

		    $langStorage = Lang::storage();
			$cfg = $langStorage->get($file);

			if($cfg->offsetExists($localisationKey))
			{
                $cfg->offsetUnset($localisationKey);
                if(!$langStorage->save($cfg)){
                    return self::ERROR_FS_LOCALISATION;
                }
			}

            if(!$translator->save($objectName, $translation)){
                return self::ERROR_FS_LOCALISATION;
            }
		}	
		return 0;
	}
	
	/**
	 * Rename object field
	 * @param Orm\Record\Config $cfg
	 * @param string $oldName
	 * @param string $newName
	 * @return integer 0 on success or error code
	 */
	public function renameField(Orm\Record\Config $cfg , $oldName , $newName)
	{
		$localisations = $this->getLocalisations();
		$langWritePath = Lang::storage()->getWrite();

		foreach ($localisations as $file){
            if(file_exists($langWritePath . $file) && !is_writable($langWritePath . $file)){
                return self::ERROR_FS_LOCALISATION;
            }
            $localeName = basename(dirname($file));
            $translator = $this->getTranslator($localeName, $cfg->getName());
            if(!$translator->removeObjectTranslation($cfg->getName(), true)){
                return self::ERROR_FS_LOCALISATION;
            }
        }

		$localisationKey = strtolower($cfg->getName());
        $langStorage = Lang::storage();

		foreach ($localisations as $file)
		{
            $langCfg = $langStorage->get($file,true,true);

            if($langCfg->offsetExists($localisationKey)) {
                $langCfg->offsetUnset($localisationKey);
                if(!$langStorage->save($langCfg)){
                    return self::ERROR_FS_LOCALISATION;
                }
            }

            $localeName = basename(dirname($file));
            $translator = $this->getTranslator($localeName, $cfg->getName());
            $translation = $translator->getTranslation($cfg->getName());

            if(isset($translation['fields'][$oldName])){
                $translation['fields'][$newName] = $translation['fields'][$oldName];
            }
            unset($translation['fields'][$oldName]);

            if(!$translator->save($cfg->getName(), $translation)){
                return self::ERROR_FS_LOCALISATION;
            }
		}

        if(!$cfg->renameField($oldName, $newName))
            return self::ERROR_EXEC;

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
        $objectConfig = Orm\Record\Config::factory($oldName);
	   /*
		* Check fs write permissions for associated objects
		*/
		$assoc = Orm\Record\Expert::getAssociatedStructures($oldName);

		if(!empty($assoc))
			foreach ($assoc as $config)
				if(!is_writable(Config::storage()->getPath($path).strtolower($config['object']).'.php'))
					return self::ERROR_FS_LOCALISATION;
		
	   /*
		* Check fs write permissions for localisation files
		*/
        $langStorage = Lang::storage();
		$localisations = $this->getLocalisations();
		$langWritePath = $langStorage->getWrite();

        $translator = $objectConfig->getTranslator();

		foreach ($localisations as $file)
		{
            if(file_exists($langWritePath . $file) && !is_writable($langWritePath . $file)){
                return self::ERROR_FS_LOCALISATION;
            }

            $localeName = basename(dirname($file));
            $translator = $this->getTranslator($localeName, $oldName);
            if(!$translator->removeObjectTranslation($oldName, true)){
                return self::ERROR_FS_LOCALISATION;
            }
        }

		$localisationKey = strtolower($oldName);
		foreach ($localisations as $file)
		{
            $localeName = basename(dirname($file));

			$cfg = $langStorage->get($file,true,true);
			if($cfg->offsetExists($localisationKey))
			{
				$cfg->remove($localisationKey);
                if(!$langStorage->save($cfg)){
                    return self::ERROR_FS;
                }
			}

            $localeName = basename(dirname($file));
            $translator = $this->getTranslator($localeName, $oldName);
            $oldTranslations = $translator->getTranslation($oldName);
            if(!$translator->removeObjectTranslation($oldName)){
                return self::ERROR_FS_LOCALISATION;
            }
            $translator = $this->getTranslator($localeName, $newName);
            if(!$translator->save($newName, $oldTranslations)){
                return self::ERROR_FS_LOCALISATION;
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
				
				$oConfig = Orm\Record\Config::factory($object);
				
				foreach ($fields as $fName=>$fType)				
					if($oConfig->getField($fName)->isLink())
						if(!$oConfig->setFieldLink($fName, $newName))
							return self::ERROR_EXEC;
									
				if(!$oConfig->save())
					return self::ERROR_FS;
			}
		}
		return 0;
	}

    /**
     * Sync Distributed index structure
     * add fields into ObjectId
     * @param string $objectName
     * @throws Exception
     * @return bool
     */
    public function syncDistributedIndex($objectName)
    {
        $oConfig = Orm\Record\Config::factory($objectName);
        $distIndexes = $oConfig->getDistributedIndexesConfig();

        $idObject = $oConfig->getDistributedIndexObject();
        $idObjectConfig = Orm\Record\Config::factory($idObject);

        foreach ($distIndexes as $name=>$info)
        {
            if($name == $idObjectConfig->getPrimaryKey()){
                continue;
            }

            $cfg = $oConfig->getFieldConfig($name);
            $cfg['system'] = false;
            $cfg['db_isNull'] = true;

            $unique = false;
            if(isset($cfg['unique']) && $cfg['unique']){
                $unique = true;
            }
            $idObjectConfig->setFieldConfig($name,$cfg);
            $idObjectConfig->setIndexConfig($name,[
                'columns' => [$name],
                'fulltext' => false,
                'unique' => $unique,
            ]);
        }
        return $idObjectConfig->save();
    }


    public function getTranslator(string $locale, string $objectName) : Translator
    {
        $ormConfig = Config::storage()->get('orm.php');
        $commonFile = $locale . '/objects.php';
        $objectsDir = $locale . '/' . $ormConfig->get('translations_dir');
        return new Translator($commonFile, $objectsDir);
    }
}