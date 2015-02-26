<?php
class Filestorage
{
    /**
     * @param string $adapter
     * @param Config_Abstract $config
     * @return Filestorage_Abstract
     */
	static public function factory($adapter , Config_Abstract $config)
	{
	    $adapter = 'Filestorage_'.ucfirst($adapter);
        return new $adapter($config);
	}
}