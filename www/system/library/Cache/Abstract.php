<?php
abstract class Cache_Abstract
{
	protected $_keyPrefix = '';
	protected $_normalizeKeys = false;
	
	protected $_opCounter = array(
				'load' => 0 , 
				'save' => 0 , 
				'remove' => 0
			  );

	protected function _id($key)
	{
		$key = $this->_keyPrefix . $key;
		
		if($this->_normalizeKeys)
			$key = $this->_normalizeKey($key);
		
		return $key;
	}

	protected function _normalizeKey($key)
	{
		return md5($key);
	}

	/**
	 * Get cache operations stats
	 * @return array
	 */
	public function getOperationsStat()
	{
		return $this->_opCounter;
	}
} 