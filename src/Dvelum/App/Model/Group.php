<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
declare(strict_types=1);

namespace Dvelum\App\Model;

use Dvelum\Orm\Model;
use Dvelum\Orm;
use Dvelum\Utils;

class Group extends Model
{
	/**
	 * Get group names indexed by group id
	 * @return array
	 */
	public function getGroups() : array
	{
		/*
		 * Check cache
		 */
		if($this->cache && $data = $this->cache->load('groups_list'))
			return $data;

		$sql = $this->dbSlave->select()->from($this->table() , ['id' , 'title']);
		$data = $this->dbSlave->fetchAll($sql);

		if(!empty($data))
			$data = Utils::collectData('id', 'title', $data);
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
	 * @return bool
	 */
	public function removeGroup($id) : bool
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