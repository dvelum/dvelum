<?php
abstract class Ext_Component_Abstract_Renderer_Dictionary extends Ext_Component_Renderer
{
	protected $_dictionaryData;
	protected $_name;

	public function __construct()
	{
		$this->_dictionaryData = Dictionary::getInstance($this->_name)->getData();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ext_Component_Renderer::__toString()
	 */
	public function __toString()
	{
		return 'function(value, metaData, record, rowIndex, colIndex, store ,view)
		{
			var data = ' . json_encode($this->_dictionaryData) . ';
			if(Ext.isEmpty(data[value])){
				return "";
			}else{
				return data[value];
			}
		}';
	}
}