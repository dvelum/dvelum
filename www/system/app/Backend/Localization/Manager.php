<?php
class Backend_Localization_Manager
{
  /**
   * @var Config_Abstract
   */
  protected $_appConfig;
  /**
   * Message language
   * @var Lang
   */
  protected $_lang;

  protected $_indexLanguage = 'en';

  /**
   * Localizations file path
   * @var string
   */
  protected $_langsPath;

  /**
   * @param Config_Abstract $appConfig
   */
  public function __construct(Config_Abstract $appConfig)
  {
    $this->_appConfig =  $appConfig;
    $this->_langsPath =  $this->_appConfig->get('lang_path');
    $this->_lang = Lang::lang();
  }
  /**
   * Get list of system languages
   * @param boolean $onlyMain - optional. Get only global locales without subpackages
   * @return array
   */
  public function getLangs($onlyMain = true)
  {
    $langDir = $this->_appConfig->get('lang_path');
    if(!is_dir($langDir))
      return array();

    $files = File::scanFiles($langDir , array('.php'), !$onlyMain , File::Files_Only);
    $data = array();

    foreach ($files as $file)
    {
      // Windows fix
      if(DIRECTORY_SEPARATOR !=='/')
          $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);

      $file = str_replace('//','/' , $file);

      $lang = str_replace($langDir, '', substr($file,0,-4));
      if(strpos($file , 'index')===false && basename($file)!=='objects.php')
        $data[] = $lang;
    }

    return $data;
  }

  /**
   * Rebuild all localization indexes
   */
  public function rebuildAllIndexes()
  {
    $this->rebuildIndex();
    $sub = $this->getSubPackages();

    foreach ($sub as $pack)
     $this->rebuildIndex($pack);
  }

  /**
   * Get language subpackages
   * @param string $language - optional
   * @return array
   */
  public function getSubPackages($lang = false)
  {
    if(!$lang)
      $lang = $this->_indexLanguage;

    $langDir = $this->_appConfig->get('lang_path') . $lang;
    if(!is_dir($langDir))
      return array();

    $files = File::scanFiles($langDir , array('.php'), false , File::Files_Only);
    $data = array();

    foreach ($files as $file)
    {
      // IIS fix
      if(DIRECTORY_SEPARATOR !=='/')
        $file = str_replace(DIRECTORY_SEPARATOR,'/' , $file);

      $file = str_replace('//','/' , $file);

      $lang = str_replace($langDir, '', substr($file,0,-4));
      if(basename($file)!=='objects.php')
          $data[] = $lang;
    }
    return $data;
  }
  /**
   * Get list of sub dictionaries (names only)
   * @return array
   */
  public function getSubDictionaries()
  {
  	$result = $this->getSubPackages(Lang::getDefaultDictionary());
  	if(!empty($result)){
  		foreach ($result as $k=>&$v){
  			$v = str_replace(array('/','\\'), '', $v);
  		}
  	}else{
  		$result = array();
  	}
  	return $result;
  }

  /**
   * Rebuild language index
   * @param string $subPackage - optional
   * @throws Exception
   */
  public function rebuildIndex($subPackage = false)
  {
    $index = array();
    $indexFile = '';
    $indexBase = '';

    if(!$subPackage)
    {
      $indexName = $this->getIndexName();
      $indexBase = $this->_appConfig->get('lang_path') . $this->_indexLanguage.'.php';
    }else
    {
      $indexName = $this->getIndexName($subPackage);
      $indexBase = $this->_appConfig->get('lang_path') . $this->_indexLanguage.'/'.$subPackage.'.php';
    }
    $indexFile = $this->_appConfig->get('lang_path').$indexName;

    if(file_exists($indexFile) && !is_writable($indexFile))
      throw new Exception($this->_lang->get('CANT_WRITE_FS') . ' ' . $indexFile);

    if(!file_exists($indexBase))
      throw new Exception($this->_lang->get('CANT_LOAD') . ' ' . $indexBase);

    $data = include $indexBase;

    if(!is_array($data))
      throw new Exception($this->_lang->get('CANT_LOAD') . ' ' . $indexBase);

    $index = array_keys($data);

    if(!Utils::exportArray($indexFile , $index)){
      throw new ErrorException($this->_lang->get('CANT_WRITE_FS') . ' ' . $indexFile);
    }
  }
  /**
   * Get dictionary index name
   * @param string $dictionary
   * @return string
   */
  public function getIndexName($dictionary='')
  {
  	return $dictionary.'_index.php';
  }
  /**
   * Get dictionary_index
   * @param string $dictionary
   * @return boolean|array
   */
  public function getIndex($dictionary = '')
  {
    $subPackage = basename($dictionary);
    $indexName = $this->getIndexName($subPackage);
    $indexFile = $this->_appConfig->get('lang_path') . $indexName;

    if(!file_exists($indexFile))
      return false;

    $data = include $indexFile;

    if(!is_array($data))
      return false;

    return $data;
  }

  /**
   * Update index content
   * @param array $data
   * @param string $dictionary - optional
   * @throws ErrorException
   */
  public function updateIndex($data , $dictionary)
  {
    $subPackage = basename($dictionary);
    $indexName = $this->getIndexName($subPackage);
    $indexFile = $this->_appConfig->get('lang_path') . $indexName;

    if(!Utils::exportArray($indexFile , $data)){
        throw new ErrorException($this->_lang->get('CANT_WRITE_FS') . ' ' . $indexFile);
    }
  }

  /**
   * Get localization config
   * @param string $dictionary
   * @return array
   */
  public function getLocalistaion($dictionary)
  {
    $dFile = $this->_appConfig->get('lang_path') . $dictionary . '.php';
    $dictionaryData = array();

    if(file_exists($dFile)){
      $dictionaryData = include $dFile;
      if(!is_array($dictionaryData))
        $dictionaryData = array();
    }

    if(strpos($dictionary , '/')!==false)
      $index = $this->getIndex($dictionary);
    else
      $index = $this->getIndex();

    if(!is_array($index))
      return array();

    $keys = array_keys($dictionaryData);
    $newKeys = array_diff($keys, $index);
    $result = array();

    foreach ($index as $dKey)
    {
      $value ='';
      $sync = true;
      if(isset($dictionaryData[$dKey]))
        $value = $dictionaryData[$dKey];
      else
        $sync = false;

      $result[] = array('id'=>$dKey,'key'=>$dKey , 'title'=>$value ,'sync'=>$sync);
    }

    if(!empty($newKeys))
    {
      foreach ($newKeys as $key){
        $result[] = array('id'=>$key,'key'=>$key ,'title'=>$dictionaryData[$key] ,'sync'=>true);
      }
    }
    return $result;
  }

  /**
   * Add key to localization index
   * @param string $key
   * @param string $dictionary
   */
  public function addToIndex($key , $dictionary = '')
  {
    $index = $this->getIndex($dictionary);
    if(!in_array($key, $index , true))
      $index[] = $key;

    $this->updateIndex($index, $dictionary);
  }

  /**
   * Remove key from localization index
   * @param string $key
   * @param string $dictionary
   */
  public function removeFromIndex($key , $dictionary = '')
  {
    $index = $this->getIndex($dictionary);
    if(!in_array($key, $index , true))
       return ;

    foreach ($index as $k=>$v)
      if($v===$key)
        unset($index[$k]);

    $this->updateIndex($index, $dictionary);
  }

  /**
   * Add dictionary record
   * @param string $dictionary
   * @param string $key
   * @param array $langs
   * @throws Exception
   */
  public function addRecord($dictionary , $key , array $langs)
  {
     $isSub = false;

     if(strpos($dictionary, '/')!==false)
     {
       $tmp = explode('/', $dictionary);
       $dictionaryName = $tmp[1];
       $isSub = true;
     }

     if($isSub)
       $index = $this->getIndex($dictionary);
     else
       $index = $this->getIndex();

     // add index for dictionary key
     if(!in_array($key, $index , true))
     {
       if($isSub)
          $this->addToIndex($key , $dictionary);
       else
          $this->addToIndex($key);
     }

     $mainLangs = $this->getLangs(true);

     if(!$isSub)
     {
       // check write permissions
       foreach ($langs as $langName => $value)
       {
         $langFile = $this->_langsPath . $langName .'.php';
         if(!$this->checkCanEdit($langFile)){
            throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
         }
       }


       foreach ($langs as $langName => $value)
       {
         $langFile = $this->_langsPath . $langName .'.php';
         $langData = include $langFile;
         $langData[$key] = $value;
         if(!Utils::exportArray($langFile, $langData)){
           throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
         }
       }

     }
     else
     {
       // check write permissions
       foreach ($langs as $langName => $value)
       {
           $langFile = $this->_langsPath . $langName .'/'.$dictionaryName.'.php';
           if(!$this->checkCanEdit($langFile)){
               throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
           }
       }

       foreach ($langs as $langName => $value)
       {
           $langFile = $this->_langsPath . $langName .'/'.$dictionaryName.'.php';
           $langData = include $langFile;
           $langData[$key] = $value;
           if(!Utils::exportArray($langFile, $langData)){
               throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
           }
       }
     }
  }
  /**
   * Check if file exists and writable
   * @param string $file
   * @return boolean
   */
  protected function checkCanEdit($file)
  {
    if(file_exists($file) && is_writable($file))
      return true;
    else
      return false;
  }
  /**
   * Remove key from localizations
   * @param string $dictionary
   * @param string $key
   * @throws Exception
   */
  public function removeRecord($dictionary , $key)
  {
    $isSub = false;

    if(strpos($dictionary, '/')!==false)
    {
        $tmp = explode('/', $dictionary);
        $dictionaryName = $tmp[1];
        $isSub = true;
    }

    if($isSub)
        $this->removeFromIndex($key , $dictionary);
    else
      $this->removeFromIndex($key);

    $mainLangs = $this->getLangs(true);

    if(!$isSub)
    {
        // check write permissions
        foreach ($mainLangs as $langName)
        {
            $langFile = $this->_langsPath . $langName .'.php';
            if(!$this->checkCanEdit($langFile)){
                throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
            }
        }

        foreach ($mainLangs as $langName)
        {
            $langFile = $this->_langsPath . $langName .'.php';
            $langData = include $langFile;
            unset($langData[$key]);
            if(!Utils::exportArray($langFile, $langData)){
                throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
            }
        }
    }
    else
    {
        // check write permissions
        foreach ($mainLangs as $langName)
        {
            $langFile = $this->_langsPath . $langName .'/'.$dictionaryName.'.php';
            if(!$this->checkCanEdit($langFile)){
                throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
            }
        }

        foreach ($mainLangs as $langName)
        {
            $langFile = $this->_langsPath . $langName .'/'.$dictionaryName.'.php';
            $langData = include $langFile;
            unset($langData[$key]);
            if(!Utils::exportArray($langFile, $langData)){
                throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
            }
        }
    }
  }
  /**
   * Update localization records
   * @param string $dictionary
   * @param array $data
   * @throws Exception
   */
  public function updateRecords($dictionary , $data)
  {
     $langFile = $this->_langsPath . $dictionary . '.php';
     if(!$this->checkCanEdit($langFile)){
         throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
     }
     $langData = include $langFile;

     foreach ($data as $k=>$rec)
       $langData[$rec['id']] = $rec['title'];

     if(!Utils::exportArray($langFile, $langData)){
         throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
     }
  }
  /**
   * Check if dictionary exists (only sub dictionaies not languages)
   * @param string $name
   * @return boolean
   */
  public function dictionaryExists($name)
  {
      $list = $this->getSubDictionaries();

      if(in_array($name, $list , true))
        return true;

      return false;
  }
  /**
   * Create sub dicionary
   * @throws Exception
   * @param string $name
   */
  public function createDictionary($name)
  {
    $filePath = $this->_langsPath . $this->getIndexName($name);

    if(!Utils::exportArray($filePath, array()))
        throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$filePath);

    $langs = $this->getLangs(true);
    foreach ($langs as $lang){
        $filePath = $this->_langsPath . $lang . '/' . $name . '.php';
        if(!Utils::exportArray($filePath, array()))
            throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$filePath);
    }
  }
}