<?php
class Ext_Property_Component_Filter extends Ext_Property
{
	public $store = self::Object;
	public $storeField = self::String;
	public $local = self::Boolean;
	public $autoFilter = self::Boolean;
	
	static public $extend = 'Ext.form.FieldContainer';
	static public $xtype = 'fieldcontainer';
}
