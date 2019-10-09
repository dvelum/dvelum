<?php
/*
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
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

namespace Dvelum\BackgroundTask;

/**
 * Class is used for managing background tasks, sending signals to background processes, collecting statistics.
 * @author Kirill A Egorov 2011
 * @package Bgtask
 */
class Manager
{

    const LAUNCHER_HTTP = '\\Dvelum\\BackgroundTask\\Launcher\\Local\\Http';
    const LAUNCHER_JSON = '\\Dvelum\\BackgroundTask\\Launcher\\Local\\Json';
    const LAUNCHER_SILENT = '\\Dvelum\\BackgroundTask\\Launcher\\Local\\Silent';
    const LAUNCHER_SIMPLE = '\\Dvelum\\BackgroundTask\\Launcher\\Simple';

    const STORAGE_ORM = '\\Dvelum\\BackgroundTask\\Storage\\Orm';

    static protected $instance = null;

    /**
     * Instantiate an object
     * @return Manager
     */
    static public function factory(): Manager
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    /**
     * Task Storage
     * @var Storage
     */
    protected $storage = null;

    /**
     * Task activity logger
     * @var Log
     */
    protected $logger = null;

    /**
     * Set up storage adapter for background tasks
     * @param Storage $storage
     */
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Set up logger adapter
     * @param Log $logger
     */
    public function setLogger(Log $logger)
    {
        $this->logger = $logger;
    }

    /**
     *  Get logger adapter
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Launch a background task
     * @param string $launcher - const, task launcher
     * @param string $task - task object class name
     * @param array $config
     */
    public function launch($launcher, $task, array $config)
    {
        if (!class_exists($launcher)) {
            trigger_error('Invalid Task launcher ' . $launcher);
        }
        /**
         * @var Launcher $launcher
         */
        $launcher = new $launcher;
        $launcher->launch($task, $config);
    }

    /**
     * Get a list of tasks
     * @return array
     */
    public function getList()
    {
        return $this->storage->getList();
    }

    /**
     * Get information on a task by its pid
     * @param integer $pid
     * @return array
     */
    public function get($pid)
    {
        return $this->storage->get($pid);
    }

    /**
     * Sends a signal to a background process
     * @param integer $pid
     * @param integer $signal - const
     * @return bool
     */
    public function signal($pid, $signal)
    {
        return $this->storage->signal($pid, $signal);
    }

    /**
     * Set up task configuration
     * @param integer $pid
     * @param array $config
     * @return void
     */
    public function setConfig($pid, array $config)
    {
        $this->storage->setConfig($pid, $config);
    }

    /**
     * Get task configuration
     * @param integer $pid
     * @return array
     */
    public function getConfig($pid)
    {
        return $this->storage->getConfig($pid);
    }

    /**
     * Kill a task
     * @param integer $pid
     * @return boolean
     */
    public function kill($pid)
    {
        return $this->storage->kill($pid);
    }

    /**
     * et signals for a certain task by its pid
     * @param integer $pid
     * @param boolean $clean - remove signals after reading
     * @return array();
     */
    public function getSignals($pid, $clean = false)
    {
        return $this->storage->getSignals($pid, $clean);
    }

    /**
     * Remove  signals for a certain task by its pid
     * @param integer $pid
     * @param array $sigId - optional
     * @return array
     */
    public function clearSignals($pid, $sigId = false)
    {
        return $this->storage->clearSignals($pid, $sigId);
    }

    /**
     * Update task status
     * @param integer $pid
     * @param integer $opTotal - expected operations count
     * @param integer $opFinished - operations finished
     * @param integer $status - status constant
     */
    public function updateState($pid, $opTotal, $opFinished, $status)
    {
        $memoryPeak = memory_get_peak_usage();
        $memoryAllocated = memory_get_usage();
        $this->storage->updateState($pid, $opTotal, $opFinished, $status, $memoryPeak, $memoryAllocated);
    }

    /**
     * Check if the task is running
     * @param integer $pid
     * @return boolean
     */
    public function isLive($pid)
    {
        return $this->storage->isLive($pid);
    }

    /**
     *  Finish (status ‘Finished’)
     * @param integer $pid
     */
    public function setFinished($pid)
    {
        return $this->storage->setFinished($pid, date('Y-m-d H:i:s'));
    }

    /**
     *  Stop a task
     * @param integer $pid
     */
    public function setStoped($pid)
    {
        return $this->storage->setStoped($pid, date('Y-m-d H:i:s'));
    }

    /**
     * Define error when running a task
     * @param integer $pid
     */
    public function setError($pid)
    {
        return $this->storage->setError($pid, date('Y-m-d H:i:s'));
    }

    /**
     * Start running a task
     * @param integer $pid
     */
    public function setStarted($pid)
    {
        return $this->storage->setStarted($pid, date('Y-m-d H:i:s'));
    }

    /**
     * Add a task and get its identifier (pid)
     * @param string $description
     * @return integer
     */
    public function addTaskRecord($description)
    {
        return $this->storage->addTaskRecord($description);
    }
}