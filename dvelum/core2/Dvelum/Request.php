<?php
namespace Dvelum;

/**
 * Class Request
 * @todo refactor! it's temporary realization
 * @package Dvelum
 */
class Request
{
    /**
     * @return Request
     */
    static public function factory()
    {
        static $request = null;

        if(empty($request)){
            $request = new static();
        }

        return $request;
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
}