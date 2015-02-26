<?php
class Backend_Blocks_Controller extends Backend_Controller_Crud_Vc{

    public function listAction()
    {           	
        $pager = Request::post('pager', 'array', array());
        $query = Request::post('search', 'string', false);
     
        $result = array('success'=>true, 'count'=>0, 'data'=>array());
    
        $dataModel = Model::factory('Blocks');
        $vc = Model::factory('Vc');
        
        $fields = array(
            'id' ,
            'title',
            'date_created',
            'published' , 
            'published_version',
            'date_updated',
        	'is_system',
        	'sys_name',
        	'params'
        );

        $data = $dataModel->getListVc($pager , false, $query, $fields, 'user','updater');
        
        if(empty($data))
            Response::jsonArray($result);
    
        $ids = array(); 
        foreach ($data as $k=>$v)
            $ids[] = $v['id'];
           
        $maxRevisions = $vc->getLastVersion('blocks',$ids);
        
        foreach ($data as $k=>&$v)
        {
            if(isset($maxRevisions[$v['id']]))
                $v['last_version'] = $maxRevisions[$v['id']];
            else
                $v['last_version'] = 0;   
        } 
        unset($v);
        
        $result = array(
            'success'=>true,
            'count'=>$dataModel->getCount(array('is_system'=>0) , $query),
            'data'=>$data
        );
        Response::jsonArray($result);
    }
    
 	/**
     * List defined Blocks
     */
    public function classlistAction()
    {	
    	$blocksPath = $this->_configMain['blocks'];
    	$classesPath = $this->_configMain['application_path'];
    	$files = File::scanFiles($blocksPath , array('.php'), true , File::Files_Only);
    	
    	foreach ($files as $k=>$file)
    	{
    		$class = Utils::classFromPath(str_replace($classesPath, '',$file));
    		if($class != 'Block_Abstract')
    			$data[] = array('id'=>$class,'title'=>$class);
    	}
    	
    	if($this->_configMain->get('allow_externals'))
    	{
    		$config = Config::factory(Config::File_Array, $this->_configMain->get('configs') . 'externals.php');
    		$eExpert = new Externals_Expert($this->_configMain, $config);
    		$extBlocks = $eExpert->getBlocks();
    			
    		if(!empty($extBlocks))
    			foreach ($extBlocks as $class=>$path)
    				$data[] = array('id'=>$class,'title'=>$class);
    	}
    	
    	Response::jsonSuccess($data);
    }
    
    /**
     * Get list of accepted menu
     */
    public function menulistAction()
    {
    	$menuModel = Model::factory('menu');
    	$list = $menuModel->getList(false,false, array('id' , 'title'));
    	
    	if(!empty($list))
    		$list = array_values($list);
    	
    	Response::jsonSuccess($list);
    }
}