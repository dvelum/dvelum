<?php
class Model_Group extends Model
{
	/**
	 * Get group names indexed by group id
	 * @return array
	 */
	public function getGroups()
	{
		/*
		 * Check cache
		 */
		if($this->_cache && $data = $this->_cache->load('groups_list'))
			return $data;

		$data = array();
		$sql = $this->_dbSlave->select()->from($this->table() , array('id' , 'title'));
		$data = $this->_dbSlave->fetchAll($sql);
		if(!empty($data))
			$data = Utils::collectData('id', 'title', $data);
		/*
		 * Store cache
		 */
		if($this->_cache)
			$this->_cache->save($data, 'groups_list');

		return $data;
	}
	/**
	 * Add users group
	 * @param string  $title - group name
	 */
	public function addGroup($title)
	{
		$obj = new Db_Object($this->_name);
		$obj->set('title', $title);

		if(!$obj->save())
			return false;

		/**
		 * Invalidate cache
		 */
		if($this->_cache)
			$this->_cache->remove('groups_list');

		return $obj->getId();
	}
	/**
	 * Remove users Group
	 * @param integer $id
	 * @return boolean
	 */
	public function removeGroup($id)
	{
		$obj = new Db_Object($this->_name, $id);

		if(!$obj->delete())
			return false;

		/**
		 * Invalidate cache
		 */
		if($this->_cache)
			$this->_cache->remove('groups_list');

		return true;
	}
}