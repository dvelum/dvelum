<?php
class Ext_Property_Form_Field_Combobox extends Ext_Property_Form_Field_Picker
{	
	public $store = self::Object;
	public $valueField = self::String;
	public $allQuery = self::String;
	public $anyMatch = self::Boolean;
	public $autoLoadOnValue = self::Boolean;
	public $autoSelect = self::Boolean;
	public $caseSensitive = self::Boolean;
	public $clearFilterOnBlur = self::Boolean;
	public $collapseOnSelect = self::Boolean;
	public $defaultListConfig = self::Object;
	public $delimiter = self::String;
	public $displayField = self::String;
	public $displayTpl = self::Object;
	public $enableRegEx = self::Boolean;
	public $filters = self::Object;
	public $forceSelection = self::Boolean;
	public $growToLongestValue = self::Boolean;
	public $hiddenDataCls = self::String;
	public $hiddenName = self::String;
	public $listConfig = self::Object;
	public $minChars = self::Number;
	public $pageSize = self::Number;
	public $queryCaching = self::Boolean;
	public $queryDelay = self::Number;
	public $queryMode = self::String;
	public $queryParam = self::String;
	public $selectOnTab = self::Boolean;
	public $selection = self::Object;
	public $transform = self::String;
	public $transformInPlace = self::Boolean;
	public $triggerAction = self::String;
	public $triggerCls = self::String;
	public $typeAhead = self::Boolean;
	public $typeAheadDelay = self::Number;
	public $valueNotFoundText = self::String;

	static public $extend = 'Ext.form.field.ComboBox';
	static public $xtype = 'combobox';
}