<?php
/*
 * DVelum project http://code.google.com/p/phpveil/ , http://dvelum.net
 * Copyright (C) 2011  Kirill A Egorov kirill.a.egorov@gmail.com
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
 * ORM query class, used in Report Constructor
 * @author Kirill A Egorov
 * @uses Tree , Db_Object , Db_Object_Property
 * @package Db
 */
class Db_Query
{
	/**
	 * @var Tree
	 */
	protected $_tree;
	
	protected $_conditions = array();

	/**
	 * Load Db_Query from file
	 * @param string $file
	 * @return Db_Query or false on error
	 */
	static public function load($file)
	{
		$data = file_get_contents($file);
		$obj = unserialize($data);
		if(!is_object($obj) || !$obj instanceof Db_Query)
			return false;
		
		//$this->_filename = $file;
		return $obj;
	}

	/**
	 * Save Db_Query into the file
	 * @param string $file
	 * @return boolean  - success status
	 */
	public function save($file)
	{
		return @file_put_contents($file , serialize($this));
	}

	public function __construct()
	{
		$this->_tree = new Tree();
	}

	public function getRootPart()
	{
		if(!$this->_tree->hasChilds(0))
			return false;
		$root = $this->_tree->getChilds(0);
		return $root[key($root)]['data'];
	}

	/**
	 * Get query part by ID
	 * @param string $id
	 * @return Db_Query_Part or false on error
	 */
	public function getPart($id)
	{
		try
		{
			$item = $this->_tree->getItem($id);
			return $item['data'];
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	/**
	 * Find child part by field
	 * @param string $partId
	 * @return Db_Query_Part or false
	 */
	public function findChild($partId , $field)
	{
		if(!$this->_tree->hasChilds($partId))
			return false;
		
		$childs = $this->_tree->getChilds($partId);
		
		foreach($childs as $child)
			if($child['data']->getParentField() == $field)
				return $child['data'];
		
		return false;
	}

	/**
	 * Add Query part
	 * @param Db_Query_Part $part
	 * @param string $parentPartId - parend Db_Part id
	 * @return boolean
	 */
	public function addPart(Db_Query_Part $part , $parentPartId = 0)
	{
		$part->setParentPart($parentPartId);
		return $this->_tree->addItem($part->getId() , $parentPartId , $part);
	}

	/**
	 * Remove Query part
	 * @param string $partId
	 * @return boolean
	 */
	public function removePart($partId)
	{
		 $this->_tree->removeItem($partId);
		 return true;
	}

	/**
	 * Get Query SQL
	 * @return Db_Select | Zend_Db_Select | false on failure
	 */
	public function getSql()
	{
	    $globalDb = Registry::get('db');
		$sql = $globalDb->select();
		$part = $this->getRootPart();
		
		if(!$part)
			return false;
		
		$fieldsToSelect = $this->_extractFields($part);
		$sql->from(Model::factory($part->getObject())->table() , $fieldsToSelect);
		
		$partId = $part->getId();
		
		if($this->_tree->hasChilds($partId))
			$this->_fillQuery($partId , $sql);
		
		$this->_applyConditions($sql);
		
		return $sql;
	}
	
	/**
	 * Get count selector
	 * @return Db_Select | Zend_Db_Select | boolean false on failure
	 */
	public function getCountSql()
	{
	    $globalDb = Registry::get('db');
		$sql = $globalDb->select();		
		$part = $this->getRootPart();
		
		if(!$part)
			return false;
		
		$sql->from(Model::factory($part->getObject())->table() , array(
				'count' => 'COUNT(*)'
		));
		
		$partId = $part->getId();
		
		if($this->_tree->hasChilds($partId))
			$this->_fillQuery($partId , $sql , true);
		
		$this->_applyConditions($sql);
		
		return $sql;
	
	}
	/**
	 * Apply where conditions
	 * @param Db_Select | Zend_Db_Select $sql
	 */
	protected function _applyConditions($sql)
	{
		$conditions = $this->getConditions();
		if(empty($conditions))
			return;
		
		$dictionary = Dictionary::getInstance('sqloperator');
		
		foreach($conditions as $condition)
		{
			if(!$dictionary->isValidKey($condition->operator))
				continue;
			
			$operator = $dictionary->getValue($condition->operator);
			switch($condition->operator)
			{
				
				case 'IS_NULL' :
				case 'IS_NOT_NULL' :
					$table = Db_Object_Config::getInstance($condition->object)->getTable();
					$sql->where($table . '.' . $condition->field . ' ' . $operator);
					break;
				
				case 'BETWEEN' :
				case 'NOT_BETWEEN' :
					$table = Db_Object_Config::getInstance($condition->object)->getTable();
					$sql->where($table . '.' . $condition->field . ' ' . $operator . ' \'' . addslashes($condition->value) . '\' AND \'' . addslashes($condition->value2) . '\'  ');
					break;
				
				case 'IN' :
				case 'NOT_IN' :
					$table = Db_Object_Config::getInstance($condition->object)->getTable();
					$sql->where($table . '.' . $condition->field . ' ' . $operator . ' (?)' , explode(',' , $condition->value));
					break;
				
				case 'custom' :
					$sql->where($condition->value);
					break;
				
				default :
					$table = Db_Object_Config::getInstance($condition->object)->getTable();
					$sql->where($table . '.' . $condition->field . ' ' . $operator . ' ?' , $condition->value);
			}
		}
	}



	protected function _extractFields(Db_Query_Part $part)
	{
		$fieldsToSelect = array();
		$fields = $part->getFields();
		foreach($fields as $key => $config)
		{
			if(!$config['select'])
				continue;
			
			if(strlen($config['alias']))
				$fieldsToSelect[$config['alias']] = $key;
			else
				$fieldsToSelect[] = $key;
		}
		return $fieldsToSelect;
	}

	/**
	 * Recursive method
	 * @param string $parent - parent part id
	 * @param Db_Select $sql
	 */
	protected function _fillQuery($partId ,  $sql , $countOnly = false)
	{
		$parentItem = $this->_tree->getItem($partId);
		$childs = $this->_tree->getChilds($partId);
		
		foreach($childs as $child)
		{
			$this->_addSqlPart($child['data'] , $sql , $parentItem['data'] , $countOnly);
			$part = $child['data'];
			
			if($this->_tree->hasChilds($part->getId()))
				$this->_fillQuery($part->getId() , $sql , $countOnly);
		}
	}

	protected function _addSqlPart(Db_Query_Part $part , $sql , Db_Query_Part $parenPart , $countOnly = false)
	{
		if($countOnly){
			$fields = array();
		}else{
			$fields = $this->_extractFields($part);
			if(empty($fields))
				return;
		}
		
		
		$parentTable = Db_Object_Config::getInstance($parenPart->getObject())->getTable();
		$curTable = Db_Object_Config::getInstance($part->getObject())->getTable();
		
		$condition = '`' . $parentTable . '`.`' . $part->parentField . '` = `' . $curTable . '`.`'.$part->childField.'`';
		
		switch($part->joinType)
		{
			case Db_Query_Part::JOIN_INNER :
				$sql->joinInner($curTable ,$condition , $fields);
				break;
			case Db_Query_Part::JOIN_LEFT :
				$sql->joinLeft($curTable ,$condition, $fields);
				break;
			case Db_Query_Part::JOIN_RIGHT :
				$sql->joinRight($curTable ,$condition , $fields);
				break;
		}
	}

	/**
	 * Get list of selected columns
	 * @return array
	 */
	public function getSelectedColumns()
	{
		$result = array();
		$partsArray = $this->_tree->getItems();
		
		if(empty($partsArray))
			return array();
		
		foreach($partsArray as $part)
		{
			$fields = $part['data']->getFields();
			if(empty($fields))
				continue;
			
			foreach($fields as $key => $field)
			{
				if(!$field['select'])
					continue;
				
				if(strlen($field['alias']))
					$name = $field['alias'];
				else
					$name = $key;
				
				$result[] = array(
						'name' => $name , 
						'title' => $field['title']
				);
			}
		}
		
		return $result;
	}

	/**
	 * Get list of selected objects
	 * @return array
	 */
	public function getSelectedObjects()
	{
		$result = array();
		$partsArray = $this->_tree->getItems();
		if(!empty($partsArray))
			foreach($partsArray as $part)
				$result[] = $part['data']->getObject();
		
		return array_unique($result);
	}

	/**
	 * Add query condition
	 * @param Db_Query_Condition $condition
	 */
	public function addCondition(Db_Query_Condition $condition)
	{
		$this->_conditions[] = $condition;
	}

	/**
	 * Remove sql condition
	 * @param mixed $index
	 */
	public function removeCondition($index)
	{
		if(isset($this->_conditions[$index]))
			unset($this->_conditions[$index]);
	}

	/**
	 * Get list of sql conditions
	 * @return array
	 */
	public function getConditions()
	{
		return $this->_conditions;
	}

	/**
	 * Set condition (add/update)
	 * @param mixed $index
	 * @param Db_Query_Condition $condition
	 */
	public function setCondition($index , Db_Query_Condition $condition)
	{
		$this->_conditions[$index] = $condition;
	}

	/**
	 * Get condition
	 * @param mixed $index
	 * @return Db_Query_Condition or false
	 */
	public function getCondition($index)
	{
		if(!isset($this->_conditions[$index]))
			return false;
		return $this->_conditions[$index];
	}
}
/*
 TEST
 		$query =new Db_Query($this->_db);
		$part = new Db_Query_Part();
		$part->setObject('Articles');
		
		$part->setFieldCfg('id', 'select', true);
		$part->setFieldCfg('id', 'alias', 'article_id');
		
		$part->setFieldCfg('title', 'select', true);
		$part->setFieldCfg('title', 'alias', 'article_title');

		$query->addPart($part);
//==================================	
		$part = new Db_Query_Part();
		$part->setObject('Topics');
		$part->parentField = 'main_topic';
		$part->curField = 'id';
		$part->joinType = $part::JOIN_LEFT;
		
		$part->setFieldCfg('id', 'select', true);
		$part->setFieldCfg('id', 'alias', 'topic_id');
		
		$part->setFieldCfg('title', 'select', true);
		$part->setFieldCfg('title', 'alias', 'topic_title');

		$query->addPart($part , 'Articles');
//===================================
		$part = new Db_Query_Part();
		$part->setObject('User');
		$part->parentField = 'author_id';
		$part->curField = 'id';
		$part->joinType = $part::JOIN_LEFT;
		

		$part->setFieldCfg('name', 'select', true);
		$part->setFieldCfg('name', 'alias', 'users_name');

		$query->addPart($part , 'Articles');
		
		
		echo $query->getSql(); 
 */