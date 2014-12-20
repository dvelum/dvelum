<?php
class Ext_Component_Field_System_Dictionary extends Ext_Component_Field
{
	protected function _initDefaultProperties()
	{
		$this->_config->displayField = 'title';
		$this->_config->valueField = 'id';
		$this->_config->forceSelection = true;
		$this->_config->triggerAction = 'all';
		$this->_config->queryMode = 'local';
		$this->_config->showAll = false;
	}

	public function __toString()
	{
		$this->_convertListeners();
		$combo = Ext_Factory::object('Form_Field_Combobox');
		$combo->setName($this->getName());
		Ext_Factory::copyProperties($this, $combo);

		if($this->isValidProperty('dictionary') && strlen($this->dictionary))
		{
			$dM = new Dictionary_Manager();
			if($dM->isValidDictionary($this->dictionary))
			{
			    $allowBlank = false;
			    if($this->_config->allowBlank && !$this->_config->showAll){
			    	$allowBlank = true;
			    }

				if($this->_config->isValidProperty('showAll') && !empty($this->_config->showAllText)){
			    	$allText = $this->_config->showAllText;
			    }else{
			        $allText = false;
			    }			    
				$data = Dictionary::getInstance($this->dictionary)->__toJs($this->_config->showAll , $allowBlank , $allText);

				if(strlen($data))
				{
					$combo->store = 'Ext.create("Ext.data.Store",{
					        model:"app.comboStringModel",
					        data: '.$data.'
						 })';
				}
			}
		}
		return $combo->getConfig()->__toString();
	}
}