<?php

use Dvelum\Orm;
use Dvelum\Orm\Model;

class Model_Links extends Model
{
    /**
     * Clear object links
     * @param Orm\Record $object
     */
    public function clearObjectLinks(Orm\Record $object)
    {
        $this->_db->delete($this->table(),'src = '.$this->_db->quote($object->getName()).' AND src_id = '.intval($object->getId()));
    }
    /**
     * Clear links for object list
     * @param string $objectName
     * @param array $objectsIds
     */
    public function clearLinksFor($objectName , array $objectsIds)
    {
    	$this->_db->delete(
    		$this->table(),
    		'`src` = ' . $this->_db->quote($objectName).' 
    			AND
    		 `src_id` IN('.\Utils::listIntegers($objectsIds).')'
        );
    }
}