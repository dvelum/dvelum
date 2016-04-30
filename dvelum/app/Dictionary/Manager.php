<?php

class Dictionary_Manager
{
    const CACHE_KEY_LIST = 'Dictionary_Manager_list';
    const CACHE_KEY_DATA_HASH = 'Dictionary_Manager_dataHash';

    /**
     * Base path
     * @var string
     */
    protected $_baseDir ='';

    /**
     * Path to localized dictionary
     * @var string
     */
    protected $_path ='';
    /**
     * @var Cache_Interface
     */
    protected $_cache = false;
    /**
     * @var string
     */
    protected $_language = '';

    static protected $_list = null;

    /**
     * Valid dictionary local cache
     * @var array
     */
    static protected $_validDictionary = array();

    /**
     * @param Config_Abstract $appConfig
     * @param mixed  Cache_Interface | false $cache
     * @throws Exception
     */
    protected function __construct(Config_Abstract $appConfig , $cache = false)
    {
        $this->_appConfig =  $appConfig;
        $this->_language = $appConfig->get('language');
        $this->_path = Config::storage()->getWrite();
        $this->_baseDir = $appConfig->get('dictionary_folder');
        $this->_cache = $cache;

        if($this->_cache && $list = $this->_cache->load(self::CACHE_KEY_LIST))
            self::$_list = $list;
    }

    /**
     * Get list of dictionaries
     * return array
     */
    public function getList()
    {
        if(!is_null(self::$_list))
            return array_keys(self::$_list);

        $paths = Config::storage()->getPaths();

        $list = array();

        foreach($paths as $path)
        {
            if(!file_exists($path . $this->_baseDir . 'index/'))
                continue;

            $files = File::scanFiles($path.$this->_baseDir . 'index/', array('.php'), false, File::Files_Only);

            if(!empty($files)){
                foreach($files as $path){
                    $name = substr(basename($path),0,-4);
                    $list[$name] = $path;
                }
            }
        }

        self::$_list = $list;

        if($this->_cache)
            $this->_cache->save($list, self::CACHE_KEY_LIST);

        return array_keys($list);
    }
    /**
     * Create dictionary
     * @param string $name
     * @param string $language, optional
     * @return boolean
     */
    public function create($name , $language = false)
    {
        if($language == false){
            $language = $this->_language;
        }

        $indexFile = $this->_path . $this->_baseDir . 'index/' . $name . '.php';
        $dictionaryFile =  $this->_path . $this->_baseDir . $language . '/' .  $name . '.php';

        if(!file_exists($dictionaryFile) && Config_File_Array::create($dictionaryFile))
        {
            if(!file_exists($indexFile) && !Config_File_Array::create($indexFile)){
                return false;
            }

            self::$_validDictionary[$name]=true;
            $this->resetCache();
            return true;
        }
        return false;
    }
    /**
     * Rename dictionary
     * @param string $oldName
     * @param string $newName
     * @return boolean
     */
    public function rename($oldName, $newName)
    {
        $dirs = File::scanFiles($this->_path. $this->_baseDir, false, false, File::Dirs_Only);

        foreach($dirs as $path){
            if(file_exists($path . '/' . $oldName . '.php')){
                if(!@rename($path . '/' . $oldName . '.php', $path . '/' . $newName.'.php')){
                    return false;
                }
            }
        }

        if(isset(self::$_validDictionary[$oldName]))
            unset(self::$_validDictionary[$oldName]);

        self::$_validDictionary[$newName]=true;

        $this->resetCache();

        return true;
    }
    /**
     * Check if dictionary exists
     * @param string $name
     * @return boolean
     */
    public function isValidDictionary($name)
    {
        /*
         * Check local cache
         */
        if(isset(self::$_validDictionary[$name]))
            return true;

        if(Config::storage()->exists($this->_baseDir . 'index/' . $name . '.php')) {
            self::$_validDictionary[$name] = true;
            return true;
        }
        return false;
    }
    /**
     * Remove dictionary
     * @param string $name
     * @return boolean
     */
    public function remove($name)
    {
        $dirs = File::scanFiles($this->_path. $this->_baseDir, false, false, File::Dirs_Only);

        foreach($dirs as $path){
            $file = $path . '/' . $name . '.php';
            if(file_exists($file) && is_file($file))
                if(!@unlink($file))
                    return false;
        }

        if(isset(self::$_validDictionary[$name]))
            unset(self::$_validDictionary[$name]);

        $this->resetCache();

        return true;
    }
    /**
     * Reset cache
     */
    public function resetCache()
    {
        if(!$this->_cache)
            return;

        $this->_cache->remove(self::CACHE_KEY_LIST);
        $this->_cache->remove(self::CACHE_KEY_DATA_HASH);
    }
    /**
     * Get data hash (all dictionaries data)
     * Enter description here ...
     */
    public function getDataHash()
    {
        if($this->_cache && $hash = $this->_cache->load(self::CACHE_KEY_DATA_HASH))
            return $hash;

        $s='';
        $list = $this->getList();

        if(!empty($list))
            foreach ($list as $name)
                $s.= $name.':'.Dictionary::factory($name)->__toJs();

        $s = md5($s);

        if($this->_cache)
            $this->_cache->save($s, self::CACHE_KEY_DATA_HASH);

        return $s;
    }

    /**
     * Get Dictionary manager
     * @return Dictionary_Manager
     */
    static public function factory()
    {
        static $manager = false;

        if(!$manager){
            $cfg = Registry::get('main' , 'config');
            $cacheManager = new Cache_Manager();
            $cache = $cacheManager->get('data');
            $manager = new static($cfg , $cache);
        }

        return $manager;
    }


    /**
     * Save changes
     * @param string $name
     * @return boolean
     */
    public function saveChanges($name)
    {
        $dict = Dictionary::factory($name);
        if(!$dict->save())
            return false;

        $this->resetCache();
        $this->rebuildIndex($name);
        $this->mergeLocales($name,$this->_language);
        return true;
    }

    /**
     * Rebuild dictionary index
     * @param string $name
     * @return boolean
     */
    public function rebuildIndex($name)
    {
        $dict = Dictionary::factory($name);
        $index = Config::storage()->get($this->_baseDir . 'index/' . $name . '.php', false, false);

        $index->removeAll();
        $index->setData(array_keys($dict->getData()));
        $index->save();

        return true;
    }

    /**
     * Sync localized versions of dictionaries using base dictionary as a reference list of records
     * @param string $name
     * @param string $baseLocale
     * @return boolean
     */
    public function mergeLocales($name, $baseLocale)
    {
        $baseDict = Config::storage()->get($this->_baseDir . $baseLocale . '/' . $name . '.php', false, false);

        $locManager = new Backend_Localization_Manager($this->_appConfig);

        foreach($locManager->getLangs(true) as $locale)
        {
            if($locale == $baseLocale)
                continue;

            $dict = Config::storage()->get($this->_baseDir . $locale . '/' . $name . '.php', false, false);
            if($dict === false){
                if(!$this->create($name , $locale) || ! $dict=Config::storage()->get($this->_baseDir . $locale . '/' . $name . '.php', false, false)){
                    return false;
                }
            }

            // Add new records from base dictionary and remove redundant records from current
            $mergedData = array_merge(
            // get elements from current dictionary with keys common for arrays
                array_intersect_key($dict->__toArray(), $baseDict->__toArray()),
                // get new records for current dictionary
                array_diff_key($baseDict->__toArray(), $dict->__toArray())
            );

            $dict->removeAll();
            $dict->setData($mergedData);

            $dict->save();
        }

        return true;
    }
}