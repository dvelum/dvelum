<?php
class Backend_Blocks_Controller extends Backend_Controller_Crud_Vc
{
    /**
     * (non-PHPdoc)
     *
     * @see Backend_Controller::indexAction()
     */
    public function indexAction()
    {
        $this->_resource->addJs('/js/app/system/Blocks.js' , true , 1);
        parent::indexAction();
    }

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

        $filters = false;

        if($this->_user->onlyOwnRecords($this->_module)){
            $filters['author_id'] = $this->_user->getId();
        }

        $data = $dataModel->getListVc($pager , $filters, $query, $fields, 'user','updater');
        
        if(empty($data))
            Response::jsonArray($result);
    
        $ids = array(); 
        foreach ($data as $k=>$v)
            $ids[] = $v['id'];
           
        $maxRevisions = $vc->getLastVersion('blocks', $ids);
        
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
    public function classListAction()
    {	
    	$blocksPath = $this->_configMain['blocks'];
        $filePath = $this->_configMain->get('autoloader');
        $filePath = $filePath['paths'];

        $classes = [];
        foreach($filePath as $path)
        {
            if(is_dir($path.'/'.$blocksPath))
            {
                $files = File::scanFiles($path.'/'.$blocksPath , array('.php'), true , File::Files_Only);
                foreach ($files as $k=>$file)
                {
                    $class = Utils::classFromPath(str_replace($path, '',$file));
                    if($class != 'Block_Abstract')
                        $classes[$class] = ['id'=>$class,'title'=>$class];
                }
            }
        }
    	Response::jsonSuccess(array_values($classes));
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


    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Blocks.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}