<?php
class Model_Mediacategory extends Model
{
  /**
   * Get categories tree
   * @return array
   */
  public function getCategoriesTree()
  {
    $categoryModel = Model::factory('Mediacategory');
    $data = $categoryModel->getList();
    $tree = new Tree();
  
    if(!empty($data))
    {
      foreach ($data as $k=>$v)
      {
        if(is_null($v['parent_id']))
          $v['parent_id'] = 0;
  
        $tree->addItem($v['id'], $v['parent_id'], $v);
      }
    }
  
    return $this->_fillCatChilds($tree , 0);
  }
  
  /**
   * Fill childs data array for tree panel
   * @param Tree $tree
   * @param mixed $root
   * @return array
   */
  protected function _fillCatChilds(Tree $tree , $root = 0 )
  {
    $result = array();
    $childs = $tree->getChilds($root);
     
    if(empty($childs))
      return array();
  
    foreach($childs as $k=>$v)
    {
      $row = $v['data'];
      $obj = new stdClass();
  
      $obj->id = $row['id'];
      $obj->text = $row['title'];
      $obj->expanded = !intval($root);
      $obj->leaf = false;
      $obj->allowDrag = true;
  
      $cld= array();
      if($tree->hasChilds($row['id']))
        $cld = $this->_fillCatChilds($tree ,  $row['id']);
       
      $obj->children=$cld;
      $result[] = $obj;
    }
    return $result;
  }
  
 /**
  * Update pages order_no
  * @param array $data
  */
  public function updateSortOrder(array $sortedIds)
  {
    $i=0;
    foreach ($sortedIds as $v)
    {
      $obj = new Db_Object($this->_name, intval($v));
      $obj->set('order_no', $i);
      $obj->save();
      $i++;
    }
  }
}