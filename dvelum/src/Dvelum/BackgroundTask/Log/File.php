<?php
namespace Dvelum\BackgroundTask\Log;
use Dvelum\BackgroundTask\Log;

class File extends Log
{
	protected $file;
	
	/**
	 * @param string $file - logfile path
	 */
	public function __construct($file)
	{
		$this->file = $file;
	}
	
	/**
	 * Log
	 * @param string $message
	 */
	public function log($message){
		$message = '['.date('d.m.Y H:i:s') . '] '. $message . "\n";
		file_put_contents($this->file, $message , FILE_APPEND);
	}
}