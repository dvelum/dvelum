<?php
use Dvelum\Orm;
use Dvelum\Model;

class Trigger_Page extends Trigger
{
	public function onAfterAdd(Orm\Object $object)
	{
		parent::onAfterAdd($object);	
		$this->clearBlockCache($object->getId());
	}
	
	public function onAfterUpdate(Orm\Object $object)
	{
		parent::onAfterUpdate($object);
			
		$this->clearBlockCache($object->getId());
		$this->clearItemCache($object->code ,$object->getId());	
	}
	
	public function onAfterDelete(Orm\Object $object)
	{
		parent::onAfterDelete($object);
			
		$this->clearBlockCache($object->getId());
		$this->clearItemCache($object->code ,$object->getId());	
	}
	
	public function clearItemCache($code , $id)
	{
		if(!$this->_cache)
			return;

		$model = Model::factory('Page');
		$this->_cache->remove($model->getCacheKey(array('item', 'code', $code)));
		$this->_cache->remove(Model_Page::getCodeHash($id));
		$this->_cache->remove(Model_Page::getCodeHash($code));
		$bm = new Blockmanager();
		$bm->invalidatePageMap($id);
		$this->_cache->remove(Router_Module::CACHE_KEY_ROUTES);
	}
	
	public function clearBlockCache($pageId)
	{
		if($this->_cache){
			$bm = new Blockmanager();
			$this->_cache->remove($bm->hashPage($pageId));
			$this->_cache->remove(Model::factory('Page')->getCacheKey(array('codes')));
		}
	}
}