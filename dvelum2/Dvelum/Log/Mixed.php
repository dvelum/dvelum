<?php
namespace Dvelum\Log;

class Mixed extends \Psr\Log\AbstractLogger implements \Log
{
    /**
     * @var File
     */
    protected $logFile;
    /**
     * @var Db
     */
    protected $logDb;

    public function __construct(File $logFile , Db $logDb)
    {
        $this->logFile = $logFile;
        $this->logDb = $logDb;
    }

    public function log($level, $message, array $context = array())
    {
        if(!$this->logDb->log($level, $message, $context)){
            $this->logFile->log($level, $message, $context);
            $this->logFile->log(\Psr\Log\LogLevel::ERROR, $this->logDb->getLastError());
        }
    }
}