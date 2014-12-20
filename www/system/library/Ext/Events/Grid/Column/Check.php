<?php
class Ext_Events_Grid_Column_Check extends Ext_Events_Grid_Column{

    public $beforecheckchange= array(
    	'this'=>'Ext.ux.CheckColumn',
    	'rowIndex'=>'Number',
        'checked' => 'Boolean',
    	'eOpts'=>'Object'
    );

    public $checkchange = array(
        'this'=>'Ext.ux.CheckColumn',
        'rowIndex'=>'Number',
        'checked' => 'Boolean',
        'eOpts'=>'Object'
    );
}