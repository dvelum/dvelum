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

		if(isset($this->editor) && $this->editor instanceof Ext_Object){
			/**
			 * @var Ext_Object $object;
			 */
			$object = $this->editor;
			$state['editor'] = [];
			$state['editor']['extClass']= $object->getClass();
			$state['editor']['name'] = $object->getName();
			$state['editor']['state'] = $object->getState();
			$state['config']['editor']='';
		}

		if(!empty($this->filter) && $this->filter instanceof Ext_Grid_Filter){
			$state['filter'] = [
				'class' => get_class($this->filter),
				'name' => $this->filter->getName(),
				'extClass' => $this->filter->getClass(),
				'state' => $this->filter->getState()
			];
            $state['config']['filter'] = '';
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

		if(isset($state['editor']) && !empty($state['editor'])){
			$editor = Ext_Factory::object($state['editor']['extClass']);
			$editor->setName($state['editor']['name']);
			$editor->setState($state['editor']['state']);
			$this->editor = $editor;
		}

        if(isset($state['filter']) && !empty($state['filter']))
        {
            $filter = Ext_Factory::object($state['filter']['extClass']);
            $filter->setName($state['filter']['name']);
            $filter->setState($state['filter']['state']);
            $this->filter = $filter;
        }

	}
}