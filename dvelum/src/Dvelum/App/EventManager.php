<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2019  Kirill Yegorov
 *  
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\App;

use Dvelum\Orm;
use Dvelum\Cache\CacheInterface;
use Dvelum\Utils\Strings;
use Dvelum\App\Trigger;
/**
 * Manager for Db_Object events
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2012  Kirill A Egorov,
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
 * @license General Public License version 3
 */
class EventManager extends Orm\Record\Event\Manager
{
    protected $cache;

    /**
     * Set cache adapter
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * (non-PHPdoc)
     * @see Db_Object_Event_Manager::fireEvent()
     */
    public function fireEvent(string $code, Orm\RecordInterface $object)
    {
        $objectName = ucfirst($object->getName());

        $name = explode('_', $objectName);
        $name = array_map('ucfirst', $name);

        $triggerClass = Strings::classFromString('\\Dvelum\\App\\Trigger\\' . implode('\\', $name));
        $namespacedClass =  Strings::classFromString('\\App\\Trigger\\' . implode('\\', $name));

        if(class_exists($triggerClass) && method_exists($triggerClass, $code))
        {
            $trigger = new $triggerClass();
            if($this->cache){
                $trigger->setCache($this->cache);
            }

            $trigger->$code($object);
        }elseif (class_exists($namespacedClass) && method_exists($namespacedClass, $code)){
            $trigger = new $namespacedClass();
            if($this->cache){
                $trigger->setCache($this->cache);
            }
            $trigger->$code($object);
        }
        elseif(method_exists('\\Dvelum\\App\\Trigger', $code))
        {
            $trigger = new Trigger();
            if($this->cache){
                $trigger->setCache($this->cache);
            }
            $trigger->$code($object);
        }
    }
}