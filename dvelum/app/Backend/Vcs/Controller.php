<?php
class Backend_Vcs_Controller extends Backend_Controller{
	
    public function indexAction(){}
    
    public function listAction()
    {
        $object = Request::post('object', 'string' , false);
             
        if(!$object)
              Response::jsonSuccess(array());
    
        $pager = Request::post('pager', 'array', array());
        $filter = Request::post('filter', 'array', array());
        
        $filter['object_name'] = $object;
     
        $model= Model::factory('Vc');
        $data = $model->getListVc($pager , $filter, false, array('version','date','id','record_id'),'user_name');
    
        $result = array(
            'success'=>true,
            'count'=>$model->getCount($filter ),
            'data'=>$data
        );
        
        Response::jsonArray($result);             
   }
}