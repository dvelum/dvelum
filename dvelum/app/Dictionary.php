<?php
/**
 * System dictionary class
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2012  Kirill A Egorov,
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 */
class Dictionary
{
    protected static $_instances = array();

    protected function __construct(){}
    protected function __clone(){}

    /**
     * Path to configs
     * @var string
     */
    static protected $_configPath = null;

    /**
     * Dictionary name
     * @var string
     */
    protected $name;

    /**
     * @var Config_Abstract
     */
    protected $_data;

    /**
     * Set config files path
     * @param string $path
     * @return void
     */
    static public function setConfigPath($path)
    {
        self::$_configPath = $path;
    }

    /**
     * Instantiate a dictionary by name
     * @param string $name
     * @return Dictionary
     * @deprecated
     */
    static public function getInstance($name)
    {
        return self::factory($name);
    }

    /**
     * Instantiate a dictionary by name
     * @param string $name
     * @return Dictionary
     */
    static public function factory($name)
    {
        $name = strtolower($name);
        if(!isset(self::$_instances[$name]))
        {
            $obj = new self();
            $obj->name = $name;
            $cfgPath = self::$_configPath . $name . '.php';

            if(!Config::storage()->exists($cfgPath))
                Config::storage()->create($cfgPath);

            $obj->_data = Config::storage()->get($cfgPath, true, false);

            self::$_instances[$name] = $obj;
        }
        return self::$_instances[$name];
    }

    /**
     * Get dictionary name
     * @return array
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Check if the key exists in the dictionary
     * @param string $key
     * @return boolean
     */
    public function isValidKey($key)
    {
        return $this->_data->offsetExists($key);
    }

    /**
     * Get value by key
     * @param string $key
     * @return string
     */
    public function getValue($key)
    {
        return $this->_data->get($key);
    }

    /**
     * Get dictionary data
     * @return array
     */
    public function getData()
    {
        return $this->_data->__toArray();
    }

    /**
     * Add a record
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function addRecord($key , $value)
    {
        return $this->_data->set($key , $value);
    }

    /**
     * Delete record by key
     * @param string $key
     * @return boolean
     */
    public function removeRecord($key)
    {
        return $this->_data->remove($key);
    }

    /**
     * Save dictionary
     * @return boolean
     */
    public function save()
    {
        if(!$this->_data->save())
            return false;
        return true;
    }

    /**
     * Get dictionary as JavaScript code representation
     * @param boolean $addAll - add value 'All' with a blank key,
     * @param boolean $addBlank - add empty value is used in drop-down lists
     * @param string|boolean $allText, optional - text for not selected value
     * @return string
     */
    public function __toJs($addAll = false , $addBlank = false , $allText = false)
    {
        $result = array();

        if($addAll){
            if($allText === false){
                $allText = Lang::lang()->get('ALL');
            }
            $result[] = array('id' => '' , 'title' => $allText);
        }

        if(!$addAll && $addBlank)
            $result[] = array('id' => '' , 'title' => '');

        foreach($this->_data as $k => $v)
            $result[] = array('id' => strval($k) , 'title' => $v);

        return json_encode($result);
    }

    /**
     * Get key for value
     * @param $value
     * @param boolean $i case insensitive
     * @return mixed, false on error
     */
    public function getKeyByValue($value, $i = false)
    {
        foreach($this->_data as $k=>$v)
        {
            if($i){
                $v = strtolower($v);
                $value = strtolower($value);
            }
            if($v === $value){
                return $k;
            }
        }
        return false;
    }

}