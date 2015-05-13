<?php
/**
 * Pages Model
 * @author Kirill Egorov 2011
 */
class Model_Page extends Model
{
	/**
     * Get Pages tree
     * @return array
     */
    public function getTreeList(array $fields , $author=false , $publisher=false)
    {
        /*
         * Add the required fields to the list
         */
         $fields = array_unique(array_merge(array('id','parent_id','order_no','code','menu_title','is_fixed'),$fields));
         $data = $this->getListVc(array('sort'=>'order_no','dir'=>'ASC'), false, false, $fields , $author , $publisher);

         if(empty($data))
             return array();

         $ids = Utils::fetchCol('id', $data);
         $vc = Model::factory('Vc');
         $maxRevisions = $vc->getLastVersion('page' , $ids);

         foreach ($data as $k=>&$v){
            if(isset($maxRevisions[$v['id']]))
                $v['last_version'] = $maxRevisions[$v['id']];
            else
                $v['last_version'] = 0;
         }unset($v);

          if(empty($data))
               return array();

         $tree = new Tree();

         foreach($data as $value)
         {
             if(!$value['parent_id'])
                 $value['parent_id'] = 0;

             $tree->addItem($value['id'], $value['parent_id'], $value ,$value['order_no']);
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

           $appConfig = Registry::get('main' , 'config');

           foreach($childs as $k=>$v)
           {
                  $row = $v['data'];
                  $obj = new stdClass();

                  $obj->id = $row['id'];
                  $obj->text = $row['menu_title'] . ' <i>('.$row['code'].$appConfig['urlExtension'].')</i>';
                  $obj->expanded = true;
                  $obj->leaf = false;

                  if($row['published']){
                      $obj->qtip = $row['menu_title'] . ' <i>('.$row['code'].$appConfig['urlExtension'].')</i> published';
                      $obj->iconCls = 'pagePublic';
                  } else {
                      $obj->qtip = $row['menu_title'] . ' <i>('.$row['code'].$appConfig['urlExtension'].')</i> not published';
                      $obj->iconCls = 'pageHidden';
                  }

                   if($row['is_fixed'])
                       $obj->allowDrag = false;

                   $cld= array();
                   if($tree->hasChilds($row['id']))
                      $cld = $this->_fillChilds($tree ,  $row['id']);

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
            foreach ($sortedIds as $v){
            	$obj = new Db_Object($this->_name, intval($v));
				$obj->set('order_no', $i);
				$obj->save();

                $i++;
            }
    }

    /**
     * Check if page code exists
     * @param string $code
     */
     public function codeExists($code)
     {
          return  $this->_dbSlave->fetchOne($this->_dbSlave->select()
                                ->from($this->table() , array('count'=>'COUNT(*)'))
                                ->where('code = ?' , $code)
                  );
    }

    /**
     * Get topic item ID by its code
     * @param string $code
     * @return integer;
     */
    public function getIdByCode($code)
    {
         $recId = $this->_dbSlave->fetchOne(
                $this->_dbSlave->select()
                          ->from($this->table(), array('id'))
                          ->where('code =?',$code)
         );
         return intval($recId);
    }


    /**
     * Get hash for pagecode
     * @param string $code
     */
    static public function getCodeHash($code){
    	return md5('page_'.$code);
    }
    /**
     * Reset childs elements set parent 0
     * @param page $id
     */
    public function resetChilds($id)
    {
    	$obj = new Db_Object($this->_name, intval($id));
		$obj->set('parent_id', 0);
		$obj->save();
    }

    /**
     * Find page code by attached module
     * @param string $name
     * @return mixed (string / false)
     */
    public function getCodeByModule($name)
    {
    	 $data = $this->_dbSlave->fetchOne(
			        $this->_dbSlave->select()
			                ->from($this->table(),array('code'))
			                ->where('`func_code` = ?' ,$name)
			                ->order('published DESC')
		);
		if($data)
			return $data;
		else
			return false;
    }
    /**
     * Get page codes
     * @return array
     */
    public function getCachedCodes()
    {
    	$codes = false;

    	if($this->_cache){
    		$cacheKey = $this->getCacheKey(array('codes'));
    		$codes = $this->_cache->load($cacheKey);
    	}

    	if($codes)
    		return $codes;

    	$codes = $this->getList(false,false,array('id' , 'code'));
    	if(!empty($codes))
    		$codes = Utils::collectData('id', 'code', $codes);
		else
			$codes = array();

		if($this->_cache)
            $this->_cache->save($codes, $cacheKey);

		return $codes;
    }
    /**
     * Get id's of page with default blocks map
     * @return array:
     */
    public function getPagesWithDefaultMap()
    {
    	$ids = $this->getList(false, array('default_blocks'=>1),array('id'));

    	if(!empty($ids))
	    	return Utils::fetchCol('id' , $ids);
    	else
    		return array();
    }

    /**
     * Get pages Tree
     * @return Tree
     */
    public function getTree()
    {
        static $tree = false;

        if($tree instanceof Tree)
            return $tree;

        if($this->_cache){
            $cacheKey = $this->getCacheKey(array('pages_tree_data'));
            $tree = $this->_cache->load($cacheKey);
        }

        if($tree instanceof Tree)
            return $tree;

        $fields = array('id','parent_id','order_no','code','menu_title','is_fixed','published','in_site_map');
        $data = $this->getListVc(array('sort'=>'order_no','dir'=>'ASC'), false, false, $fields , false , false);

        $tree = new Tree();
        if(!empty($data))
            foreach ($data as $k=>$v)
            $tree->addItem($v['id'], $v['parent_id'], $v);

        if($this->_cache)
            $this->_cache->save($tree, $cacheKey);

        return $tree;
    }
}