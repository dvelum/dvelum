<?php
use Dvelum\Orm;

class Trigger_Blocks extends Trigger
{
	public function onAfterAdd(Orm\Object $object)
	{
		parent::onAfterAdd($object);
		$this->clearBlockCache($object);
	}
	
	public function onAfterUpdate(Orm\Object $object)
	{
		parent::onAfterUpdate($object);
		$this->clearBlockCache($object);
	}
	
	public function onAfterDelete(Orm\Object $object)
	{
		parent::onAfterDelete($object);
		$this->clearBlockCache($object);
	}
	
	public function clearBlockCache(Orm\Object $object)
	{	
		$blockManager = new \Dvelum\App\BlockManager();
		$blockManager->invalidateCacheBlockId($object->getId());
	}
}