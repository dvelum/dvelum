<?php
class Trigger_Blocks extends Trigger
{
	public function onAfterAdd(Db_Object $object)
	{
		parent::onAfterAdd($object);
		$this->clearBlockCache($object);
	}
	
	public function onAfterUpdate(Db_Object $object)
	{
		parent::onAfterUpdate($object);
		$this->clearBlockCache($object);
	}
	
	public function onAfterDelete(Db_Object $object)
	{
		parent::onAfterDelete($object);
		$this->clearBlockCache($object);
	}
	
	public function clearBlockCache(Db_Object $object)
	{	
		$blockManager = new Blockmanager();
		$blockManager->invalidateCacheBlockId($object->getId());
	}
}