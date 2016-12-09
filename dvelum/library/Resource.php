<?php
class Resource
{
    /**
     * Backward compatibility
     * @return \Dvelum\Resource
     */
	public static function getInstance()
	{
		return \Dvelum\Resource::factory();
	}
}