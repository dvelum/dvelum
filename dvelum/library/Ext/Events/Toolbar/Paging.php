<?php
class Ext_Events_Toolbar_Paging extends Ext_Events_Toolbar
{
	public $beforechange = array( 
		 'cmp'=>'Ext.toolbar.Paging',
		 'page'=>'Number', 
		 'eOpts'=>'Object' 
	);
	
	public $change = array( 
		'cmp'=>'Ext.toolbar.Paging',
		'pageData'=>'Object', 
		'eOpts'=>'Object' 
	);
}
