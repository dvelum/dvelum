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
     * @var array
     */
    protected $_langsPaths;

    /**
     * @param Config_Abstract $appConfig
     */
    public function __construct(Config_Abstract $appConfig)
    {
        $this->_appConfig =  $appConfig;
        $this->_langsPaths = Lang::storage()->getPaths();
        $this->_lang = Lang::lang();
    }
    /**
     * Get list of system languages
     * @param boolean $onlyMain - optional. Get only global locales without subpackages
     * @return array
     */
    public function getLangs($onlyMain = true)
    {
        $langStorage = Lang::storage();
        $langs = $langStorage->getList(false , !$onlyMain);
        $paths = $langStorage->getPaths();

        $data = array();
        foreach ($langs as $file)
        {
            $file = str_replace($paths,'' , $file);
            if(strpos($file , 'index')===false && basename($file)!=='objects.php')
                $data[] = substr($file,0,-4);
        }
        return array_unique($data);
    }

    /**
     * Rebuild all localization indexes
     */
    public function rebuildAllIndexes()
    {
        $this->rebuildIndex(false);
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

        if($lang)
            $lang = $lang.'/';
        else
            $lang = false;

        $langs = Lang::storage()->getList($lang , false);

        foreach ($langs as $file)
        {
            if(basename($file)!=='objects.php')
                $data[] = substr(basename($file),0,-4);
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
            $indexBaseName =   $this->_indexLanguage.'.php';
        }else
        {
            $indexName = $this->getIndexName($subPackage);
            $indexBaseName = $this->_indexLanguage.'/'.$subPackage.'.php';
        }

        $indexBase = Lang::storage()->get($indexBaseName);
        if($indexBase  === false)
            throw new Exception($this->_lang->get('CANT_LOAD') . ' ' . $indexBaseName);

        $baseKeys = array_keys($indexBase->__toArray());

        $indexPath =  Lang::storage()->getPath($indexName);
        $writePath =  Lang::storage()->getWrite();
        if(!file_exists($indexPath) && !file_exists($writePath . $indexName)) {
            if(!Utils::exportArray($writePath . $indexFile , array())){
                throw new ErrorException($this->_lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
            }
        }
        $indexConfig = Lang::storage()->get($indexName);
        if($indexConfig === false){
            throw new Exception($this->_lang->get('CANT_LOAD') . ' ' . $indexName);
        }
        $indexConfig->removeAll();
        $indexConfig->setData($baseKeys);
        if(!$indexConfig->save()){
            throw new ErrorException($this->_lang->get('CANT_WRITE_FS') . ' ' . $indexConfig->getName());
        }
    }
    /**
     * Get dictionary index name
     * @param string $dictionary
     * @return string
     */
    public function getIndexName($dictionary='')
    {
        return str_replace('/','_',$dictionary).'_index.php';
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

        $indexFile = Lang::storage()->getPath($indexName);

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

        $writePath =  Lang::storage()->getWrite();

        if(!file_exists($writePath . $indexName)){
            if(!Utils::exportArray($writePath . $indexName , array())){
                throw new ErrorException($this->_lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
            }
        }
        $indexConfig = Lang::storage()->get($indexName);
        $indexConfig->removeAll();
        $indexConfig->setData($data);
        if(!$indexConfig->save()){
            throw new ErrorException($this->_lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
        }
    }

    /**
     * Get localization config
     * @param string $dictionary
     * @return array
     */
    public function getLocalization($dictionary)
    {
        $dictionaryData = Lang::storage()->get($dictionary.'.php')->__toArray();

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
        if (!in_array($key, $index, true)) {
            return;
        }

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

        $writePath = Lang::storage()->getWrite();

        if(!$isSub)
        {
            foreach ($langs as $langName => $value)
            {
                $langFile = $writePath . $langName .'.php';
                $langConfig = Lang::storage()->get($langName .'.php');
                if($langConfig === false){
                    throw new Exception($this->_lang->get('CANT_LOAD').' '.$langName);
                }
                $langConfig->set($key , $value);
                if(!$langConfig->save()){
                    throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
                }
            }

        }
        else
        {
            foreach ($langs as $langName => $value)
            {
                $langFile = $writePath .  $langName .'/'.$dictionaryName.'.php';
                $langConfig = Lang::storage()->get( $langName .'/'.$dictionaryName.'.php');
                if($langConfig === false){
                    throw new Exception($this->_lang->get('CANT_LOAD').' '.$langName .'/'.$dictionaryName);
                }
                $langConfig->set($key , $value);
                if(!$langConfig->save()){
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

        $writePath = Lang::storage()->getWrite();

        if(!$isSub)
        {
            foreach ($mainLangs as $langName)
            {
                $langFile = $writePath . $langName .'.php';
                $langConfig = Lang::storage()->get($langName .'.php');
                if($langConfig === false){
                    throw new Exception($this->_lang->get('CANT_LOAD').' '.$langName);
                }
                $langConfig->remove($key);
                if(!$langConfig->save()){
                    throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$langFile);
                }
            }
        }
        else
        {
            foreach ($mainLangs as $langName)
            {
                $langFile = $writePath .  $langName .'/'.$dictionaryName.'.php';
                $langConfig = Lang::storage()->get( $langName .'/'.$dictionaryName.'.php');
                if($langConfig === false){
                    throw new Exception($this->_lang->get('CANT_LOAD').' '.$langName .'/'.$dictionaryName);
                }
                $langConfig->remove($key);
                if(!$langConfig->save()){
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
        $writePath = Lang::storage()->getWrite() . $dictionary . '.php';

        $langConfig = Lang::storage()->get($dictionary . '.php');
        foreach ($data as $k=>$rec)
            $langConfig->set( $rec['id'] , $rec['title']);

        if(!$langConfig->save()){
            throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$writePath);
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
        $writePath = Lang::storage()->getWrite();
        $indexPath = $writePath . $this->getIndexName($name);

        $indexLocation = dirname($indexPath);

        if(!file_exists($indexLocation) && !@mkdir($indexLocation , 0775 , true))
            throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$indexLocation);

        if(!Utils::exportArray($indexPath, array()))
            throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$indexPath);

        $langs = $this->getLangs(true);

        foreach ($langs as $lang)
        {
            $fileLocation = $writePath . $lang;

            if(!file_exists($fileLocation) && !@mkdir($fileLocation , 0775 , true))
                throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$fileLocation);

            $filePath = $fileLocation . '/' . $name . '.php';

            if(!Utils::exportArray($filePath, array()))
                throw new Exception($this->_lang->get('CANT_WRITE_FS').' '.$filePath);
        }
    }

    /**
     * Create JS lang files
     * @throw Exception
     */
    public function compileLangFiles()
    {
        $jsPath = $this->_appConfig->get('js_lang_path');;
        $langs = $this->getLangs(false);

        foreach ($langs as $lang)
        {
            $name = $lang;
            $dictionary = Lang::storage()->get( $lang .'.php');
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
               throw new Exception($this->_lang->get('CANT_WRITE_FS') . ' ' . $filePath);
        }
    }
}