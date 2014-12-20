<?php
class Model_Blockmapping extends Model
{
 	/**
     * Clear block map for page by page ID
     * @param integer $pageId
     * @return void
     */
    public function clearMap($pageId)
    {
        if(!$pageId)
          $this->_db->delete($this->table(),' `page_id` IS NULL');
        else 
          $this->_db->delete($this->table(),' `page_id` = ' . intval($pageId));
    }
    /**
     * Add block map for page
     * @param integer $pageId
     * @param string $code
     * @param array $blockIds
     */
    public function addBlocks($pageId , $code , array $blockIds)
    {
        if(empty($blockIds))
            return true;
            
        $order = 0;
        foreach ($blockIds as $id)
        {
            $blockmapItem = new Db_Object('blockmapping');
            $blockmapItem->set('block_id' , $id);
            if($pageId)
              $blockmapItem->set('page_id' , $pageId);
            
            $blockmapItem->set('place' , $code);
            $blockmapItem->set('order_no' , $order);
            $blockmapItem->save(false);
            $order++;
        }    
        return true;    
    }
}