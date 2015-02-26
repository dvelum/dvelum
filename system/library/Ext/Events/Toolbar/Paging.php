<?php
class Ext_Events_Toolbar_Paging extends Ext_Events_Toolbar
{
	public $beforechange = array( 
		 'pager'=>'Ext.toolbar.Paging', 
		 'page'=>'Number', 
		 'eOpts'=>'Object' 
	);
	
	public $change = array( 
		'pager'=>'Ext.toolbar.Paging',  
		'pageData'=>'Object', 
		'eOpts'=>'Object' 
	);
}
