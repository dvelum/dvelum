<?php
/*
* DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
* Copyright (C) 2011-2014  Kirill A Egorov
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
 * Cronjob Base class
 * @author Kirill A Egorov
 * @abstract
 */
abstract class Cronjob_Abstract
{
    /**
     * Job config
     * @var Config_Abstract
     */
    protected $_config;
    /**
     * Job statistics
     * @var array
     */
    protected $_stat;

    /**
     * File lock
     * @var Cron_Lock
     */
    protected $_lock = false;

    /**
     * @param Config_Abstract $config
     */
    public function __construct(Config_Abstract $config)
    {
    	$this->_config = $config;
    	$this->_stat = array();
    	if($config->offsetExists('lock')){
    		$lock = $config->get('lock');
    		if($lock instanceof Cron_Lock){
    		    $this->_lock = $lock;
    		}
    	}
    	// Setting thread number
    	if(!$this->_config->offsetExists('thread')){
    		$this->_config->set('thread' , 0);
    	}
    }
    /**
     * Get job statistics
     * @return array
     */
    public function getStat()
    {
    	return $this->_stat;
    }
    /**
     * Get job statistics as string
     * (useful for logs)
     * @return string
     */
    public function getStatString()
    {
       $s = '';
       foreach ($this->_stat as $k=>$v)
           $s.= $k .' : '.$v.'; ';

       return $s;
    }
    /**
     * Launch job
     * @return boolean
     */
    public function run()
    {
        // Check file lock
        return $this->_checkCanLock();
    }

    protected function _checkCanLock()
    {
        if($this->_lock && !$this->_lock->launch(get_called_class().'-'.$this->_config->get('thread')))
        	return false;

        return true;
    }
    /**
     * Check file lock, execution time, sync
     */
    public function checkTimeLimit()
    {
    	if(!$this->_lock)
    	    return;

        $this->_lock->checkTimeLimit($this->getStatString());
    }
}