<?php
class Ext_Events_Grid_Column_Action_Button extends Ext_Events
{
	public $handler = array(
			'grid' => 'Ext.grid.Panel',
			'rowIndex' => 'integer', 
			'colIndex' => 'integer' 
	);
}