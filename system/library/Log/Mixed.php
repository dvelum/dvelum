<?php
/**
 * Смешаный лог, пытается писать в базу если не может то в файл
 * @author Kirill A Egorov 2014
 */
class Log_Mixed implements Log
{
    /**
     * @var Log_File
     */
    protected $_logFile;
    /**
     * @var Log_Db
     */
    protected $_logDb;

    public function __construct(Log_File $logFile , Log_Db $logDb)
    {
        $this->_logFile = $logFile;
        $this->_logDb = $logDb;
    }

    public function log($message)
    {
    	if(!$this->_logDb->log($message)){
    		$this->_logFile->log($message);
    		$this->_logFile->log($this->_logDb->getLastError());
    	}
    }
}