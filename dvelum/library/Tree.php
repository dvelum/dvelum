<?php
/**
 * Class optimized for fast work with tree structures.
 * Easily handles up to 25000-30000 sets of elements (less than 1 second to fill out)
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011  Kirill A Egorov
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
class Tree
{
	protected $_items = array();
	protected $_childs = array();

	/**
	 * Set elements sorting order by ID
	 * @param mixed $id — element identifier
	 * @param integer $order — sorting order
	 * @return boolean
	 */
	public function setItemOrder($id , $order)
	{
		if(! $this->itemExists($id))
			return false;
		
		$this->_items[$id]['order'] = $order;
		return true;
	}

	/**
	 * Sort child elements
	 * @param mixed $parentId — nor required;  a parent identifier -
	 * is the root node by default, which sorts all other nodes
	 */
	public function sortItems($parentId = false)
	{
		if($parentId)
			$this->_sortChilds($parentId);
		else
			foreach($this->_childs as $k => $v)
				$this->_sortChilds($k);
	}

	/**
	 * Check if the node exists by its identifier
	 * @param mixed $id
	 * @return boolean
	 */
	public function itemExists($id)
	{
		return isset($this->_items[$id]);
	}

	/**
	 * Get the number of elements in a tree
	 * @return integer
	 */
	public function getItemsCount()
	{
		return sizeof($this->_items);
	}

	/**
	 * Add a node to the tree
	 * @param mixed $id — unique identifier
	 * @param mixed $parent — parent node identifier
	 * @param mixed $data — node data
	 * @param boolean|integer $order  - sorting order, not required
	 * @return boolean —  successfully invoked
	 */
	public function addItem($id , $parent , $data , $order = false)
	{
		if($this->itemExists($id) || strval($id) === '0')
			return false;
		
		if($order === false && isset($this->_childs[$parent]))
			$order = sizeof($this->_childs[$parent]);
		
		$this->_items[$id] = array(
				'id' => $id , 
				'parent' => $parent , 
				'data' => $data , 
				'order' => $order
		);
		if(! isset($this->_childs[$parent]))
			$this->_childs[$parent] = array();
		
		$this->_childs[$parent][$id] = & $this->_items[$id];
		return true;
	}

	/**
	 * Update the node data
	 * @param mixed $id — node identifier
	 * @param mixed $data — node data
	 * @return boolean —  successfully invoked
	 */
	public function updateItem($id , $data)
	{
		if(!$this->itemExists($id) || strval($id) === '0')
			return false;
		
		$this->_items[$id]['data'] = $data;
		return true;
	}

	/**
	 * Get node structure by ID
	 * @param mixed $id
	 * @throws Exception
	 * @return array - an array with keys ('id','parent','order','data')
	 */
	public function getItem($id)
	{
		if($this->itemExists($id))
			return $this->_items[$id];
		else
			throw new Exception('Item "'.$id.'" is not found');
	}

	/**
	 * Get node data by ID
	 * @param string $id
	 * @return mixed
	 */
	public function getItemData($id)
	{
		$data = $this->getItem($id);
		return $data['data'];
	}

	/**
	 * Check if the node has child elements
	 * @param string $id — node identifier
	 * @return boolean
	 */
	public function hasChilds($id)
	{
		if(isset($this->_childs[$id]) && !empty($this->_childs[$id]))
			return true;
		else
			return false;
	}

	/**
	 * Get data on all child elements (recursively)
	 * @param mixed id - parent node identifier
	 * @return array - an array with keys ('id','parent','order','data')
	 */
	public function getChildsR($id)
	{
		$data = array();
		if($this->hasChilds($id))
		{
			$childs = $this->getChilds($id);
			foreach($childs as $k => $v)
			{
				$data[] = $v['id'];
				$subChilds = $this->getChildsR($v['id']);
				if(! empty($subChilds))
					$data = array_merge($data , $subChilds);
			}
		}
		return $data;
	}

	protected function _sortChilds($id)
	{
		if(!isset($this->_childs[$id]) || empty($this->_childs[$id]))
			return;
		
		$tmp = array();
		
		foreach($this->_childs[$id] as $key => &$dat)
			$tmp[$dat['id']] = $dat['order'];
		unset($dat);
		
		$this->_childs[$id] = array();
		asort($tmp);

		$sort = 0;
		foreach($tmp as $key => $order){
			$this->_items[$key]['order'] = $sort;
			$this->_childs[$id][$this->_items[$key]['id']] = & $this->_items[$key];
			$sort++;
		}
	}

	/**
	 * Get child nodes’ structures
	 * @var mixed id
	 * @return array
	 */
	public function getChilds($id)
	{
		if(! $this->hasChilds($id))
			return array();
		
		return $this->_childs[$id];
	}

	/**
	 * Recursively removing
	 * @param mixed $id
	 * @return void
	 */
	protected function _remove($id)
	{
		$item = $this->getItem($id);
		$childs = $this->getChilds($id);
		
		if(!empty($childs))
			foreach($childs as $k => &$v)
				$this->_remove($v['id']);
		
		if(isset($this->_childs[$id]))
			unset($this->_childs[$id]);
		
		$parent = $this->_items[$id]['parent'];
		
		if(! empty($this->_childs[$parent]) && isset($this->_childs[$parent][$id]))
			unset($this->_childs[$parent][$id]);
		
		unset($this->_items[$id]);
	}

	/**
	 * Get the parent node identifier by the child node identifier
	 * @param string $id — child node identifier
	 * @return mixed string or false 
	 */
	public function getParentId($id)
	{
		if(! $this->itemExists($id))
			return false;
		
		$data = $this->getItem($id);
		return $data['parent'];
	}

	/**
	 * Change the parent node for the node
	 * @param mixed $id — node identifier
	 * @param mixed $newParent — new parent node identifier
	 * @return boolean
	 */
	public function changeParent($id , $newParent)
	{
		if(!$this->itemExists($id) || (!$this->itemExists($newParent) && strval($newParent) !== '0') || strval($id)==strval($newParent))
			return false;
		
		$oldParent = $this->_items[$id]['parent'];
		$this->_items[$id]['parent'] = $newParent;
		
		if(! empty($this->_childs[$oldParent]) && isset($this->_childs[$oldParent][$id]))
			unset($this->_childs[$oldParent][$id]);
		
		$this->_childs[$newParent][$id] = & $this->_items[$id];
		return true;
	}

	/**
	 * Delete node
	 * @param mixed $id
	 * @return boolean
	 */
	public function removeItem($id)
	{
		if($this->itemExists($id))
			$this->_remove($id);
		
		return true;
	}

	/**
	 * Get structures of the tree elements (nodes)
	 * @return array - an array with keys ('id','parent','order','data')
	 */
	public function getItems()
	{
		return $this->_items;
	}
	
	/**
	 * Get list of parent nodes
	 * @param mixed $id
	 * @return array
	 */
	public function getParentsList($id)
	{
	  $parents = array();
	  if(!$this->itemExists($id))
	    return array();
	   
	  while ($this->getParentId($id)) {
	    $p = $this->getParentId($id);
	    $parents[] = $p;
	    $id = $p;
	  }
	   
	  if(!empty($parents))
	    $parents = array_reverse($parents);
	   
	  return $parents;
	}
}
