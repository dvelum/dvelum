<?php
class Ext_Model_Association
{
	public $type;
	public $model;
	public $name;
	
	public function __construct($type , $model , $name)
	{
		$this->type = $type;
		$this->model = $model;
		$this->name = $name;
	}
}