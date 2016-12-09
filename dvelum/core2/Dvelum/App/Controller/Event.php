<?php
namespace Dvelum\App\Controller;

class Event
{
    public $type;

    protected $stop = false;

    protected $data;

    protected $error = false;
    protected $errorMessage = '';


    public function  stopPropagation()
    {
        $this->stop = true;
    }

    public function isPropagationStopped()
    {
        return $this->stop;
    }

    public function setError($message)
    {
        $this->stopPropagation();
        $this->error = true;
        $this->errorMessage = $message;
    }

    public function hasError()
    {
        return $this->error;
    }

    public function getError()
    {
        return $this->errorMessage;
    }

    public function setData(\stdClass $data)
    {
        $this->data = $data;
    }

    public function getData() : \stdClass
    {
        return $this->data;
    }
}
