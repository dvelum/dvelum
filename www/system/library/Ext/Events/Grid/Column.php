<?php
class Ext_Events_Grid_Column extends Ext_Events_Container{
	
	public $columnhide = array(
		  'ct'=>'Ext.grid.header.Container', 
		  'column'=>'Ext.grid.column.Column', 
		  'eOpts'=>'Object'
	 );

	 public $columnmove = array( 
	 	 'ct'=>'Ext.grid.header.Container', 
	 	 'column'=>'Ext.grid.column.Column', 
	 	 'fromIdx'=>'Number', 
	 	 'toIdx'=>'Number', 
	 	 'eOpts'=>'Object' 
	 );

	 public $columnresize = array( 
	 	 'ct'=>'Ext.grid.header.Container', 
	 	 'column'=>'Ext.grid.column.Column', 
	 	 'width'=>'Number', 
	 	 'eOpts'=>'Object'
	 );

	 public $columnshow = array( 
	 	 'ct'=>'Ext.grid.header.Container', 
	 	 'column'=>'Ext.grid.column.Column', 
	 	 'eOpts'=>'Object' 
	 );

	 public $headerclick = array( 
	 	 'ct'=>'Ext.grid.header.Container', 
	 	 'column'=>'Ext.grid.column.Column', 
	 	 'e'=>'Ext.EventObject', 
	 	 't'=>'HTMLElement', 
	 	 'eOpts'=>'Object' 
	 );

	 public $headertriggerclick = array( 
	 	 'ct'=>'Ext.grid.header.Container', 
	 	 'column'=>'Ext.grid.column.Column', 
	 	 'e'=>'Ext.EventObject', 
	 	 't'=>'HTMLElement', 
	 	 'eOpts'=>'Object' 
	 );

	 public $menucreate = array( 
	 	 'ct'=>'Ext.grid.header.Container', 
	 	 'menu'=>'Ext.menu.Menu', 
	 	 'eOpts'=>'Object'
	 );

	 public $sortchange = array( 
	 	 'ct'=>'Ext.grid.header.Container', 
	 	 'column'=>'Ext.grid.column.Column', 
	 	 'direction'=>'String', 
	 	 'eOpts'=>'Object'
	 );
}