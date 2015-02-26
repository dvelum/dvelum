<?php
/**
 * Field Event abstract (class name based on sencha architecture)
 * @author Kirill A Egorov
 */
abstract class Ext_Events_Form_Field_Field extends Ext_Events_Form_Field_Base
{
	public $change = array( 
		 'field'=>'Ext.form.field.Field', 
		 'newValue'=>'Object', 
		 'oldValue'=>'Object',  
		 'eOpts'=>'Object', 
	);
	public $dirtychange = array( 
		'field'=>'Ext.form.field.Field', 
		'isDirty'=>'Boolean', 
		'eOpts'=>'Object', 
	);
	public $validitychange = array( 
		'field'=>'Ext.form.field.Field',  
		'isValid'=>'Boolean', 
		'eOpts'=>'Object', 
	);
}