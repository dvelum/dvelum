<?php

use Dvelum\Orm\Model;
use Dvelum\Orm;

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
		if($this->cache && $data = $this->cache->load('groups_list'))
			return $data;

		$data = array();
		$sql = $this->dbSlave->select()->from($this->table() , array('id' , 'title'));
		$data = $this->dbSlave->fetchAll($sql);

		if(!empty($data))
			$data = \Dvelum\Utils::collectData('id', 'title', $data);
		/*
		 * Store cache
		 */
		if($this->cache)
			$this->cache->save($data, 'groups_list');

		return $data;
	}

	/**
	 * Add users group
	 * @param string  $title - group name
     * @return mixed
	 */
	public function addGroup(string $title)
	{
		$obj = Orm\Record::factory($this->name);
		$obj->set('title', $title);

		if(!$obj->save())
			return false;

		/**
		 * Invalidate cache
		 */
		if($this->cache)
			$this->cache->remove('groups_list');

		return $obj->getId();
	}
	/**
	 * Remove users Group
	 * @param integer $id
	 * @return boolean
	 */
	public function removeGroup($id)
	{
		$obj = Orm\Record::factory($this->name, $id);

		if(!$obj->delete())
			return false;

		/**
		 * Invalidate cache
		 */
		if($this->cache)
			$this->cache->remove('groups_list');

		return true;
	}
}