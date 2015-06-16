<?php
/**
 * Field Event abstract (class name based on sencha architecture)
 * @author Kirill A Egorov
 */
abstract class Ext_Events_Form_Field_Field extends Ext_Events_Form_Field_Base
{
	public $dirtychange = array(
		'cmp'=>'Ext.form.field.Field',
		'isDirty'=>'Boolean', 
		'eOpts'=>'Object', 
	);
	public $validitychange = array( 
		'cmp'=>'Ext.form.field.Field',
		'isValid'=>'Boolean', 
		'eOpts'=>'Object', 
	);
}