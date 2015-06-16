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
 * Abstract class for Background Storage
 * @author Kirill A Egorov 2011
 * @package Bgtask
 */
abstract class Bgtask_Storage
{
	/**
	 * Get current task list
	 * @return array
	 */
	abstract public function getList();

	/**
	 * Get Task info by Id
	 * @param integer $pid
	 * @return array
	 */
	abstract public function get($pid);

	/**
	 * Send signal
	 * @param integer $pid
	 * @param integer $signal - const
	 * @return void
	 */
	abstract public function signal($pid , $signal);

	/**
	 * Kill task
	 * @param integer $pid
	 * @return boolean
	 */
	abstract public function kill($pid);

	/**
	 * Get task signals
	 * @param integer $pid
	 * @param boolean $clean - remove signals after reading
	 * @return array();
	 */
	abstract public function getSignals($pid , $clean = false);

	/**
	 * Remove signals
	 * @param integer $pid
	 * @param array $sigId - optional
	 * @return array
	 */
	abstract public function clearSignals($pid , $sigId = false);

	/**
	 * Update task state
	 * @param integer $pid
	 * @param integer $memory - memory allocated
	 * @param integer $opTotal
	 * @param integer $opFinished
	 * @param integer $status
	 */
	abstract public function updateState($pid , $opTotal , $opFinished , $status , $memoryPeak , $memoryAllocated);

	/**
	 * Teminate process
	 */
	protected function _terminate()
	{
		/**
		 * @todo log termination
		 */
		exit();
	}

	/**
	 * Check task record
	 * @param integer $pid
	 * @return boolean
	 */
	abstract public function isLive($pid);

	/**
	 * Set task finished
	 * @param integer $pid
	 * @param string $time
	 */
	abstract public function setStarted($pid , $time);

	/**
	 * Set task finished
	 * @param integer $pid
	 * @param string $time
	 */
	abstract public function setFinished($pid , $time);

	/**
	 * Set task stopped
	 * @param integer $pid
	 * @param string $time
	 */
	abstract public function setStoped($pid , $time);
	
	/**
	 * Set task stopped with error
	 * @param integer $pid
	 * @param string $time
	 */
	abstract public function setError($pid , $time);

	/**
	 * Add task record
	 * @param string $description
	 * @return inter  - task pid
	 */
	abstract public function addTaskRecord($description);
}