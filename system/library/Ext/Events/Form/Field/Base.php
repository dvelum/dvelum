<?php
/**
 * Bsse Field Event abstract (class name based on sencha architecture)
 * @author Kirill A Egorov
 */
abstract class Ext_Events_Form_Field_Base extends Ext_Events_Component
{
	public $specialkey = array(
		'cmp'=>'Ext.form.field.Base',
		'e'=>'Ext.EventObject',
		'eOpts'=>'Object'
	);
	public $change = array(
		'cmp'=>'Ext.form.field.Field',
		'newValue'=>'Object',
		'oldValue'=>'Object',
		'eOpts'=>'Object'
	);
	public $writetablechange = array(
		'cmp'=>'Ext.form.field.Base',
		'Read'=>'Boolean',
		'eOpts'=>'Object'
	);

}