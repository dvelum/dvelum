<?php
use Dvelum\Orm;

class Trigger_Blocks extends Trigger
{
	public function onAfterAdd(Orm\ObjectInterface $object)
	{
		parent::onAfterAdd($object);
		$this->clearBlockCache($object);
	}
	
	public function onAfterUpdate(Orm\ObjectInterface $object)
	{
		parent::onAfterUpdate($object);
		$this->clearBlockCache($object);
	}
	
	public function onAfterDelete(Orm\ObjectInterface $object)
	{
		parent::onAfterDelete($object);
		$this->clearBlockCache($object);
	}
	
	public function clearBlockCache(Orm\ObjectInterface $object)
	{	
		$blockManager = new \Dvelum\App\BlockManager();
		$blockManager->invalidateCacheBlockId($object->getId());
	}
}