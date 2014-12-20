<?php
class Trigger
{
	/**
	 * @var Cache_Abstract | false
	 */
	protected $_cache = false;

	public function setCache(Cache_Abstract $cache)
	{
		$this->_cache = $cache;
	}


	protected function _getItemCacheKey(Db_Object $object)
	{
		$objectModel = Model::factory($object->getName());
		return $objectModel->getCacheKey(array('item',$object->getId()));
	}

	public function onBeforeAdd(Db_Object $object)
	{

	}

	public function onBeforeUpdate(Db_Object $object)
	{

	}

	public function onBeforeDelete(Db_Object $object)
	{

	}

	public function onAfterAdd(Db_Object $object)
	{
		if(!$this->_cache)
			return;

		$this->_cache->remove($this->_getItemCacheKey($object));
	}

	public function onAfterUpdate(Db_Object $object)
	{
		if(!$this->_cache)
			return;

		$this->_cache->remove($this->_getItemCacheKey($object));
	}

	public function onAfterDelete(Db_Object $object)
	{
		if(!$this->_cache)
			return;

		$this->_cache->remove($this->_getItemCacheKey($object));
	}

	public function onAfterUpdateBeforeCommit(Db_Object $object)
	{

	}

}