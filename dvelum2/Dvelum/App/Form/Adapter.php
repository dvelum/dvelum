<?php
namespace Dvelum\App\Form;

use Dvelum\Config;
use Dvelum\Request;

abstract class Adapter
{
    protected $request;

    protected $errors = [];

    /**
     * @var Config $config
     */
    protected $config;

    protected $lang;

    public function __construct( Request $request , \Dvelum\Lang $lang,  Config\ConfigInterface $config)
    {
        $this->lang = $lang;
        $this->request = $request;
        $this->config = $config;
    }

    abstract public function validateRequest() : bool;

    abstract public function getData();

    /**
     * Get list of errors
     * @return Form\Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}