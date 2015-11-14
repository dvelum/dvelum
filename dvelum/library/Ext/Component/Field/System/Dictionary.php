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
			$dM = Dictionary_Manager::factory();

			if($dM->isValidDictionary($this->dictionary))
			{
			    $allowBlank = false;

			    if($this->_config->allowBlank && !$this->_config->showAll){
			    	$allowBlank = true;
			    }

				$data = Dictionary::factory($this->dictionary)->__toJs(false , $allowBlank);

				if(($this->_config->isValidProperty('showReset') && $this->_config->showReset) || $this->_config->isValidProperty('showAll') && $this->_config->showAll)
                {
                    if($this->_config->isValidProperty('emptyText') && empty($this->_config->emptyText)){
                        $this->_config->emptyText = '[js:]appLang.ALL';
                    }

                    $combo->triggers = '{
                        clear: {
                            cls: "x-form-clear-trigger",
                            tooltip:appLang.RESET,
                            handler:function(field){
                                field.reset();
                            }
                        }
                    }';
				}

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