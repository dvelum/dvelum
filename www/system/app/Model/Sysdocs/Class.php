<?php
class Model_Sysdocs_Class extends Model
{
    /**
     * Get class hierarchy
     * @param integer $vers
     * @return Tree
     */
	public function getTree($vers)
	{
		$data = $this->getList(false , array('vers'=>$vers), array('id','parentId','name'));
		$tree = new Tree();

		if(!empty($data))
		{
		    foreach ($data as $k=>$v)
		    {
		      
		      if(empty($v['parentId']))
		        $v['parentId'] = 0;
		        
		      $tree->addItem($v['id'], $v['parentId'], $v['name']);
		    }
		}
	    return $tree;
	}
}