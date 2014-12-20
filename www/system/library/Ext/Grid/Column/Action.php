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
 * Action column for data grid
 * @author Kirill A Egorov
 * @package Ext
 * @subpackage Ext_Grid
 */
class Ext_Grid_Column_Action extends Ext_Grid_Column
{
	/**
	 * @var Tree
	 */
	protected $_actions = array();
	
	public function __construct()
	{
		$this->_actions = new Tree();	
		parent::__construct();
	}
	
	/**
	 * Add action object
	 * @param string $id
	 * @param mixed $data
	 */
	public function addAction($id , $data)
	{
		$this->_actions->addItem($id , 0 , $data);
	}
	
	/**
	 * Check if action exists
	 * @param string $id - action name
	 * @return boolean
	 */
	public function actionExists($id)
	{
		return $this->_actions->itemExists($id);
	}
	
	/**
	 * Get actions list
	 * @return $array
	 */
	public function getActions()
	{
		if(!$this->_actions->hasChilds(0))
			return array();
		
		$list = $this->_actions->getChilds(0);
		
		$result = array();
		if(!empty($list))
			foreach($list as $k=>$v)
				$result[$v['id']] = $v['data'];
		
		return $result;
	}
	
	/**
	 * Set action sorting order
	 * @param string $id
	 * @param integer $order
	 */
	public function setActionOrder($id , $order)
	{
		$this->_actions->setItemOrder($id , $order);
	}
	
	/**
	 * Apply sorting orders for actions (sort actions)
	 */
	public function sortActions()
	{
		$this->_actions->sortItems();
	}
	
	/**
	 * Get Action
	 * @param string $id
	 * @return Ext_Grid_Column_Action_Button
	 * @throws Exceptio
	 */
	public function getAction($id)
	{
		if(!$this->actionExists($id))
			throw new Exception('Invalid action');
		
		return $this->_actions->getItemData($id);
	}
	
	/**
	 * Remove Action
	 * @param string $id
	 * @return boolean
	 */
	public function removeAction($id)
	{
		if(!$this->actionExists($id))
			return false;
		
		return $this->_actions->removeItem($id);
	}
	
	public function __toString()
	{
		$this->_convertListeners();
		
		$actions = $this->getActions();
		
		if(!empty($actions)){
			$actions = array_values($actions);
			$this->_config->items = '['.implode(',', $actions).']';
		}
		
		return $this->_config->__toString();
	}
}