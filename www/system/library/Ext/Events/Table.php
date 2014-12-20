<?php
class Ext_Events_Table extends Ext_Events_Panel
{
	static protected $_viewEventOptions = array(
		'view'=>'Ext.view.View',
		'e'=>'Ext.EventObject',
		'eOpts'=>'Object'
	);
	
	static protected $_viweRecordItemIndexEventOptions = array(
		'view'=>'Ext.view.View',
		'record'=>'Ext.data.Model',
		'item'=>'HTMLElement',
		'index'=>'Number',
		'e'=>'Ext.EventObject',
		'eOpts'=>'Object'
	);
	
	static protected $_selectionRecordIndexOptions = array(
		'sm'=>'Ext.selection.RowModel',  
		'record'=>'Ext.data.Model', 
		'index'=>'Number', 
		'eOpts'=>'Object'
	);
	
	static protected $_clickOptions = array(	    
	    'view' => 'Ext.view.Table',
	    'td' =>  'HTMLElement',
	    'cellIndex' =>  'Number',
	    'record' =>  'Ext.data.Model',
	    'tr' =>  'HTMLElement',    
	    'rowIndex' =>  'Number',
	    'e' =>  'Ext.EventObject',
	    'eOpts' =>  'Object',
	);
	
	
	public $beforecontainerclick;
	public $beforecontainercontextmenu;
	public $beforecontainerdblclick;
	public $beforecontainermousedown;
	public $beforecontainermouseover;
	public $beforecontainermouseup;
	public $beforedeselect;
	
	public $beforeitemcontextmenu;
	public $beforeitemdblclick;
	public $beforeitemmousedown;
	public $beforeitemmouseenter;
	public $beforeitemmouseleave;
	public $beforeitemmouseup;
	
	public $beforeselect;
	
	
	public $beforeitemclick =array(
		'view'=>'Ext.view.View', 
		'record'=>'Ext.data.Model', 
		'item'=>'HTMLElement',
		'index'=>'Number',
		'e'=>'Ext.EventObject',
		'eOpts'=>'Object'
	);

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
	
	public $containerclick;
	public $containercontextmenu;
	public $containerdblclick;
	public $containermouseout;
	public $containermouseover;
	public $containermouseup;
	public $deselect;
	
	public $itemclick;
	public $itemcontextmenu;
	public $itemdblclick;
	public $itemmousedown;
	public $itemmouseenter;
	public $itemmouseleave;
	public $itemmouseup;
	
	public $reconfigure = array(
		'p'=>'Ext.panel.Table', 
		'eOpts'=>'Object' 
	);
	
	public $scrollerhide = array( 
		'scroller'=>'Ext.grid.Scroller', 
		'orientation'=>'String', 
		'eOpts'=>'Object'
	);
	
	public $scrollershow = array( 
		'scroller'=>'Ext.grid.Scroller', 
		'orientation'=>'String', 
		'eOpts'=>'Object'
	);
	
	public $select;
	
	public $selectionchange = array( 
		'sm'=>'Ext.selection.Model', 
		'selected'=>'Ext.data.Model[]', 
		'eOpts'=>'Object'
	);
	public $sortchange = array( 
		'ct'=>'Ext.grid.header.Container', 
		'column'=>'Ext.grid.column.Column',  
		'direction'=>'String' , 
		'eOpts'=>'Object' 
	);
	public $viewready = array( 
		'panel'=>'Ext.panel.Table', 
		'eOpts'=>'Object' 
	);
	
	public function _initConfig()
	{
		parent::_initConfig();
		
		$this->beforecontainerclick = static::$_viewEventOptions;
		$this->beforecontainerclick = static::$_viewEventOptions;
		$this->beforecontainercontextmenu = static::$_viewEventOptions;
		$this->beforecontainerdblclick = static::$_viewEventOptions;
		$this->beforecontainermousedown = static::$_viewEventOptions;
		$this->beforecontainermouseover = static::$_viewEventOptions;
		$this->beforecontainermouseup = static::$_viewEventOptions;
		$this->beforedeselect = static::$_selectionRecordIndexOptions;
		
		$this->beforeitemcontextmenu = static::$_viweRecordItemIndexEventOptions;
		$this->beforeitemdblclick = static::$_viweRecordItemIndexEventOptions;
		$this->beforeitemmousedown = static::$_viweRecordItemIndexEventOptions;
		$this->beforeitemmouseenter = static::$_viweRecordItemIndexEventOptions;
		$this->beforeitemmouseleave = static::$_viweRecordItemIndexEventOptions;
		$this->beforeitemmouseup = static::$_viweRecordItemIndexEventOptions;
		$this->beforeselect = static::$_selectionRecordIndexOptions;
				
		$this->containerclick = static::$_viewEventOptions;
		$this->containercontextmenu = static::$_viewEventOptions;
		$this->containerdblclick = static::$_viewEventOptions;
		$this->containermouseout = static::$_viewEventOptions;
		$this->containermouseover = static::$_viewEventOptions;
		$this->containermouseup = static::$_viewEventOptions;
		$this->deselect = static::$_selectionRecordIndexOptions;
		
		$this->itemclick = static::$_viweRecordItemIndexEventOptions;
		$this->itemcontextmenu = static::$_viweRecordItemIndexEventOptions;
		$this->itemdblclick = static::$_viweRecordItemIndexEventOptions;
		$this->itemmousedown = static::$_viweRecordItemIndexEventOptions;
		$this->itemmouseenter = static::$_viweRecordItemIndexEventOptions;
		$this->itemmouseleave = static::$_viweRecordItemIndexEventOptions;
		$this->itemmouseup = static::$_viweRecordItemIndexEventOptions;
		
		$this->cellclick = static::$_clickOptions;
		$this->cellcontextmenu = static::$_clickOptions;
		$this->celldblclick = static::$_clickOptions;
		$this->cellkeydown = static::$_clickOptions;
		$this->cellmousedown = static::$_clickOptions;
		$this->cellmouseup = static::$_clickOptions;
		
		$this->select = static::$_selectionRecordIndexOptions;
	}
}