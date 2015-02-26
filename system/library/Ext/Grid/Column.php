<?php
class Ext_Grid_Column extends Ext_Object
{
	/**
	 * (non-PHPdoc)
	 * @see Ext_Object::__toString()
	 */
	public function __toString()
	{
		$this->_convertListeners();
		
		if(strlen($this->_config->renderer) )
		{
			if(class_exists($this->_config->renderer)){
				$obj = new $this->_config->renderer();
				$this->_config->renderer = $obj->__toString();
			}else{
				$this->_config->renderer = '';
			}	
		}
		
		if(strlen($this->_config->summaryRenderer) )
		{
			if(class_exists($this->_config->summaryRenderer))
			{
				$obj = new $this->_config->summaryRenderer();
				$this->_config->summaryRenderer = $obj->__toString();	
			}else{
				$this->_config->summaryRenderer = '';
			}
		}
		return $this->_config->__toString();
	}
}