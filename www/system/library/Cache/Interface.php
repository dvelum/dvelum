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
 * Interface for cache adapters
 * @author Kirill A Egorov
 * @package Cache
 */
interface Cache_Interface
{
	/**
	 * Add / replace cache variable
	 * @param mixed $data
	 * @param string $key
	 * @param integer $lifetime - optional
	 */
	public function save($data , $key , $lifetime=false);
	/**
	 * Load cached variable
	 * @param string $key
	 * @return mixed|false 
	 */
	public function load($key);
	/**
	 * Clear cache
	 */
	public function clean();
	/**
	 * Remove cached variable
	 * @param string $key
	 * @return boolean 
	 */
	public function remove($key);
	/**
	 * Get statistics for cache operation
	 * @return array
	 */
	public function getOperationsStat();
}