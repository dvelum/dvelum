<?php
/**
 * Manager for Db_Object events
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2012  Kirill A Egorov,
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 */

use Dvelum\Orm;
use Dvelum\Cache\CacheInterface;

class Eventmanager extends Orm\Record\Event\Manager
{
    protected $_cache;

    /**
     * Set cache adapter
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * (non-PHPdoc)
     * @see Db_Object_Event_Manager::fireEvent()
     */
    public function fireEvent(string $code, Orm\RecordInterface $object)
    {
        $objectName = ucfirst($object->getName());
        $triggerClass = \Utils_String::classFromString('Trigger_' . $objectName);

        if(class_exists($triggerClass) && method_exists($triggerClass, $code))
        {
            $trigger = new $triggerClass();
            if($this->_cache)
                $trigger->setCache($this->_cache);
            $trigger->$code($object);
        }
        elseif(method_exists('Trigger', $code))
        {
            $trigger = new \Trigger();
            if($this->_cache)
                $trigger->setCache($this->_cache);
            $trigger->$code($object);
        }
    }
}