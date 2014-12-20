<?php
class Ext_Component_Field_System_Searchfield extends Ext_Component_Field
{
	/**
	 * (non-PHPdoc)
	 * @see Ext_Object::__toString()
	 */
	public function __toString()
	{		
		$this->_convertListeners();
		return $this->_config->__toString();
	}
}
