<?php
/**
 * Menu item model
 */
class Model_Menu_Item extends Model
{
	/**
	 * Get data for menu tree
	 * @param integer $menuId
	 * @return array
	 */
	public function getTreeList($menuId)
	{
         $data = $this->getList(
         	array('sort'=>'order','dir'=>'ASC'),
         	array('menu_id'=>$menuId)
         );
         
         if(empty($data))
             return array();

         $tree = new Tree(); 
         
         foreach($data as $value)
             $tree->addItem($value['tree_id'], $value['parent_id'], $value ,$value['order']);
         
         return $this->_fillChilds($tree , 0);
	}
	
	/**
     * Fill childs data array for tree panel
     * @param Tree $tree
     * @param mixed $root
     * @return array
     */
    protected function _fillChilds(Tree $tree , $root = 0 )
    {
           $result = array();   
           $childs = $tree->getChilds($root);      
               
           if(empty($childs))
               return array();
                   
           foreach($childs as $k=>$v)
           {
                  $row = $v['data'];                            
                  $obj = new stdClass();
 
                  $obj->id = $row['tree_id'];  
                  $obj->text= $row['title'];
                  $obj->expanded= true;
                  $obj->leaf = false;
                  $obj->allowDrag = true;
                  $obj->page_id = $row['page_id'];
                  $obj->parent_id = $row['parent_id'];
                  $obj->published = $row['published'];
                  $obj->url = isset($row['url']) ? $row['url'] : '';
                  $obj->resource_id = isset($row['resource_id']) ? $row['resource_id'] : '';
                  $obj->link_type = isset($row['link_type']) ? $row['link_type'] : '';

			  
                  if($row['published'])
                      $obj->iconCls = 'pagePublic';
                  else 
                      $obj->iconCls = 'pageHidden';
                  
                       
                   $cld= array();
                   
                   if($tree->hasChilds($row['tree_id']))
                      $cld = $this->_fillChilds($tree ,  $row['tree_id']);
                       
                   $obj->children=$cld;                                            
                   $result[] = $obj;
           }            
           return $result;     
    }
    /**
     * Update menu links
     * @param integer $objectId
     * @param array $links
     * @return boolean
     */
    public function updateLinks($objectId, $links)
    {  	
    	$this->_db->delete($this->table() , 'menu_id = '.intval($objectId));
    	
    	if(!empty($links))
    	{
    		foreach($links as $k=>$item)	
    		{
    			$obj = new Db_Object('Menu_Item');
    			try{  				
    				$obj->tree_id = $item['id'];
    				$obj->page_id = $item['page_id'];
    				$obj->title = $item['title'];
    				$obj->published = $item['published'];
    				$obj->menu_id = $objectId;
    				$obj->parent_id = $item['parent_id'];
    				$obj->order = $item['order'];
    				$obj->link_type = $item['link_type'];
    				$obj->url = $item['url'];
    				$obj->resource_id = $item['resource_id'];
    				if(!$obj->save(false)){
    					throw new Exception(Lang::lang()->CANT_CREATE);
    				}
    				
    			}catch (Exception $e){
    				return false;
    			}
    		}	
    	}
    	return true;	
    }
    /**
     * Import Data from Site structure
     */
    public function exportsiteStructure()
    {
        $pageModel = Model::factory('page');
    	$data = $pageModel->getList(
    		array('sort'=>'order_no','dir'=>'DESC'),
    		false,
    		array(
    			'tree_id'=>'id' ,
    			'title'=>'menu_title',
    			'page_id'=>$pageModel->getPrimaryKey(),
    			'published'=>'published',
    			'parent_id'=>'parent_id',
    			'order'=>'order_no'
    		)
    	);
    	    	
    	if(!$data)
    		return array();
    	
		$tree = new Tree();
    		
		foreach($data as $value){
    		 $value['link_type'] = 'page';
    		 $value['resource_id'] ='';
    		 $value['url']='';
             $tree->addItem($value['tree_id'], intval($value['parent_id']), $value ,$value['order']);
    	}
    	return $this->_fillChilds($tree , 0);
    }
}