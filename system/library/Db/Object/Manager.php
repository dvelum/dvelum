<?php
/**
 * Db_Object Manager class
 * @package Db
 * @subpackage Db_Object
 * @author Kirill A Egorov kirill.a.egorov@gmail.com 
 * @copyright Copyright (C) 2011-2012  Kirill A Egorov, 
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 */
class Db_Object_Manager
{
	static protected $_objects = null;
	/**
	 * External objects expert
	 * @var Externals_Expert
	 */
	static protected $_externalsExpert = false;
	
    /**
     * Get list of registered objects (names only)
     * @return array
     */
    public function getRegisteredObjects()
    {
    	if(is_null(self::$_objects))
    	{
    		 $paths = File::scanFiles(Db_Object_Config::getConfigPath() , array('.php'), false, File::Files_Only);
    		 self::$_objects = array();
    		 if(!empty($paths))
    		 	foreach ($paths as $path)
    		 		self::$_objects[] = substr(basename($path), 0, -4);		
    		 
    		 /*
    		  * Scan for External objects 
    		  */
    		 if(self::$_externalsExpert)
    		 {
    		 	$objects =  self::$_externalsExpert->getObjects();
    		 	if(!empty($objects))
    		 		self::$_objects = array_merge(self::$_objects , array_keys($objects));
    		 }	 
    	}  
    	return self::$_objects;
    }
    
    /**
     * Check if object exists
     * @param string $name
     * @return boolean
     */
    public function objectExists($name)
    {
    	$list = $this->getRegisteredObjects();
    	return in_array(strtolower($name), $list , true);
    }
    
    /**
     * Set default externals experts
     * @param Externals_Expert $expert
     */
    static public function setExternalsExpert(Externals_Expert $expert)
    {
    	self::$_externalsExpert	 = $expert;
    }
    
    /**
     * Get external Objects expert
     * @return Externals_Expert | false
     */
    public function getExternalsExpert()
    {
    	return self::$_externalsExpert;
    }
}