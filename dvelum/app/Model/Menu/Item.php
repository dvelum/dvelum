<?php

use Dvelum\Orm;
use Dvelum\Orm\Model;

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
         $data = $this->query()
             ->params(['sort'=>'order','dir'=>'ASC'])
             ->filters(['menu_id'=>$menuId])
             ->fetchAll();

         
         if(empty($data))
             return [];

         $tree = new Tree(); 

         foreach($data as $value)
             $tree->addItem($value['tree_id'], $value['parent_id'], $value ,$value['order']);
         
         return $this->fillChildren($tree , 0);
	}
	
	/**
     * Fill children data array for tree panel
     * @param Tree $tree
     * @param mixed $root
     * @return array
     */
    protected function fillChildren(Tree $tree , $root = 0 )
    {
           $result = array();
            $children = $tree->getChilds($root);
               
           if(empty($children))
               return [];
                   
           foreach($children as $k=>$v)
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
                      $cld = $this->fillChildren($tree ,  $row['tree_id']);
                       
                   $obj->children=$cld;                                            
                   $result[] = $obj;
           }            
           return $result;     
    }
    /**
     * Update menu links
     * @param integer $objectId
     * @param array $links
     * @return bool
     */
    public function updateLinks($objectId, $links) : bool
    {  	
    	$this->db->delete($this->table() , 'menu_id = '.intval($objectId));
    	
    	if(!empty($links))
    	{
    		foreach($links as $k=>$item)	
    		{
                /**
                 * @var Orm\RecordInterface $obj
                 */
    			$obj = Orm\Record::factory('Menu_Item');

    			try{
    			    $obj->setValues([
    			        'tree_id' => $item['id'],
                        'page_id' => $item['page_id'],
                        'title' => $item['title'],
                        'published' => $item['published'],
                        'menu_id' => $objectId,
                        'parent_id' => $item['parent_id'],
                        'order' => $item['order'],
                        'link_type' => $item['link_type'],
                        'url' => $item['url'],
                        'resource_id' => $item['resource_id']
                    ]);

    				if(!$obj->save(false)){
    					throw new Exception(Lang::lang()->get('CANT_CREATE'));
    				}
    			}catch (Exception $e){
                    $this->logError($e->getMessage());
    				return false;
    			}
    		}	
    	}
    	return true;	
    }
    /**
     * Import Data from Site structure
     */
    public function exportSiteStructure() : array
    {
        $pageModel = Model::factory('page');
        $data = $pageModel->query()
            ->params(['sort'=>'order_no','dir'=>'DESC'])
            ->fields([
    			'tree_id'=>'id' ,
    			'title'=>'menu_title',
    			'page_id'=>$pageModel->getPrimaryKey(),
    			'published'=>'published',
    			'parent_id'=>'parent_id',
    			'order'=>'order_no'
    		])
            ->fetchAll();

    	    	
    	if(!$data)
    		return [];
    	
		$tree = new Tree();
    		
		foreach($data as $value){
    		 $value['link_type'] = 'page';
    		 $value['resource_id'] ='';
    		 $value['url']='';
             $tree->addItem($value['tree_id'], intval($value['parent_id']), $value ,$value['order']);
    	}
    	return $this->fillChildren($tree , 0);
    }
}