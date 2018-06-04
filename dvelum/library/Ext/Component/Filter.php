<?php

class Ext_Component_Filter extends Ext_Object
{
    /**
     * @var Ext_Object $_viewObject
     */
    protected $_viewObject;

    protected function _initDefaultProperties()
    {
        $this->_viewObject = Ext_Factory::object('Form_Field_Text');
    }

    /**
     * Get visualisation object
     * @return Ext_Object
     * @deprecated
     */
    public function getViewObject()
    {
        return $this->getObject();
    }

    /**
     * Get visualisation object
     * @return Ext_Object
     */
    public function getObject()
    {
        return $this->_viewObject;
    }

    /**
     * Set visualisation object
     * @param Ext_Object $object
     */
    public function setViewObject(Ext_Object $object)
    {
        $this->_viewObject = $object;
    }

    public function __toString()
    {
        $local = false;
        $field = '';
        $store = '';
        $autoFilter = '';


        if ($this->_config->isValidProperty('local') && $this->_config->local) {
            $local = true;
        }

        if ($this->_config->isValidProperty('storeField') && strlen($this->_config->storeField)) {
            $field = $this->_config->storeField;
        }

        if ($this->_config->isValidProperty('store') && strlen($this->_config->store)) {
            $store = $this->_config->store;
        }

        if ($this->_config->isValidProperty('autoFilter') && intval($this->_config->autoFilter)) {
            $autoFilter = true;
        }

        $object = $this->getObject();

        $ident = "\n\t\t\t";

        if ($this->_viewObject->isValidProperty('triggers')) {
            $triggers = $this->_viewObject->triggers;
            if (is_string($triggers) && empty($triggers)) {
                $this->_viewObject->triggers = '{
                            clear: {
                                cls: "x-form-clear-trigger",
                                tooltip:appLang.RESET,
                                handler:function(field){
                                    field.reset();
                                }
                            }
                        }';
            }
        }

        if (strlen($store) && strlen($field) && $autoFilter) {
            $listener = 'function(fld){' . $ident . ' var store = ' . $store . ';';


            if ($local) {
                $listener .= $ident . 'store.filter("' . $field . '" , fld.getValue());';
            } else {
                if ($this->_viewObject->isValidProperty('multiSelect') && $this->_viewObject->multiSelect) {
                    $listener .= $ident . ' store.proxy.setExtraParam("filter[' . $field . '][]" , fld.getValue());';
                } else {
                    $listener .= $ident . ' store.proxy.setExtraParam("filter[' . $field . ']" , fld.getValue());';
                }
                $listener .= $ident . 'if(Ext.isEmpty(store.isBufferedStore)){';
                $listener .= $ident . ' store.loadPage(1);';
                $listener .= $ident . '}else{';
                $listener .= $ident . ' store.load();';
                $listener .= $ident . '}';
            }

            $listener .= "\n\t" . '}';

            if ($this->_viewObject->getClass() == 'Form_Field_Combobox') {
                $object->addListener('select', $listener);
            } else {
                $object->addListener('change', $listener);
            }
        }

        // copy listeners
        $listeners = $this->getListeners();
        if (!empty($listeners)) {
            foreach ($listeners as $name => $code) {
                $object->addListener($name, $code);
            }
        }

        return $object->__toString();
    }

    /**
     * Get object state for smart export
     */
    public function getState()
    {
        $state = parent::getState();
        $state['viewObject'] = [
            'class' => $this->getViewObject()->getClass(),
            'state' => $this->getViewObject()->getState()
        ];
        return $state;
    }

    /**
     * Set object state
     * @param $state
     */
    public function setState(array $state)
    {
        parent::setState($state);
        $viewObject = Ext_Factory::object($state['viewObject']['class']);
        $viewObject->setState($state['viewObject']['state']);
        $this->setViewObject($viewObject);
    }
}