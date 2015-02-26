<?php
class Ext_Events_Container extends Ext_Events_Component
{
	public $add = array( 
		'container'=>'Ext.container.Container' , 
		'component'=>'Ext.Component', 
		'index'=>'Number', 
		'eOpts'=>'Oject' 
	);

	public $afterlayout = array( 
		'container'=>'Ext.container.Container', 
		'layout','Ext.layout.container.Container', 
		'eOpts'=>'Object' 
	);

	public $beforeadd = array( 
		'container'=>'Ext.container.Container', 
		'component'=>'Ext.Component', 
		'index'=>'Number' , 
		'eOpts'=>'Object'  
	);

	public $beforeremove = array( 
		'container'=>'Ext.container.Container', 
		'component'=>'Ext.Component' , 
		'eOpts'=>'Object'  
	);
	
	public $remove = array( 
		'container'=>'Ext.container.Container', 
		'component'=>'Ext.Component' , 
		'eOpts'=>'Object'
	);
}