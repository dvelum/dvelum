<?php

namespace Dvelum\App\Form;

class Error
{
    protected $field = null;
    protected $message = null;
    protected $code = null;

    public function __construct($message, $field = null, $code = null)
    {
        $this->message = $message;
        $this->field = $field;
        $this->code = $code;
    }

    public function getField() : ?string
    {
        return $this->field;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getCode()
    {
        return $this->code;
    }
}