<?php
class Ext_Events_Table extends Ext_Events_Panel
{
	static protected $_viewEventOptions = array(
		'cmp'=>'Ext.view.View',
		'e'=>'Ext.EventObject',
		'eOpts'=>'Object'
	);
	
	static protected $_viewRecordItemIndexEventOptions = array(
		'cmp'=>'Ext.view.View',
		'record'=>'Ext.data.Model',
		'item'=>'HTMLElement',
		'index'=>'Number',
		'e'=>'Ext.EventObject',
		'eOpts'=>'Object'
	);
	
	static protected $_selectionRecordIndexOptions = array(
		'cmp'=>'Ext.selection.RowModel',
		'record'=>'Ext.data.Model', 
		'index'=>'Number', 
		'eOpts'=>'Object'
	);
	
	static protected $_cellClickOptions = array(
		'cmp'=>'Ext.view.Table',
		'td'=>'HTMLElement',
		'cellIndex'=>'Number',
		'record'=>'Ext.data.Model',
		'tr'=>'HTMLElement',
		'rowIndex'=>'Number',
		'e'=>'Ext.EventObject',
		'eOpts'=>'Object'
	);

	static protected $_headerOptions = array(
		'ct'=>'Ext.grid.header.Container',
		'column'=>'Ext.grid.column.Column',
		'e'=>'Ext.EventObject',
		't'=>'HTMLElement',
		'eOpts'=>'Object'
	);

	static protected $_rowEventOptions = array(
		'cmp'=>'Ext.view.Table',
		'record'=>'Ext.data.Model',
		'tr'=>'HTMLElement',
		'rowIndex'=>'Number',
		'e'=>'Ext.EventObject',
		'eOpts'=>'Object'
	);

	public $beforecellclick;
	public $beforecellcontextmenu;
	public $beforecelldblclick;
	public $beforecellkeydown;
	public $beforecellmousedown;
	public $beforecellmouseup;

	public $beforecontainerclick;
	public $beforecontainercontextmenu;
	public $beforecontainerdblclick;
	public $beforecontainerkeydown;
	public $beforecontainerkeypress;
	public $beforecontainerkeyup;
	public $beforecontainermousedown;
	public $beforecontainermouseout;
	public $beforecontainermouseover;
	public $beforecontainermouseup;

	public $beforedeselect;

	public $beforeitemclick;
	public $beforeitemcontextmenu;
	public $beforeitemdblclick;
	public $beforeitemkeydown;
	public $beforeitemkeypress;
	public $beforeitemkeyup;
	public $beforeitemmousedown;
	public $beforeitemmouseenter;
	public $beforeitemmouseleave;
	public $beforeitemmouseup;
	
	public $beforeselect;

	public $cellclick;
	public $cellcontextmenu;
	public $celldblclick;
	public $cellkeydown;
	public $cellmousedown;
	public $cellmouseup;

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

	public $columnschanged = array(
		'ct'=>'Ext.grid.header.Container',
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
	public $containerkeydown;
	public $containerkeypress;
	public $containerkeyup;
	public $containermousedown;
	public $containermouseout;
	public $containermouseover;
	public $containermouseup;

	public $deselect;

	public $filterchange = array(
		'store'=>'Ext.data.Store',
		'filter'=>'Ext.util.Filter',
		'eOpts'=>'Object'
	);
	public $groupchange = array(
		'store'=>'Ext.data.Store',
		'grouper'=>'Ext.util.Grouper',
		'eOpts'=>'Object'
	);

	public $headerclick;
	public $headercontextmenu;
	public $headertriggerclick;

	public $itemclick;
	public $itemcontextmenu;
	public $itemdblclick;
	public $itemkeydown;
	public $itemkeypress;
	public $itemkeyup;
	public $itemmousedown;
	public $itemmouseenter;
	public $itemmouseleave;
	public $itemmouseup;

	public $rowclick;
	public $rowcontextmenu;
	public $rowdblclick;
	public $rowkeydown;
	public $rowmousedown;
	public $rowmouseup;

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

		$this->beforecellclick = static::$_cellClickOptions;
		$this->beforecellcontextmenu = static::$_cellClickOptions;
		$this->beforecelldblclick = static::$_cellClickOptions;
		$this->beforecellkeydown = static::$_cellClickOptions;
		$this->beforecellmousedown = static::$_cellClickOptions;
		$this->beforecellmouseup = static::$_cellClickOptions;

		$this->beforecontainerclick = static::$_viewEventOptions;
		$this->beforecontainerclick = static::$_viewEventOptions;
		$this->beforecontainercontextmenu = static::$_viewEventOptions;
		$this->beforecontainerdblclick = static::$_viewEventOptions;
		$this->beforecontainerkeydown = static::$_viewEventOptions;
		$this->beforecontainerkeypress = static::$_viewEventOptions;
		$this->beforecontainerkeyup = static::$_viewEventOptions;
		$this->beforecontainermousedown = static::$_viewEventOptions;
		$this->beforecontainermouseout = static::$_viewEventOptions;
		$this->beforecontainermouseover = static::$_viewEventOptions;
		$this->beforecontainermouseup = static::$_viewEventOptions;

		$this->beforedeselect = static::$_selectionRecordIndexOptions;

		$this->beforeitemclick = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemcontextmenu = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemdblclick = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemkeydown = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemkeypress = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemkeyup = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemmousedown = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemmouseenter = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemmouseleave = static::$_viewRecordItemIndexEventOptions;
		$this->beforeitemmouseup = static::$_viewRecordItemIndexEventOptions;

		$this->beforeselect = static::$_selectionRecordIndexOptions;

		$this->cellclick = static::$_cellClickOptions;
		$this->cellcontextmenu = static::$_cellClickOptions;
		$this->celldblclick = static::$_cellClickOptions;
		$this->cellkeydown = static::$_cellClickOptions;
		$this->cellmousedown = static::$_cellClickOptions;
		$this->cellmouseup = static::$_cellClickOptions;

		$this->containerclick = static::$_viewEventOptions;
		$this->containercontextmenu = static::$_viewEventOptions;
		$this->containerdblclick = static::$_viewEventOptions;
		$this->containerkeydown = static::$_viewEventOptions;
		$this->containerkeypress = static::$_viewEventOptions;
		$this->containerkeyup = static::$_viewEventOptions;
		$this->containermousedown = static::$_viewEventOptions;
		$this->containermouseout = static::$_viewEventOptions;
		$this->containermouseover = static::$_viewEventOptions;
		$this->containermouseup = static::$_viewEventOptions;

		$this->deselect = static::$_selectionRecordIndexOptions;

		$this->headerclick = static::$_headerOptions;
		$this->headercontextmenu = static::$_headerOptions;
		$this->headertriggerclick = static::$_headerOptions;

		$this->itemclick = static::$_viewRecordItemIndexEventOptions;
		$this->itemcontextmenu = static::$_viewRecordItemIndexEventOptions;
		$this->itemdblclick = static::$_viewRecordItemIndexEventOptions;
		$this->itemkeydown = static::$_viewRecordItemIndexEventOptions;
		$this->itemkeypress = static::$_viewRecordItemIndexEventOptions;
		$this->itemkeyup = static::$_viewRecordItemIndexEventOptions;
		$this->itemmousedown = static::$_viewRecordItemIndexEventOptions;
		$this->itemmouseenter = static::$_viewRecordItemIndexEventOptions;
		$this->itemmouseleave = static::$_viewRecordItemIndexEventOptions;
		$this->itemmouseup = static::$_viewRecordItemIndexEventOptions;

		$this->rowclick = static::$_rowEventOptions;
		$this->rowcontextmenu = static::$_rowEventOptions;
		$this->rowdblclick = static::$_rowEventOptions;
		$this->rowkeydown = static::$_rowEventOptions;
		$this->rowmousedown = static::$_rowEventOptions;
		$this->rowmouseup = static::$_rowEventOptions;

		$this->select = static::$_selectionRecordIndexOptions;
	}
}