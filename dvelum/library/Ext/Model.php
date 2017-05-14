<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Ext Model implementation
 * @author Kirill A Egorov
 * @package Ext
 */
class Ext_Model extends Ext_Object
{
	protected $_fields = array();
	protected $_validations = array();
	protected $_associations = array();
	
	/**
	 * Get model Associations
	 * @return array
	 */
	public function getAssociations()
	{
		return $this->_associations;
	}
	/**
	 * Get Model validations (validators)
	 * @return array
	 */
	public function getValidations()
	{
		return $this->_validations;
	}
	
	/**
	 * Remove Association by index
	 * @param string $index
	 */
	public function removeAssociation($index)
	{
		if(isset($this->_associations[$index]))
			unset($this->_associations[$index]);
	}
	/**
	 * Remove validation by index
	 * @param string $index
	 */
	public function removeValidation($index)
	{
		if(isset($this->_validations[$index]))
			unset($this->_validations[$index]);
	}
	

	/**
	 * Add Validation
	 * @param Ext_Model_Validation $validation
	 */
	public function addValidation(Ext_Model_Validation $validation)
	{
		$this->_validations[] = $validation;
	}
	
	/**
	 * Add Association
	 * @param Ext_Model_Association $association
	 */
	public function addAssociation(Ext_Model_Association $association)
	{
		$this->_associations = $association;
	}
	
	/**
	 * Add Model field
	 * @param Ext_Virtual | Ext_Model_Field | array $object
	 * @return boolean
	 */
	public function addField($object)
	{
	    /**
	     * backward compatibility
	     */
	    if($object instanceof  Ext_Model_Field){
	        $object = get_object_vars($object);    
	    }

	    if($object instanceof Ext_Virtual && $object->getClass()=='Data_Field')
	    {
	        if(empty($object->name))
	            return false;
	    }
	    elseif(is_array($object))
	    {
	        if(!isset($object['name']))
	            return false;
	         
	        $object = Ext_Factory::object('Data_Field' , $object);
	    }
	    else
	    {
	        return false;
	    }
	
	    $this->_fields[$object->name] = $object;
	     
	    return true;
	}
	
	/**
	 * Add fields from array (configs or Ext_Virtual)
	 * @param array $fields
	 */
	public function addFields(array $fields)
	{
	    foreach($fields as $field)
	        $this->addField($field);
	}
	
	/**
	 * Get model field
	 * @param string $name
	 * @throws Exception
	 * @return Ext_Virtual
	 */
	public function getField($name)
	{
	    $this->_convertFields();
	     
	    if(!isset($this->_fields[$name]))
	        throw new Exception('Cannot find field:' . $name);
	     
	    return $this->_fields[$name];
	}
	
	/**
	 * Get Model fields
	 * @return array
	 */
	public function getFields()
	{
	    $this->_convertFields();
	     
	    return $this->_fields;
	}
	
	/**
	 * Remove model fields
	 */
	public function resetFields()
	{
	    $this->_convertFields();
	    $this->_fields = array();
	}
	
	/**
	 * Remove model field by name
	 * @param string $name
	 */
	public function removeField($name)
	{
	    $this->_convertFields();
	     
	    if(isset($this->_fields[$name]))
	        unset($this->_fields[$name]);
	}
	
	/**
	 * Rename model field
	 * @param string $oldName
	 * @param string $newName
	 * @return boolean
	 */
	public function renameField($oldName , $newName)
	{
	    $this->_convertFields();
	     
	    if(empty($newName) || !isset($this->_fields[$oldName]) || isset($this->_fields[$newName]))
	        return false;
	     
	    $cfg = $this->getField($oldName);
	    $cfg->name = $newName;
	    $this->removeField($oldName);
	    $this->addField($cfg->getConfig()->__toArray(true));
	    return true;
	}
	
	/**
	 * Check if field exists
	 * @param string $name
	 * @return boolean
	 */
	public function fieldExists($name)
	{
	    $this->_convertFields();
	    return isset($this->_fields[$name]);
	}
	
	
	public function __get($name)
	{
	    $this->_convertFields();
	     
	    if($name === 'fields')
	        return array_values($this->getFields());
	    else
	        return parent::__get($name);
	}
	
	/**
	 * Convert fields from config property to the local variable
	 */
	protected function _convertFields()
	{
	    if(empty($this->_config->fields))
	        return;
	    
	    if(is_string($this->_config->fields)){	        
	        $fields = json_decode($this->_config->fields , true);
	    }
	     
	
	    if(!empty($fields))
	    {
	        foreach ($fields as $field){
	            if(isset($field['name']) && !isset($this->_fields[$field['name']]))
	                $this->addField($field);
	        }
	    }
	    $this->fields = '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ext_Object::getDefineJs()
	 */
	public function getDefineJs($namespace = false)
	{	
		if($namespace)
			$name = $namespace.'.'.$this->getName();
		else 
			$name = $this->getName();

        if(!empty($this->_fields) && $this->_config->isValidProperty('fields'))
        {
//            foreach ($this->_fields as $field)
//            {
//                if($field->getConfig()->isValidProperty('mapping') && strlen($field->mapping))
//                {
//                    $model = Ext_Code::appendNamespace($field->mapping);
//                    $field->mapping = $model;
//                }
//            }
            $this->fields = '['.implode(',',array_values($this->_fields)).']';
        }

			
		$code = "\n".'Ext.define("'.$name.'",{'."\n".
			"\t".'extend:"'.$this->_config->getExtends().'",'."\n".
			implode(",\n\t", $this->_config->asStringList()).
		'});';		
		return $code;
	}
	/**
	 * (non-PHPdoc)
	 * @see Ext_Object::__toString()
	 */
	public function __toString()
	{

	    if(!empty($this->_fields) && $this->_config->isValidProperty('fields'))
	    {
            foreach ($this->_fields as $field)
            {
                if($field->getConfig()->isValidProperty('mapping') && strlen($field->mapping))
                {
                    $model = Ext_Code::appendNamespace($field->mapping);
                    $field->mapping = $model;
                }
            }

            $this->fields = '['.implode(',',array_values($this->_fields)).']';
        }
		return parent::__toString();
	}
	/**
	 * @see Ext_Object::getState()
	 */
	public function getState()
	{
		$state = parent::getState();
		$fields = $this->_fields;
		$fieldData = array();
		if(!empty($fields)){
			foreach($fields as $name=>$v){
				$fieldData[$name] = array(
					'class' => get_class($v),
					'extClass' => $v->getClass(),
					'state' => $v->getState()
				);
			}
		}
		$state['state'] = [
			'_validations'=> $this->_validations,
			'_associations'=>$this->_associations
		];
		$state['fields'] = $fieldData;

		return $state;
	}
	/**
	 * Set object state
	 * @param $state
	 */
	public function setState(array $state)
	{
		parent::setState($state);

		if(isset($state['fields']) && !empty($state['fields'])){
			foreach($state['fields'] as $k=>$v){
				$field = Ext_Factory::object($v['extClass']);
				$field->setState($v['state']);
				$this->_fields[$k] = $field;
			}
		}
	}
}