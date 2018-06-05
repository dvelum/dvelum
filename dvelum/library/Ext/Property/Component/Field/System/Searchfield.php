<?php
class Ext_Property_Component_Field_System_Searchfield extends Ext_Property
{
	public $store = self::Object;
	public $fieldNames = self::Object;
	public $local = self::Boolean;
	public $width = self::Numeric;
	public $hideLabel = self::Boolean;
	public $searchParam = self::String;
	public $fieldLabel = self::String;
	public $minChars = self::Numeric;

	static public $extend = 'SearchPanel';
	static public $xtype = 'searchpanel';
}