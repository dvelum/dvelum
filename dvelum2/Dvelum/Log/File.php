<?php

namespace Dvelum\Log;

class File extends \Psr\Log\AbstractLogger implements \Log
{
    protected $file;

    /**
     * @param string $file - logfile path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    public function log($level, $message, array $context = array())
    {
        $message = '['.date('d.m.Y H:i:s') . '] ('.$level.') '. $message . ' '.json_encode($context)."\n";
        file_put_contents($this->file, $message , FILE_APPEND);
    }
}