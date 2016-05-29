<?php
class Backend_Designer_Sub_Store extends Backend_Designer_Sub{

    /**
     * @var Designer_Project
     */
    protected $_project;
    /**
     * @var Ext_Store
     */
    protected $_object;


    protected function _checkObject()
    {
        $name = Request::post('object', 'string', '');
        $project = $this->_getProject();
        if(!strlen($name) || !$project->objectExists($name) || !in_array($project->getObject($name)->getClass() , Designer_Project::$storeClasses, true))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $this->_project = $project;
        $this->_object = $project->getObject($name);
    }

    public function importormfieldsAction()
    {
        $this->_checkLoaded();
        $this->_checkObject();
        $objectName = Request::post('objectName', 'string', false);
        $fields = Request::post('fields', 'array', false);

        $data = Backend_Designer_Import::checkImportORMFields($objectName, $fields);

        if(!$data)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(!empty($data))
            foreach ($data as $field)
                $this->_object->addField($field);

        $this->_storeProject();

        Response::jsonSuccess();
    }

    /**
     * Get list of store fields
     */
    public function storeFieldsAction()
    {
        $this->_checkLoaded();
        $name = Request::post('object', 'string', '');

        $name = trim(str_replace(Designer_Project_Code::$NEW_INSTANCE_TOKEN, '', $name));

        $project = $this->_getProject();

        if(!strlen($name) || !$project->objectExists($name))
            Response::jsonError('Undefined Store object');

        $this->_project = $project;

        $this->_object = $project->getObject($name);

        if($this->_object->isInstance())
            $this->_object = $this->_object->getObject();

        $fields = $this->_object->fields;

        if(is_string($fields)){
            $fields = json_decode($fields , true);
        }elseif(is_array($fields) && !empty($fields)){
            foreach ($fields as $name=>&$field){
                $field = $field->getConfig()->__toArray(true);
            }unset ($field);
        }
        Response::jsonSuccess($fields);
    }
    /**
     * Get list of store fields, include fields from model
     */
    public function listfieldsAction()
    {
        $this->_checkLoaded();
        $name = Request::post('object', 'string', '');

        $name = trim(str_replace(Designer_Project_Code::$NEW_INSTANCE_TOKEN, '', $name));

        $project = $this->_getProject();
        if(!strlen($name) || !$project->objectExists($name))
            Response::jsonError('Undefined Store object');

        $this->_project = $project;
        $this->_object = $project->getObject($name);
        if($this->_object->isInstance())
            $this->_object = $this->_object->getObject();
        $fields = array();

        $model = $this->_object->model;

        if(strlen($model)){
            $model = $this->_project->getObject($model);
            $fields = $model->fields;
        }

        if(empty($fields))
            $fields = $this->_object->fields;

        if(is_string($fields)){
            $fields = json_decode($fields , true);
        }elseif(is_array($fields) && !empty($fields)){
            foreach ($fields as $name=>&$field){
                $field = $field->getConfig()->__toArray(true);
            }unset ($field);
        }

        Response::jsonSuccess($fields);
    }

    public function allfieldsAction()
    {
        $this->_checkLoaded();
        $name = Request::post('object', 'string', '');

        $project = $this->_getProject();
        if(!strlen($name) || !$project->objectExists($name))
            Response::jsonError('Undefined Store object');

        $this->_project = $project;
        $this->_object = $project->getObject($name);
        if($this->_object->isInstance())
            $this->_object = $this->_object->getObject();
        $fields = array();

        if($this->_object->isValidProperty('model') && strlen($this->_object->model) && $this->_project->objectExists($this->_object->model))
        {
            $model = $this->_project->getObject($this->_object->model);

            if($model->isValidProperty('fields'))
            {
                $fields = $model->fields;
                if(is_string($fields))
                    $fields = json_decode($model->fields , true);
            }
        }

        if(empty($fields) && $this->_object->isValidProperty('fields'))
        {
            $fields = $this->_object->fields;

            if(empty($fields))
                $fields = array();

            if(is_string($fields))
                $fields = json_decode($fields , true);
        }

        $data = array();
        if(!empty($fields))
        {
            foreach ($fields as $item)
                if(is_object($item))
                    $data[] = array('name'=>$item->name , 'type'=>$item->type);
                else
                    $data[] = array('name'=>$item['name'],'type'=>$item['type']);
        }

        Response::jsonSuccess($data);
    }

    public function importdbfieldsAction(){
        $this->_checkLoaded();
        $this->_checkObject();
        $connectionId = Request::post('connectionId','string',false);
        $table = Request::post('table','string',false);
        $conType = Request::post('type', 'integer', false);
        $fields = Request::post('fields', 'array', false);

        if($connectionId === false || !$table || empty($fields) || $conType===false)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $conManager = new Backend_Orm_Connections_Manager($this->_configMain->get('db_configs'));
        $cfg = $conManager->getConnection($conType, $connectionId);
        if(!$cfg)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        $cfg = $cfg->__toArray();

        $data = Backend_Designer_Import::checkImportDBFields($cfg, $fields, $table);

        if(!$data)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(!empty($data))
            foreach ($data as $field)
                $this->_object->addField($field);

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
}