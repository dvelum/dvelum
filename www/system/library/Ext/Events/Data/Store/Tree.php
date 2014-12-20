<?php
class Ext_Events_Data_Store_Tree extends Ext_Events_Data_Abstractstore
{
	public $append = array(
		'store' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'index'=>'Number',
		'eOpts'=>'Object'
	);
	
	public $beforeappend = array(
		'store' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $beforecollapse = array(
		'store' =>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $beforeexpand = array(
		'store' =>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $beforeinsert = array(
		'store' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'refNode'=>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $beforemove = array(
		'store' =>'Ext.data.NodeInterface',
		'oldParent'=>'Ext.data.NodeInterface',
		'newParent'=>'Ext.data.NodeInterface',
		'index'=>'Number',
		'eOpts'=>'Object'
	);
	
	public $beforeremove = array(
		'store' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'isMove'=>'Boolean',
		'eOpts'=>'Object'
	);
	
	public $collapse = array(
		'store' =>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $expand = array(
		'store' =>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $insert = array(
		'store' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'refNode'=>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $load = array(
		'store' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'records'=>'Ext.data.Model[]',
		'successful'=>'Boolean',
		'eOpts'=>'Object'
	);
	
	public $move = array(
		'store' =>'Ext.data.NodeInterface',
		'oldParent'=>'Ext.data.NodeInterface',
		'newParent'=>'Ext.data.NodeInterface',
		'index'=>'Number',
		'eOpts'=>'Object'
	);
	
	public $remove = array(
		'store' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'isMove'=>'Boolean',
		'eOpts'=>'Object'
	);
	
	public $rootchange = array(
		'root'=>'Ext.data.Model',	
		'eOpts'=>'Object'
	);
	
	public $sort = array(
		'store' =>'Ext.data.NodeInterface',
		'childNodes' =>'Ext.data.NodeInterface[]',
		'eOpts'=>'Object'
	);
}