<?php
class Ext_Code
{
	static protected $_namespace = null;
	static protected $_runNamespace = null;
	
	static public function setRunNamespace($name)
	{
		self::$_runNamespace = $name;
	}
	
	static public function setNamespace($name)
	{
		self::$_namespace = $name;
	}
	
	static public function getNamespace()
	{
		return self::$_namespace;
	}
	
	static public function getRunNamespace()
	{
		return self::$_runNamespace;
	}
	
	static public function appendRunNamespace($name)
	{
		if(!is_null(self::$_runNamespace))
			return self::$_runNamespace . '.' . $name;
		else 
			return $name;	
	}
	
	static public function appendNamespace($name)
	{
		if(!is_null(self::$_namespace))
			return self::$_namespace . '.' . $name;
		else 
			return $name;	
	}
}