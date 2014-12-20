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
		'tree'=>'Ext.data.NodeInterface',
		'node' =>'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $beforeitemcollapse = array(
		'tree'=>'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $beforeitemexpand = array(
		'tree'=>'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $beforeiteminsert = array(
		'tree'=>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'refNode'=>'Ext.data.NodeInterface',
		'eOpts' => 'Object'		
	);
	
	public $beforeitemmove = array(
		'tree'=>'Ext.data.NodeInterface',
		'oldParent'=>'Ext.data.NodeInterface',
		'newParent'=>'Ext.data.NodeInterface',
		'index' =>'Number',
		'eOpts' => 'Object'
	);
	
	public $beforeitemremove = array(
		'tree'=>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'isMove'=>'Boolean',
		'eOpts'=>'Object'
	);
	
	public $beforeload = array(
		'store' => 'Ext.data.Store',
		'operation' => 'Ext.data.Operation',
		'eOpts' => 'Object'
	);
	
	public $checkchange = array(
		'node' => 'Ext.data.Nodeinterface',
		'checked' => 'Boolean',
		'eOpts' => 'Object'		
	);
	
	public $itemappend = array(
		'tree' => 'Ext.data.NodeInterface',
		'node' => 'Ext.data.NodeInterface',
		'index' => 'Number',
		'eOpts' => 'Object'
	);
	
	public $itemcollapse = array(
		'tree' => 'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $itemexpand = array(
		'tree' => 'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $iteminsert = array(
		'tree'=>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'refNode'=>'Ext.data.NodeInterface',
		'eOpts' => 'Object'
	);
	
	public $itemmove = array(
		'tree'=>'Ext.data.NodeInterface',
		'oldParent'=>'Ext.data.NodeInterface',
		'newParent'=>'Ext.data.NodeInterface',
		'index' => 'Number',
		'eOpts' => 'Object'
	);
	
	public $itemremove = array(
		'tree'=>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'isMove'=>'Boolean',
		'eOpts' => 'Object'
	);
	
	public $load = array(
		'store'=>'Ext.data.Store',
		'records'=>'Ext.data.Model[]',
		'successful'=>'Boolean',
		'eOpts'=>'Object'
	);
}