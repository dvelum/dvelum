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

namespace Dvelum\BackgroundTask\Storage;

use Dvelum\BackgroundTask\AbstractTask;
use Dvelum\BackgroundTask\Storage;
use Dvelum\Orm\Record;
use Dvelum\Orm\Model;

/**
 *
 * ORM based tasks storage
 * @author Kirill A Egorov
 * @package Bgtask
 * @subpackage Storage
 */
class Orm extends Storage
{
    protected $objects = [];

    /**
     * @var Model
     */
    protected $objectModel;

    /**
     * @var \Dvelum\App\Model\Bgtask\Signal
     */
    protected $signalModel;

    /**
     * @param Model $objectModel
     * @param Model $signalModel
     */
    public function __construct(Model $objectModel, Model $signalModel)
    {
        $this->objectModel = $objectModel;
        $this->signalModel = $signalModel;
    }

    /**
     * @inheritDoc
     * @see Storage::getList()
     */
    public function getList()
    {
        return $this->objectModel->query()->fetchAll();
    }

    /**
     * @inheritDoc
     * @see Storage::get()
     */
    public function get($pid)
    {
        return $this->objectModel->getItem($pid);
    }

    /**
     * @inheritDoc
     * @see Storage::signal()
     */
    public function signal($pid, $signal)
    {
        $object = Record::factory('bgtask_signal');
        $object->pid = $pid;
        $object->signal = $signal;
        return $object->save(false);
    }

    /**
     * @inheritDoc
     * @see Storage::kill()
     */
    public function kill($pid)
    {
        $this->objectModel->remove($pid);
        $this->signalModel->clearSignals($pid);

        if (isset($this->objects[$pid])) {
            unset($this->objects[$pid]);
        }

        return true;
    }

    /**
     * @inheritDoc
     * @see Storage::getSignals()
     */
    public function getSignals($pid, $clean = false)
    {
        $signals = $this->signalModel->query()->filters(['pid' => $pid])->fetchAll();

        $result = [];
        if (!empty($signals)) {
            $result = \Dvelum\Utils::fetchCol('signal', $signals);
        }
        if ($clean) {
            $this->signalModel->clearSignals($pid);
        }
        return $result;
    }

    /**
     * @inheritDoc
     * @see Storage::clearSignals()
     */
    public function clearSignals($pid, $sigId = false)
    {
        if ($sigId) {
            $this->signalModel->remove($sigId);
        } else {
            $this->signalModel->clearSignals($pid);
        }
    }

    /**
     * @inheritDoc
     * @see Storage::updateState()
     */
    public function updateState($pid, $opTotal, $opFinished, $status, $memoryPeak, $memoryAllocated)
    {
        $object = $this->getObject($pid);
        $object->memory_peak = $memoryPeak;
        $object->memory = $memoryAllocated;
        $object->op_total = $opTotal;
        $object->op_finished = $opFinished;
        $object->status = $status;

        if (!$object->save()) {
            $this->terminate();
        }
    }

    /**
     * @inheritDoc
     * @see Storage::isLive()
     */
    public function isLive($pid)
    {
        if ($this->objectModel->query()->filters(['id' => $pid])->getCount()) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     * @see Storage::setFinished()
     */
    public function setFinished($pid, $time)
    {
        $object = $this->getObject($pid);
        $object->status = AbstractTask::STATUS_FINISHED;
        $object->time_finished = $time;

        if (!$object->save()) {
            $this->terminate();
        }
    }

    /**
     * @inheritDoc
     * @see Storage::setStoped()
     */
    public function setStoped($pid, $time)
    {
        $object = $this->getObject($pid);
        $object->status = AbstractTask::STATUS_STOPED;
        $object->time_finished = $time;

        if (!$object->save()) {
            $this->terminate();
        }
    }

    /**
     * @inheritDoc
     * @see Storage::setError()
     */
    public function setError($pid, $time)
    {
        $object = $this->getObject($pid);
        $object->status = AbstractTask::STATUS_ERROR;
        $object->time_finished = $time;

        if (!$object->save()) {
            $this->terminate();
        }
    }

    /**
     * @inheritDoc
     * @see Storage::setStarted()
     */
    public function setStarted($pid, $time)
    {
        $object = $this->getObject($pid);
        $object->status = AbstractTask::STATUS_RUN;
        $object->time_started = $time;

        if (!$object->save()) {
            $this->terminate();
        }
    }

    /**
     * @inheritDoc
     * @see Storage::addTaskRecord()
     */
    public function addTaskRecord($description)
    {
        $object = Record::factory('bgtask');
        $object->title = $description;
        $pid = $object->save();
        $this->objects[$pid] = $object;
        return $pid;
    }

    /**
     * Load Orm\Record
     * @param string $class
     * @param integer $pid
     */
    protected function getObject($pid)
    {
        if (!isset($this->objects[$pid])) {
            $this->objects[$pid] = Record::factory('bgtask', $pid);
        }

        return $this->objects[$pid];
    }
}