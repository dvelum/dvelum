<?php
namespace Dvelum;

use Dvelum\Config;

/**
 * Class Request
 * @todo refactor! it's temporary realization
 * @package Dvelum
 */
class Request
{
    protected $config;

    protected $request;

    /**
     * @return Request
     */
    static public function factory()
    {
        static $instance = null;

        if(empty($instance)){
            $instance = new static();
        }

        return $instance;
    }

    private function __construct()
    {
        $this->request = \Request::getInstance();
    }


    /**
     * Set configuration options
     * @param Config\Config $config
     */
    public function setConfig(Config\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Set configuration option value
     * @param $name
     * @param $value
     */
    public function setConfigOption(string $name , $value)
    {
        $this->config->set($name, $value);
    }

    /**
     * @return array
     */
    public function postArray() : array
    {
        return \Request::postArray();
    }

    /**
     * @param $field
     * @param $type
     * @param $default
     * @return mixed
     */
    public function post($field, $type, $default)
    {
        return \Request::post($field, $type, $default);
    }

    /**
     * @param $field
     * @param $type
     * @param $default
     * @return mixed
     */
    public function get($field, $type, $default)
    {
        return \Request::post($field, $type, $default);
    }

    public function getPart($index)
    {
        return $this->request->getPart($index);
    }

    public function url(array $paths , $useExtension = true)
    {
        return \Request::url($paths , $useExtension);
    }

    public function extFilters()
    {
        return \Request::extFilters();
    }
}