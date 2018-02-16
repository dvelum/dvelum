<?php

use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Service;

class Trigger_Menu extends Trigger
{
	public function onAfterDelete(Orm\RecordInterface $object)
	{		
		if(!$this->_cache)
			return;
			
		parent::onAfterDelete($object);			
		$this->clearBlockCache($object);
	}
	
	public function onAfterUpdate(Orm\RecordInterface $object)
	{
		if(!$this->_cache)
			return;
		
		parent::onAfterUpdate($object);	
		$this->clearBlockCache($object);	
	}

	public function clearBlockCache(Orm\RecordInterface $object)
	{
		if(!$this->_cache)
			return;
			
		$menuModel = Model::factory('Menu');
		$this->_cache->remove($menuModel->resetCachedMenuLinks($object->getId()));			
		$blockManager = Service::get('blockManager');
		$blockManager->invalidateCacheBlockMenu($object->getId());
	}
}