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
 * Abstract adapter for filestorage
 * @author Kirill A Egorov 2014
 */
abstract class Filestorage_Abstract
{
    const ERROR_CANT_WRITE_FS = 1;

    /**
     * Configuration object
     * @var Config_Abstract
     */
    protected $_config;

    /**
     * Loags adapter
     * @var Log
     */
    protected $_log = false;

    public function __construct(Config_Abstract $config)
    {
    	$this->_config = $config;
    }
    /**
     * Fileupload via POST and FILES
     * @throws Exception
     */
    abstract public function upload();
    /**
     * Remove file from storage
     * @param string $fileId
     * @return boolen
     */
    abstract public function remove($fileId);

    /**
     * Add file (copy to storage)
     * @param string $filePath
     * @param sting $useName, optional set specific file name
     * @throws Exception
     * @return array | boolean false - file info
     */
    abstract public function add($filePath , $useName);

    /**
     * Set logs adapter
     * @param Log $log
     */
    public function setLog(Log $log)
    {
    	$this->_log = $log;
    }
    /**
     * Log error
     * @param string $message
     */
    public function logError($message)
    {
    	if($this->_log)
    	    $this->_log->log('Filestorage ' . $message);
    }

    /**
     * @return Config_Abstract
     */
    public function getConfig()
    {
        return $this->_config;
    }
}