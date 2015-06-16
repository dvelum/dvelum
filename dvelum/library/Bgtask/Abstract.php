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
 * An abstract class for implementing background tasks.
 * @package Bgtask
 */
abstract class Bgtask_Abstract
{
	const SIGNAL_SLEEP = 1;
	const SIGNAL_CONTINUE = 2;
	const SIGNAL_STOP = 3;

	const STATUS_UNDEFINED = 0;
	const STATUS_RUN = 1;
	const STATUS_SLEEP = 2;
	const STATUS_STOPED = 3;
	const STATUS_FINISHED = 4;
	const STATUS_ERROR = 5;

	protected $_logger = null;
	/**
	 * Task PID
	 * @var integer
	 */
	protected $_pid;
	/**
	 * Sleep state
	 * @var boolean
	 */
	protected $_sleepFlag = false;
	/**
	 * Sleep interval in seconds
	 * @var integer
	 */
	protected $_sleepInterval = 3;
	/**
	 * Operation count
	 * @var integer
	 */
	protected $_opTotal = 0;
	/**
	 * Finished operations
	 * @var integer
	 */
	protected $_opFinished = 0;
	/**
	 * Status
	 * @var integer
	 */
	protected $_status = 0;
	/**
	 * Task config
	 * @var array
	 */
	protected $_config;
	/**
	 * @var Bgtask_Manager
	 */
	protected $_tm;

	/**
	 * Constructor, receives task settings
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->_config = $config;
		$this->_tm = Bgtask_Manager::getInstance();
		$this->_logger = $this->_tm->getLogger();
		$this->_init();
		$this->run();
	}

	/**
	 * Get task description, an abstract
     * method, the logic is to be implemented in the relevant task class,
     * returns a description string, like: «Statistics revision»
	 * @return string
	 */
	abstract public function getDescription();

	protected function _init()
	{
		$this->_pid = $this->_tm->addTaskRecord($this->getDescription());
		$this->_status = self::STATUS_RUN;
		$this->_tm->setStarted($this->_pid);
		if(!is_null($this->_logger))
			$this->_logger->log('start');
	}

	/**
	 * Check if task was killed by task manager
	 */
	protected function _isLive()
	{
		if(!$this->_tm->isLive($this->_pid))
			$this->terminate();
	}

	/**
	 * Sleep
	 */
	protected function _sleep()
	{
		$this->_status = self::STATUS_SLEEP;
		$this->_sleepFlag = true;
		$this->updateState();
		if(!is_null($this->_logger))
			$this->_logger->log('sleep');
	}

	/**
	 * Continue
	 */
	protected function _continue()
	{
		$this->_sleepFlag = false;
		$this->_status = self::STATUS_RUN;
		$this->updateState();
		if(!is_null($this->_logger))
			$this->_logger->log('continue');
	}

	/**
	 * Kill the process
	 */
	public function terminate()
	{
		if(!is_null($this->_logger))
			$this->_logger->log('terminated');
		exit();
	}

   /**
	* Finish the task, task statistics getting updated,
	* setting the «Finish» status
	*/
	public function finish()
	{
		$this->updateState();

		$manager = $this->_tm;
		$manager->setFinished($this->_pid);
		$manager->clearSignals($this->_pid);

		if(!is_null($this->_logger))
			$this->_logger->log('finish');

		exit();
	}

	/**
	 * Stop running a task due to an error
	 * @param string $message - optioanl
	 */
	public function error($message = '')
	{
        $this->updateState();

		$manager = $this->_tm;
		$manager->setError($this->_pid);
		$manager->clearSignals($this->_pid);

		if(!is_null($this->_logger))
			$this->_logger->log('error '. $message);

		exit();
	}

	/**
	 * Record the message to the task log
	 * @param string $message
	 */
	public function log($message)
	{
		if(!is_null($this->_logger))
			$this->_logger->log($message);
	}

	/**
	 * Stop running a task
	 */
	public function stop()
	{
		$this->_sleepFlag = false;
		$this->updateState();
		$this->_tm->setStoped($this->_pid);
		$this->_tm->clearSignals($this->_pid);

		if(!is_null($this->_logger))
			$this->_logger->log('stop');

		exit();
	}

	/**
	 * Update the task statistics
     * to send the information on the task progress
	 */
	public function updateState()
	{
		$this->_tm->updateState($this->_pid , $this->_opTotal , $this->_opFinished , $this->_status);
	}

   /**
    * Process received signals,
    * starts processing signals and arranges for their receival
    */
	public function processSignals()
	{
		$this->_isLive();

		$sig = $this->_tm->getSignals($this->_pid , true);

		if(empty($sig))
			return;

		foreach($sig as $signal)
		{
			switch($signal)
			{
				case self::SIGNAL_SLEEP :
					$this->_sleep();
					break;
				case self::SIGNAL_CONTINUE :
					$this->_continue();
					break;
				case self::SIGNAL_STOP :
					$this->stop();
			}
		}
		if($this->_sleepFlag)
			$this->_wait();
	}

   /**
	* Wait for signals
	*/
	protected function _wait()
	{
		while($this->_sleepFlag)
		{
			sleep($this->_sleepInterval);
			$this->processSignals();
		}
	}

   /**
	* Run a task, an abstract method
	* for describing the task itself
	*/
	abstract public function run();

	/**
	 * Set up an adapter for logger interface
	 * @param Bgtask_Log $logger
	 * @return void
	 */
	public function setLogger(Bgtask_Log $logger)
	{
		$this->_logger = $logger;
	}

	/**
	 * Set the number of expected operations (overall counter)
	 * @param integer $count
	 */
	public function setTotalCount($count)
	{
		$this->_opTotal = $count;
	}

	/**
	 * Set the number of finished operations
	 * @param integer $count
	 */
	public function setCompletedCount($count)
	{
		$this->_opFinished = $count;
	}

	/**
	 * Increase the number of finished operations
	 * @param integer $count — optional, default = 1
	 */
	public function incrementCompleted($count = 1)
	{
		$this->_opFinished+= $count;
	}
}