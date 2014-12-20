<?php

class Ext_Events_Menu_Datepicker extends Ext_Events_Menu
{
	/**
	 * Fires when a date is selected
	 */
	public $select = array(
			'cmp' => 'Ext.picker.Date',
			'date' => 'Date',
			'eOpts' => 'Object'
	);

}