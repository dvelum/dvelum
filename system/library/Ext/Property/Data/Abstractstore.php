<?php
abstract class Ext_Property_Data_Abstractstore extends Ext_Property{
	
	public $autoLoad = self::Boolean;
	public $autoSync = self::Boolean;
	public $fields = self::Object;
	public $filters = self::Object;
	public $model = self::String;
	public $proxy = self::Object;
	public $storeId = self::String;
}