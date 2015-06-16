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
 * Class Bgtask_Manager is used for managing background tasks, sending signals to background processes, collecting statistics.
 * @author Kirill A Egorov 2011
 * @package Bgtask
 */
class Bgtask_Manager
{

	const LAUNCHER_HTTP = 'Bgtask_Launcher_Local_Http';
	const LAUNCHER_JSON = 'Bgtask_Launcher_Local_Json';
	const LAUNCHER_SILENT = 'Bgtask_Launcher_Local_Silent';
    const LAUNCHER_SIMPLE = 'Bgtask_Launcher_Simple';

	const STORAGE_ORM = 'Bgtask_Storage_Orm';

    static protected $_instance = null;

    /**
     * Instantiate an object
     * @return Bgtask_Manager
     */
    static public function getInstance()
    {
        if(is_null(self::$_instance))
            self::$_instance = new self;
        return  self::$_instance;
    }

    protected function __construct(){}
    protected function __clone(){}

    /**
     * Task Storage
     * @var Bgtask_Storage
     */
    protected $_storage = null;

    /**
     * Task activity logger
     * @var Bgtask_Log
     */
    protected $_logger = null;

    /**
     * Set up storage adapter for background tasks
     * @param Bgtask_Storage $storage
     */
    public function setStorage(Bgtask_Storage $storage)
    {
        $this->_storage = $storage;
    }

    /**
     * Set up logger adapter
     * @param Log $logger
     */
    public function setLogger(Log $logger)
    {
    	$this->_logger = $logger;
    }

    /**
     *  Get logger adapter
     */
    public function getLogger()
    {
    	return $this->_logger;
    }

    /**
     * Launch a background task
     * @param string $launcher - const, task launcher
     * @param string $task - task object class name
     * @param array $config
     */
    public function launch($launcher , $task , array $config)
    {
        if(!class_exists($launcher))
            trigger_error('Invalid Task launcher '.$launcher);

        $launcher = new $launcher;
        $launcher->launch($task , $config);
    }

    /**
     * Get a list of tasks
     * @return array
     */
    public function getList()
    {
        return $this->_storage->getList();
    }

    /**
     * Get information on a task by its pid
     * @param integer $pid
     * @return array
     */
    public function get($pid)
    {
        return $this->_storage->get($pid);
    }

    /**
     * Sends a signal to a background process
     * @param integer $pid
     * @param integer $signal - const
     * @return void
     */
    public function signal($pid , $signal)
    {
        return $this->_storage->signal($pid, $signal);
    }

    /**
     * Set up task configuration
     * @param integer $pid
     * @param array $config
     * @return void
     */
    public function setConfig($pid , array $config)
    {
        return $this->_storage->setConfig($pid, $config);
    }

    /**
     * Get task configuration
     * @param integer $pid
     * @return array
     */
    public function getConfig($pid)
    {
        return $this->_storage->getConfig($pid);
    }

    /**
     * Kill a task
     * @param integer $pid
     * @return boolean
     */
     public function kill($pid)
     {
         return $this->_storage->kill($pid);
     }

    /**
     * et signals for a certain task by its pid
     * @param integer $pid
     * @param boolean $clean - remove signals after reading
     * @return array();
     */
    public function getSignals($pid , $clean = false)
    {
        return $this->_storage->getSignals($pid , $clean);
    }

    /**
     * Remove  signals for a certain task by its pid
     * @param integer $pid
     * @param array $sigId - optional
     * @return array
     */
    public function clearSignals($pid , $sigId = false)
    {
        return $this->_storage->clearSignals($pid , $sigId);
    }

    /**
     * Update task status
     * @param integer $pid
     * @param integer $opTotal  - expected operations count
     * @param integer $opFinished  - operations finished
     * @param integer $status - status constant
     */
    public function updateState($pid , $opTotal , $opFinished , $status)
    {
    	$memoryPeak = memory_get_peak_usage();
    	$memoryAllocated = memory_get_usage();
    	$this->_storage->updateState($pid , $opTotal , $opFinished , $status, $memoryPeak , $memoryAllocated);
    }

    /**
     * Check if the task is running
     * @param integer $pid
     * @return boolean
     */
    public function isLive($pid)
    {
    	return $this->_storage->isLive($pid);
    }

    /**
     *  Finish (status ‘Finished’)
     * @param integer $pid
     */
    public function setFinished($pid)
    {
    	return $this->_storage->setFinished($pid, date('Y-m-d H:i:s'));
    }

    /**
     *  Stop a task
     * @param integer $pid
     */
    public function setStoped($pid)
    {
    	return $this->_storage->setStoped($pid, date('Y-m-d H:i:s'));
    }

 	/**
     * Define error when running a task
     * @param integer $pid
     */
    public function setError($pid)
    {
    	return $this->_storage->setError($pid, date('Y-m-d H:i:s'));
    }

    /**
     * Start running a task
     * @param integer $pid
     */
    public function setStarted($pid)
    {
    	return $this->_storage->setStarted($pid, date('Y-m-d H:i:s'));
    }

    /**
     * Add a task and get its identifier (pid)
     * @param string $description
     * @return integer
     */
    public function addTaskRecord($description)
    {
    	return $this->_storage->addTaskRecord($description);
    }
}