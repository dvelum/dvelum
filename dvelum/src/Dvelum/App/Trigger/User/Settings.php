<?php

namespace Dvelum\App\Trigger\User;

use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\App\Trigger;

class Settings extends Trigger
{
    public function onAfterAdd(Orm\RecordInterface $object)
    {
        parent::onAfterAdd($object);
        $this->clearCache($object);
    }

    public function onAfterUpdate(Orm\RecordInterface $object)
    {
        parent::onAfterUpdate($object);
        $this->clearCache($object);
    }

    public function onAfterDelete(Orm\RecordInterface $object)
    {
        parent::onAfterDelete($object);
        $this->clearCache($object);
    }

    public function clearCache(Orm\RecordInterface $object)
    {
        if ($this->cache) {
            $model = Model::factory('User_Settings');
            $this->cache->remove($model->getCacheKey(array('item', 'user', $object->get('user'))));
        }
    }
}