<?php
class Model_Sysdocs_File extends Model
{
    /**
     * Get data for Tree.Panel
     * @param array $version
     */
    public function getTreeList($version)
    {
       /*
        * Add required fields
        */
        $fields = array('id','parentId','path','isDir','name','hid');
        $data = $this->getList(array('sort'=>array('isDir'=>'DESC','path','name'),'dir'=>'ASC'), array('vers'=>$version), $fields);

        if(empty($data))
            return array();
        
        $tree = new Tree();
        
        foreach($data as $value)
        {
            if(!$value['parentId'])
                $value['parentId'] = 0;
             
            $tree->addItem($value['id'], $value['parentId'], $value);
        }
         
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
    
            $obj->id = $row['id'];
            $obj->text = $row['name'];
            $obj->expanded = false;
            $obj->isDir = $row['isDir'];
            $obj->path = $row['path'];
            $obj->name = $row['name'];
            $obj->hid = $row['hid'];
            
            if($row['isDir'])
                $obj->leaf = false; 
            else 
                $obj->leaf = true;   
                       
            $cld= array();
            if($tree->hasChilds($row['id']))
                $cld = $this->_fillChilds($tree ,  $row['id']);
             
            $obj->children=$cld;
            $result[] = $obj;
        }
        return $result;
    }
}