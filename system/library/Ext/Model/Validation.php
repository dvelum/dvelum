<?php
class Ext_Model_Validation
{
	public $type;
	public $field;
	
	public $list;
	public $matcher;
	public $min;
		
	public function __construct($type , $field , $typeParam = null)
	{
		$this->type = $type;
		$this->field = $field;
		
		if(!is_null($typeParam)){
			
			switch($typeParam){
				case 'email' :  
				case 'presence' :
					break;
				case 'exclusion':
				case 'inclusion' :
					$this->list = $typeParam;
					break;
				case 'format' :	
					$this->matcher = $typeParam;
					break;
				case 'length' :
					$this->min = intval($typeParam);
					break;
			}
		}
	}
}