<?php
class Ext_Events_Tree extends Ext_Events_Table
{
	
	public $afteritemcollapse = array(
		'node'=>'Ext.data.NodeInterface',
		'index' =>'Number',
		'item' => 'HTMLElement',
		'eOpts' => 'Object'
	);
		
	public $afteritemexpand = array(
		'node'=>'Ext.data.NodeInterface',
		'index' =>'Number',
		'item' => 'HTMLElement',
		'eOpts' => 'Object'
	);
	
	public $beforeitemappend = array(
		'cmp'=>'Ext.data.NodeInterface',
		'node' =>'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $beforeitemcollapse = array(
		'cmp'=>'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $beforeitemexpand = array(
		'cmp'=>'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $beforeiteminsert = array(
		'cmp'=>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'refNode'=>'Ext.data.NodeInterface',
		'eOpts' => 'Object'		
	);
	
	public $beforeitemmove = array(
		'cmp'=>'Ext.data.NodeInterface',
		'oldParent'=>'Ext.data.NodeInterface',
		'newParent'=>'Ext.data.NodeInterface',
		'index' =>'Number',
		'eOpts' => 'Object'
	);
	
	public $beforeitemremove = array(
		'cmp'=>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'isMove'=>'Boolean',
		'eOpts'=>'Object'
	);
	
	public $beforeload = array(
		'store' => 'Ext.data.Store',
		'operation' => 'Ext.data.operation.Operation',
		'eOpts' => 'Object'
	);
	
	public $checkchange = array(
		'node' => 'Ext.data.TreeModel',
		'checked' => 'Boolean',
		'eOpts' => 'Object'		
	);
	
	public $itemappend = array(
		'cmp' => 'Ext.data.NodeInterface',
		'node' => 'Ext.data.NodeInterface',
		'index' => 'Number',
		'eOpts' => 'Object'
	);
	
	public $itemcollapse = array(
		'cmp' => 'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $itemexpand = array(
		'cmp' => 'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $iteminsert = array(
		'cmp'=>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'refNode'=>'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $itemmove = array(
		'cmp'=>'Ext.data.NodeInterface',
		'oldParent'=>'Ext.data.NodeInterface',
		'newParent'=>'Ext.data.NodeInterface',
		'index' => 'Number',
		'eOpts' => 'Object'
	);
	
	public $itemremove = array(
		'cmp'=>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'isMove'=>'Boolean',
        'context'=>'Object',
		'eOpts' => 'Object'
	);
	
	public $load = array(
		'cmp'=>'Ext.data.TreeStore',
		'records'=>'Ext.data.TreeModel[]',
		'successful'=>'Boolean',
        'operation'=>'Ext.data.Operation',
        'node'=>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
}