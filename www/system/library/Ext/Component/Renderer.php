<?php
abstract class Ext_Component_Renderer
{
	/**
	 * Renderer for grid column
	 * Example:  
	 * 1) 'someJSFunction'
	 * 2) 'function(value, metaData, record, rowIndex, colIndex, store ,view){return value};'
	 */
	abstract public function __toString();
}