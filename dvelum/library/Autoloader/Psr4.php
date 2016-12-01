<?php

/**
 * Class Autoloader_Vendor
 * Autoloader plugin, PSR-4
 */
class Autoloader_Psr4
{
    protected $config;

    public function  __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Load class
     * @param $class
     * @return bool
     */
    public function load($class)
    {
        foreach ($this->config['paths'] as $prefix => $path)
        {
            if(strpos($class , $prefix) ===0)
            {
                $filePath = str_replace([$prefix,'\\'], [$path,'/'], $class).'.php';
                if(file_exists($filePath))
                {
                    require_once $filePath;
                    return true;
                }
            }
        }
        return false;
    }
}