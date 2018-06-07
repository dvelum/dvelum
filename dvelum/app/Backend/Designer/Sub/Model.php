<?php
class Backend_Designer_Sub_Model extends Backend_Designer_Sub
{
    
    /**
	 * @var Designer_Project
	 */
    protected $_project;
    /**
	 * @var Ext_Grid
	 */
    protected $_object;

    public function __construct()
    {
        parent::__construct();
        $this->_checkLoaded();
        $this->_checkObject();
    }

    protected function _checkObject()
    {
        $name = Request::post('object' , 'string' , '');
        $project = $this->_getProject();
        if(!strlen($name) || !$project->objectExists($name) || $project->getObject($name)
            ->getClass() !== 'Model')
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $this->_project = $project;
        $this->_object = $project->getObject($name);
    }

    public function importormfieldsAction()
    {
        $objectName = Request::post('objectName' , 'string' , false);
        $fields = Request::post('fields' , 'array' , false);
        
        $data = Backend_Designer_Import::checkImportORMFields($objectName , $fields);
        
        if(!$data)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        if(!empty($data))
		    foreach ($data as $field)
		        $this->_object->addField($field);
		
		$this->_storeProject();
		
		Response::jsonSuccess();
    }

    public function importdbfieldsAction()
    {
        $connectionId = Request::post('connectionId','string',false);
		$table = Request::post('table','string',false);
		$conType = Request::post('type', 'integer', false);
		$fields = Request::post('fields', 'array', false);
		
		if($connectionId === false || !$table || empty($fields) || $conType===false)
			Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $conManager = new \Dvelum\App\Backend\Orm\Connections($this->_configMain->get('db_configs'));
		$cfg = $conManager->getConnection($conType, $connectionId);	
		if(!$cfg)
		    Response::jsonError($this->_lang->WRONG_REQUEST);	
		$cfg = $cfg->__toArray();
        
        $data = Backend_Designer_Import::checkImportDBFields($cfg , $fields , $table);
        
        if(!$data)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        if(!empty($data))
		    foreach ($data as $field)
		        $this->_object->addField($field);
		
		$this->_storeProject();			
		Response::jsonSuccess();
    }

   /**
	* List fields
	*/
    public function listfieldsAction()
    {
        $this->_checkLoaded();
        $name = Request::post('object' , 'string' , '');
        
        $project = $this->_getProject();
        if(!strlen($name) || !$project->objectExists($name))
            Response::jsonError('Undefined model object');
        
        $this->_project = $project;
        $this->_object = $project->getObject($name);

        $result = array();
        $fields = $this->_object->getFields();

        foreach ($fields as $field)
        {
            if($field instanceof Ext_Object){
                $result[] = $field->getConfig()->__toArray(true);
            } elseif($field instanceof stdClass) {
                $result[] =  $field = get_object_vars($field);
            }
        }
        Response::jsonSuccess($result);
    }
    
    /**
     * Remove store field
     */
    public function removefieldAction()
    {
        $this->_checkLoaded();
        $this->_checkObject();
         
        $id = Request::post('id', 'string', false);
    
        if(!$id)
            Response::jsonError($this->_lang->FIELD_EXISTS);
    
        if($this->_object->fieldExists($id))
            $this->_object->removeField($id);
         
        $this->_storeProject();
         
        Response::jsonSuccess();
    }
    
    /**
     * Add store field
     */
    public function addfieldAction()
    {
        $this->_checkLoaded();
        $this->_checkObject();
         
        $id = Request::post('id', 'string', false);
    
        if(!$id || $this->_object->fieldExists($id))
            Response::jsonError($this->_lang->FIELD_EXISTS);
    
        if($this->_object->addField(array('name'=>$id,'type'=>'string'))){
            $o = $this->_object->getField($id);
            $this->_storeProject();
            Response::jsonSuccess(array('name'=>$o->name,'type'=>$o->type));
        }else{
            Response::jsonError($this->_lang->CANT_EXEC);
        }
    }
}