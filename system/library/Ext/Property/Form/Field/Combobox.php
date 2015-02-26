<?php
class Ext_Property_Form_Field_Combobox extends Ext_Property_Form_Field_Picker
{	
	public $valueField = self::String;
	public $allQuery = self::String;
	public $autoSelect = self::Boolean;
	public $defaultListConfig = self::Object;
	public $delimiter  = self::String;
	public $displayField  = self::String;
	public $fieldSubTpl =self::Object;
	public $forceSelection = self::Boolean;
	public $growToLongestValue = self::Boolean;
	public $hiddenName  = self::String;
	public $listConfig = self::Object;
	public $minChars = self::Number;
	public $multiSelect = self::Boolean;
	public $pageSize = self::Number;
	public $queryCaching = self::Boolean;
	public $queryDelay = self::Number;
	public $queryMode = self::String;
	public $queryParam = self::String;
	public $selectOnTab = self::Boolean;
	public $store  = self::Object;
	public $transform  = self::String;
	public $triggerAction  = self::String;
	public $triggerCls  = self::String;
	public $typeAhead = self::Boolean;
	public $typeAheadDelay = self::Number;
	public $valueNotFoundText  = self::String;
	
	static public $extend = 'Ext.form.field.ComboBox';
	static public $xtype = 'combobox';
}