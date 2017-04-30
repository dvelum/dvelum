<?php

use Dvelum\Orm;
use Dvelum\Orm\Model;

class Trigger_Menu extends Trigger
{
	public function onAfterDelete(Orm\Object $object)
	{		
		if(!$this->_cache)
			return;
			
		parent::onAfterDelete($object);			
		$this->clearBlockCache($object);
	}
	
	public function onAfterUpdate(Orm\Object $object)
	{
		if(!$this->_cache)
			return;
		
		parent::onAfterUpdate($object);	
		$this->clearBlockCache($object);	
	}

	public function clearBlockCache(Orm\Object $object)
	{
		if(!$this->_cache)
			return;
			
		$menuModel = Model::factory('Menu');
		$this->_cache->remove($menuModel->resetCachedMenuLinks($object->getId()));			
		$blockManager =  new \Dvelum\App\BlockManager();
		$blockManager->invalidateCacheBlockMenu($object->getId());
	}
}