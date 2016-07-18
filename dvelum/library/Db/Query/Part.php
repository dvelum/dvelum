<?php
class Db_Query_Part
{
	const JOIN_LEFT = 1;
	const JOIN_RIGHT = 2;
	const JOIN_INNER = 3;
	protected $_parentPart = '';
	protected $_parentField = '';
	protected $_childField = 'id';
	protected $_joinType = Db_Query_Part::JOIN_LEFT;
	protected $_fields = array();
	protected $_object;

	/**
	 * Set Object class for part selection
	 * @param string $name
	 */
	public function setObject($name)
	{
		$config = Db_Object_Config::getInstance($name);
		$this->_object = $name;
		$this->_fields = array();
		$cfgFields = $config->getFieldsConfig(true);
		foreach($cfgFields as $k => $v)
		{
			/*
			 * Ignore multi-links
			 */
			if($config->isMultiLink($k))
				continue;
			
			$title = '';
			if(isset($v['title']))
				$title = $v['title'];
			
			$this->_fields[$k] = array(
					'alias' => '' , 
					'title' => $title , 
					'select' => false , 
					'isLink' => $config->isLink($k) , 
					'selectSub' => false
			);
		}
	}

	/**
	 * Set parent part Id
	 * @param string $parentPartId
	 */
	public function setParentPart($parentPartId)
	{
		$this->_parentPart = $parentPartId;
	}

	/**
	 * Get parent field
	 * @return string
	 */
	public function getParentField()
	{
		return $this->_parentField;
	}

	/**
	 * Set join type for selection
	 * @param integer $type
	 */
	public function setJoinType($type)
	{
		$this->_joinType = $type;
	}

	/**
	 * Set parent linked field name
	 * @param string $name
	 */
	public function setParentField($name)
	{
		$this->_parentField = $name;
	}
	/**
	 * Set linked field name for child element
	 * @param string $name
	 */
	public function setChildField($name)
	{
		$this->_childField = $name;
	}
	/**
	 * Get linked field name for child element
	 * @return string
	 */
	public function getChildField()
	{
		return $this->_childField;
	}

	public function __get($name)
	{
		if(isset($this->{'_' . $name}))
			return $this->{'_' . $name};
		else
			throw new Exception('Trying to get unregistered property _' . $name);
	}

	public function __isset($name)
    {
        return isset($this->{'_' . $name});
    }

    public function getObject()
	{
		return $this->_object;
	}

	public function getFields()
	{
		return $this->_fields;
	}

	public function setFieldCfg($fieldName , $propertyName , $value)
	{
		if(!array_key_exists($fieldName , $this->_fields))
			throw new Exception('Invalid field ' , $fieldName);
		
		if(!array_key_exists($propertyName , $this->_fields[$fieldName]))
			throw new Exception('Invalid field property ' . $propertyName);
		
		$this->_fields[$fieldName][$propertyName] = $value;
	}

	/**
	 * Get Part ID
	 * @return string
	 */
	public function getId()
	{
		return self::findId($this->_parentPart , $this->_parentField , $this->_childField ,  $this->_object);
	}

	/**
	 * Calc part ID
	 * @param string $parentPartId
	 * @param string $parentField
	 * @param string $parentField
	 * @param string $objectName
	 * @return string
	 */
	static public function findId($parentPartId , $parentField , $childField ,  $objectName)
	{
		return md5($parentPartId . $parentField . $childField . $objectName);
	}

	public function __toString()
	{
		return $this->getId();
	}
}