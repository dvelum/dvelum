<?php
class Model_Links extends Model
{
    /**
     * Clear object links
     * @param Db_Object $object
     */
    public function clearObjectLinks(Db_Object $object)
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
    		 `src_id` IN('.Model::listIntegers($objectsIds).')'
        );
    }
}