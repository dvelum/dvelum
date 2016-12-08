<?php
namespace Dvelum;

/**
 * Class Config
 * @package Dvelum
 * Backward compatibility
 */
class Config
{
    static public function storage()
    {
        return \Dvelum\Config\Factory::storage();
    }

    static public function factory($type, $name, $useCache = true)
    {
        return \Dvelum\Config\Factory::config($type, $name, $useCache);
    }
}