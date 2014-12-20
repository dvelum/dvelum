<?php
class Trigger_Menu extends Trigger
{
	public function onAfterDelete(Db_Object $object)
	{		
		if(!$this->_cache)
			return;
			
		parent::onAfterDelete($object);			
		$this->clearBlockCache($object);
	}
	
	public function onAfterUpdate(Db_Object $object)
	{
		if(!$this->_cache)
			return;
		
		parent::onAfterUpdate($object);	
		$this->clearBlockCache($object);	
	}

	public function clearBlockCache(Db_Object $object)
	{
		if(!$this->_cache)
			return;
			
		$menuModel = Model::factory('Menu');
		$this->_cache->remove($menuModel->resetCachedMenuLinks($object->getId()));			
		$blockManager = new Blockmanager();
		$blockManager->invalidateCacheBlockMenu($object->getId());
	}
}