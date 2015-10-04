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

		$renderer = $this->_config->renderer;

		if(is_string($renderer) && strlen($renderer) )
		{
			if(class_exists($renderer)){
				$obj = new $renderer();
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

	public function getState()
	{
		$state = parent::getState();
		if($this->renderer instanceof Ext_Helper_Grid_Column_Renderer){
			$state['renderer'] = ['type'=> $this->renderer->getType(),'value'=>$this->renderer->getValue()];
			$state['config']['renderer'] = '';
		}
		return $state;
	}

	public function setState(array $state)
	{
		parent::setState($state);
		if(isset($state['renderer']) && !empty($state['renderer'])){
			$renderer = new Ext_Helper_Grid_Column_Renderer();
			$renderer->setType($state['renderer']['type']);
			$renderer->setValue($state['renderer']['value']);
			$this->renderer = $renderer;
		}
	}
}