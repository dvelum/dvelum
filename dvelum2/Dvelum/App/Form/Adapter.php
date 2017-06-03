<?php
namespace Dvelum\App\Form;

use Dvelum\Config\ConfigInterface;
use Dvelum\Request;
use Dvelum\Lang;
use Dvelum\App\Form;

abstract class Adapter
{
    protected $request;

    protected $errors = [];

    /**
     * @var ConfigInterface $config
     */
    protected $config;

    protected $lang;

    public function __construct(Request $request , Lang\Dictionary $lang,  ConfigInterface $config)
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
    public function getErrors() :array
    {
        return $this->errors;
    }
}