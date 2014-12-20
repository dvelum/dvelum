<?php
class Db_Query_Condition{
	public $object = '';
	public $field = '';
	public $operator = '';
	public $value = '';
	public $value2 = '';
	
	public function __set($key , $val){
		if(!isset($this->$key))
			throw new Exception('Invalid property name ' . $key);
		$this->$key = $val;	
	}
}