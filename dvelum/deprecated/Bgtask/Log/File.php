<?php
class Bgtask_Log_File extends Bgtask_Log
{
	protected $_file;
	
	/**
	 * @param string $file - logfile path
	 */
	public function __construct($file)
	{
		$this->_file = $file;
	}
	
	/**
	 * Log
	 * @param string $message
	 */
	public function log($message){
		$message = '['.date('d.m.Y H:i:s') . '] '. $message . "\n";
		file_put_contents($this->_file, $message , FILE_APPEND);
	}
}