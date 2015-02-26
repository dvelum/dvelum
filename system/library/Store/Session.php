<?php
/**
 * Session storage
 * @author Kirill A Egorov 2008
 * @package Store
 */
class Store_Session extends Store_Local
{	
    static protected $_instances =  array();
    static protected $_prefix = 'sc_';
    /**
     * @param string $name - optional
     * @return Store_Session
     */
	static public function getInstance($name = "default")
	{
        if(!isset(self::$_instances[$name]))	
           self::$_instances[$name] = new self($name);
        return self::$_instances[$name];   
    }
	/**
	 * (non-PHPdoc)
	 * @see www/library/Store/Store_Local#_storageConnect()
	 */
    protected function _storageConnect()
    {  	
    	@session_start();
	
    	if(!isset($_SESSION[self::$_prefix][$this->_name]))
    		$_SESSION[self::$_prefix][$this->_name] =array();
    	
    	$this->_storage = &$_SESSION[self::$_prefix][$this->_name];
    }
}