<?php
use Dvelum\Orm;

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
		$blockManager = new \Dvelum\App\BlockManager();
		$blockManager->invalidateCacheBlockId($object->getId());
	}
}