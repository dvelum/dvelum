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
 * 
 * ORM based tasks storage
 * @author Kirill A Egorov
 * @package Bgtask
 * @subpackage Bgtask_Storage
 */
class Bgtask_Storage_Orm extends Bgtask_Storage
{
	protected $_objects = array();
	
	/**
	 * @var Model
	 */
	protected $_objectModel;
	
	/**
	 * @var Model_Bgtask_Signal
	 */
	protected $_signalModel;
	
	/**
	 * @param Model $objectModel
	 * @param Model $signalModel
	 */
	public function __construct(Model $objectModel , Model $signalModel)
	{
		$this->_objectModel = $objectModel;
		$this->_signalModel = $signalModel;
	}	
	
	/**
	 * (non-PHPdoc)
	 * @see Bgtask_Storage::getList()
	 */
    public function getList()
    {
    	return $this->_objectModel->getList(false,false);
    }
    
    /**
     * (non-PHPdoc)
     * @see Bgtask_Storage::get()
     */
    public function get($pid)
    {
    	return $this->_objectModel->getItem($pid);
    }
    
    /**
     * (non-PHPdoc)
     * @see Bgtask_Storage::signal()
     */
    public function signal($pid , $signal)
    {
    	$object = new Db_Object('bgtask_signal');
    	$object->pid = $pid;
    	$object->signal = $signal;
    	return $object->save(false);  	
    }
    
    /**
     * (non-PHPdoc)
     * @see Bgtask_Storage::kill()
     */
     public function kill($pid)
     {
     	$this->_objectModel->remove($pid , false);
     	$this->_signalModel->clearSignals($pid);
     		
     	if(isset($this->_objects[$pid]))
     		unset($this->_objects[$pid]);
     			
     	return true;	
     }
     
    /**
     * (non-PHPdoc)
     * @see Bgtask_Storage::getSignals()
     */
     public function getSignals($pid , $clean = false)
     {
     	$signals = $this->_signalModel->getList(false , array('pid'=>$pid));
     	
     	$result = array();
     	if(!empty($signals))
     		$result = Utils::fetchCol('signal', $signals);	
     	if($clean)
     		$this->_signalModel->clearSignals($pid);
     	return $result;
     }
     
    /**
     * (non-PHPdoc)
     * @see Bgtask_Storage::clearSignals()
     */
    public function clearSignals($pid , $sigId = false)
    {
    	if($sigId){  	
    		$this->_signalModel->remove($sigId); 	
    	}else{
    		$this->_signalModel->clearSignals($pid);
    	}	
    }
    
     /**
      * (non-PHPdoc)
      * @see Bgtask_Storage::updateState()
      */
     public function updateState($pid ,$opTotal, $opFinished , $status , $memoryPeak , $memoryAllocated)
     {
     		$object = $this->_getObject($pid);
     		$object->memory_peak = $memoryPeak;
     		$object->memory = $memoryAllocated;
     		$object->op_total = $opTotal;
     		$object->op_finished = $opFinished;
     		$object->status = $status;
     		
     		if(!$object->save())
     			$this->_terminate();
     }
     
     /**
      * (non-PHPdoc)
      * @see Bgtask_Storage::isLive()
      */
     public function isLive($pid)
     {
     	if($this->_objectModel->getCount(array('id'=>$pid)))
     		return true;
     		
     	return false;
     }
     
     /**
      * (non-PHPdoc)
      * @see Bgtask_Storage::setFinished()
      */
     public function setFinished($pid , $time)
     {
     		$object = $this->_getObject($pid);
     		$object->status = Bgtask_Abstract::STATUS_FINISHED;
     		$object->time_finished = $time;
     		
     		if(!$object->save())
     			$this->_terminate();
     }
     
     /**
      * (non-PHPdoc)
      * @see Bgtask_Storage::setStoped()
      */
     public function setStoped($pid, $time)
     {
     		$object = $this->_getObject($pid);
     		$object->status = Bgtask_Abstract::STATUS_STOPED;
     		$object->time_finished = $time;
     		
     		if(!$object->save())
     			$this->_terminate();
     }
     
     /**
      * (non-PHPdoc)
      * @see Bgtask_Storage::setError()
      */
     public function setError($pid, $time)
     {
     		$object = $this->_getObject($pid);
     		$object->status = Bgtask_Abstract::STATUS_ERROR;
     		$object->time_finished = $time;
     		
     		if(!$object->save())
     			$this->_terminate();
     }
     
     /**
      * (non-PHPdoc)
      * @see Bgtask_Storage::setStarted()
      */
     public function setStarted($pid , $time)
     {  	
     		$object = $this->_getObject($pid);
     		$object->status = Bgtask_Abstract::STATUS_RUN;
     		$object->time_started = $time;

     		if(!$object->save()){
     			$this->_terminate();
     		}
     }
     
     /**
      * (non-PHPdoc)
      * @see Bgtask_Storage::addTaskRecord()
      */
     public function addTaskRecord($description)
     {
     		$object = new Db_Object('bgtask');
     		$object->title = $description;
     		$pid = $object->save();
     		$this->_objects[$pid] = $object;
     		return $pid;
     }
     
     /**
      * Load Db_Object
      * @param string $class
      * @param integer $pid
      */
     protected function _getObject($pid)
     {
     	if(!isset($this->_objects[$pid]))
     		$this->_objects[$pid] = new Db_Object('bgtask' , $pid);
     	
     	return $this->_objects[$pid];
     }
}