<?php
/**
 * Class Resource
 * Backward compatibility
 * @deprecated
 */
class Resource
{
    /**
     * @return \Dvelum\Resource
     */
	public static function getInstance()
	{
		return \Dvelum\Resource::factory();
	}
}