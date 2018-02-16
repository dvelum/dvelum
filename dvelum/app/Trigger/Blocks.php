<?php
use Dvelum\Orm;
use Dvelum\Service;

class Trigger_Blocks extends Trigger
{
	public function onAfterAdd(Orm\RecordInterface $object)
	{
		parent::onAfterAdd($object);
		$this->clearBlockCache($object);
	}
	
	public function onAfterUpdate(Orm\RecordInterface $object)
	{
		parent::onAfterUpdate($object);
		$this->clearBlockCache($object);
	}
	
	public function onAfterDelete(Orm\RecordInterface $object)
	{
		parent::onAfterDelete($object);
		$this->clearBlockCache($object);
	}
	
	public function clearBlockCache(Orm\RecordInterface $object)
	{	
		$blockManager = Service::get('blockManager');
		$blockManager->invalidateCacheBlockId($object->getId());
	}
}