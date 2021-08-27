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
 * An abstract class for implementing background tasks.
 * @package Bgtask
 */
abstract class AbstractTask
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

    /**
     * @var Log|null
     */
    protected $logger = null;
    /**
     * Task PID
     * @var integer
     */
    protected $pid;
    /**
     * Sleep state
     * @var boolean
     */
    protected $sleepFlag = false;
    /**
     * Sleep interval in seconds
     * @var integer
     */
    protected $sleepInterval = 3;
    /**
     * Operation count
     * @var integer
     */
    protected $opTotal = 0;
    /**
     * Finished operations
     * @var integer
     */
    protected $opFinished = 0;
    /**
     * Status
     * @var integer
     */
    protected $status = 0;
    /**
     * Task config
     * @var array
     */
    protected $config;
    /**
     * @var Manager
     */
    protected $tm;

    /**
     * Constructor, receives task settings
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->tm = Manager::factory();
        $this->logger = $this->tm->getLogger();
        $this->init();
        $this->run();
    }

    /**
     * Get task description, an abstract
     * method, the logic is to be implemented in the relevant task class,
     * returns a description string, like: «Statistics revision»
     * @return string
     */
    abstract public function getDescription();

    protected function init()
    {
        $this->pid = $this->tm->addTaskRecord($this->getDescription());
        $this->status = self::STATUS_RUN;
        $this->tm->setStarted($this->pid);
        if (!is_null($this->logger)) {
            $this->logger->log('start');
        }
    }

    /**
     * Check if task was killed by task manager
     */
    protected function isLive()
    {
        if (!$this->tm->isLive($this->pid)) {
            $this->terminate();
        }
    }

    /**
     * Sleep
     */
    protected function sleep()
    {
        $this->status = self::STATUS_SLEEP;
        $this->sleepFlag = true;
        $this->updateState();
        if (!is_null($this->logger)) {
            $this->logger->log('sleep');
        }
    }

    /**
     * Continue
     */
    protected function continue()
    {
        $this->sleepFlag = false;
        $this->status = self::STATUS_RUN;
        $this->updateState();
        if (!is_null($this->logger)) {
            $this->logger->log('continue');
        }
    }

    /**
     * Kill the process
     */
    public function terminate()
    {
        if (!is_null($this->logger)) {
            $this->logger->log('terminated');
        }
        exit();
    }

    /**
     * Finish the task, task statistics getting updated,
     * setting the «Finish» status
     */
    public function finish()
    {
        $this->updateState();

        $manager = $this->tm;
        $manager->setFinished($this->pid);
        $manager->clearSignals($this->pid);

        if (!is_null($this->logger)) {
            $this->logger->log('finish');
        }

        exit();
    }

    /**
     * Stop running a task due to an error
     * @param string $message - optioanl
     */
    public function error($message = '')
    {
        $this->updateState();

        $manager = $this->tm;
        $manager->setError($this->pid);
        $manager->clearSignals($this->pid);

        if (!is_null($this->logger)) {
            $this->logger->log('error ' . $message);
        }

        exit();
    }

    /**
     * Record the message to the task log
     * @param string $message
     */
    public function log($message)
    {
        if (!is_null($this->logger)) {
            $this->logger->log($message);
        }
    }

    /**
     * Stop running a task
     */
    public function stop()
    {
        $this->sleepFlag = false;
        $this->updateState();
        $this->tm->setStoped($this->pid);
        $this->tm->clearSignals($this->pid);

        if (!is_null($this->logger)) {
            $this->logger->log('stop');
        }

        exit();
    }

    /**
     * Update the task statistics
     * to send the information on the task progress
     */
    public function updateState()
    {
        $this->tm->updateState($this->pid, $this->opTotal, $this->opFinished, $this->status);
    }

    /**
     * Process received signals,
     * starts processing signals and arranges for their receival
     */
    public function processSignals()
    {
        $this->isLive();

        $sig = $this->tm->getSignals($this->pid, true);

        if (empty($sig)) {
            return;
        }

        foreach ($sig as $signal) {
            switch ($signal) {
                case self::SIGNAL_SLEEP :
                    $this->sleep();
                    break;
                case self::SIGNAL_CONTINUE :
                    $this->continue();
                    break;
                case self::SIGNAL_STOP :
                    $this->stop();
            }
        }
        if ($this->sleepFlag) {
            $this->wait();
        }
    }

    /**
     * Wait for signals
     */
    protected function wait()
    {
        while ($this->sleepFlag) {
            sleep($this->sleepInterval);
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
     * @param Log $logger
     * @return void
     */
    public function setLogger(Log $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set the number of expected operations (overall counter)
     * @param integer $count
     */
    public function setTotalCount($count)
    {
        $this->opTotal = $count;
    }

    /**
     * Set the number of finished operations
     * @param integer $count
     */
    public function setCompletedCount($count)
    {
        $this->opFinished = $count;
    }

    /**
     * Increase the number of finished operations
     * @param integer $count — optional, default = 1
     */
    public function incrementCompleted($count = 1)
    {
        $this->opFinished += $count;
    }
}