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
     * Get list of object store fields
     */
    public function listStoreFieldsAction()
    {
        $this->_checkLoaded();
        $name = Request::post('object', 'string', '');

        $project = $this->_getProject();
        $object = $project->getObject($name);

        $store = $object->store;

        if($store instanceof Ext_Helper_Store){
            if($store->getType() == Ext_Helper_Store::TYPE_JSCODE){
                Response::jsonSuccess([]);
            }else{
                $store = $store->getValue();
            }
        }
        $store = trim(str_replace(Designer_Project_Code::$NEW_INSTANCE_TOKEN, '', $store));

        if(!strlen($store) || !$project->objectExists($store)){
            Response::jsonError('Undefined store object');
        }

        $store = $project->getObject($store);
        Response::jsonSuccess($this->prepareList($store));
    }

    protected function prepareList(Ext_Object $object) : array
    {
        if($object->isInstance()){
            $object = $object->getObject();
        }

        $fields = [];

        // Do not show model fields. It cause misleading
        //        $model = $object->model;
        //
        //        if(strlen($model)){
        //            $model = $project->getObject($model);
        //            $fields = $model->fields;
        //        }

        if(empty($fields))
            $fields = $object->fields;

        if(is_string($fields)){
            $fields = json_decode($fields , true);
        }elseif(is_array($fields) && !empty($fields)){
            foreach ($fields as $name=>&$field){
                $field = $field->getConfig()->__toArray(true);
            }unset ($field);
        }
        return $fields;
    }
    /**
     * Get list of store fields
     */
    public function listfieldsAction()
    {
        $this->_checkLoaded();
        $name = Request::post('object', 'string', '');

        $name = trim(str_replace(Designer_Project_Code::$NEW_INSTANCE_TOKEN, '', $name));

        $project = $this->_getProject();
        if(!strlen($name) || !$project->objectExists($name))
            Response::jsonError('Undefined Store object');

        $object = $project->getObject($name);

        Response::jsonSuccess($this->prepareList($object));
    }

    /**
     * @param $store
     * @param Designer_Project $project
     * @return array
     */
    protected function extractFields($store, Designer_Project $project) : array
    {
        if (empty($store)) {
            return [];
        }

        if ($store->isInstance()) {
            $store = $store->getObject();
        }

        $fields = [];

        if ($store->isValidProperty('fields')) {
            $fields = $store->fields;

            if (empty($fields)) {
                $fields = [];
            }

            if (is_string($fields)) {
                $fields = json_decode($fields, true);
            }
        }

        if ($store->isValidProperty('model') && strlen($store->model) && $project->objectExists($store->model)) {
            $model = $project->getObject($store->model);

            if ($model->isValidProperty('fields')) {
                $modelFields = $model->fields;

                if (is_string($modelFields)) {
                    $modelFields = json_decode($modelFields, true);
                }

                if (!empty($modelFields)) {
                    $fields = array_merge($fields, $modelFields);
                }
            }
        }

        $data = [];
        if (!empty($fields)) {
            foreach ($fields as $item) {
                if (is_object($item)) {
                    $data[] = ['name' => $item->name, 'type' => $item->type];
                } else {
                    $data[] = ['name' => $item['name'], 'type' => $item['type']];
                }
            }

        }
        return $data;
    }

    public function allStoreFieldsAction()
    {
        $this->_checkLoaded();
        $name = Request::post('object', 'string', '');

        $project = $this->_getProject();

        if(!strlen($name) || !$project->objectExists($name)){
            Response::jsonError('Undefined object');
        }

        $object = $project->getObject($name);
        $store = $object->store;

        if($store instanceof Ext_Helper_Store){
            if($store->getType() == Ext_Helper_Store::TYPE_JSCODE){
                Response::jsonSuccess([]);
            }else{
                $store = $store->getValue();
            }
        }
        $store = trim(str_replace(Designer_Project_Code::$NEW_INSTANCE_TOKEN, '', $store));

        if(!strlen($store) || !$project->objectExists($store)){
            Response::jsonError('Undefined stroe object');
        }

        $store = $project->getObject($store);
        Response::jsonSuccess($this->extractFields($store, $project));
    }
    public function allFieldsAction()
    {
        $this->_checkLoaded();
        $name = Request::post('object', 'string', '');

        $name = trim(str_replace('[new:]','',$name));

        $project = $this->_getProject();

        if(!strlen($name) || !$project->objectExists($name))
            Response::jsonError('Undefined Store object');

        $store = $project->getObject($name);

        Response::jsonSuccess($this->extractFields($store, $project));
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

        $conManager = new \Dvelum\Db\Manager($this->_configMain);

        try{
            $db = $conManager->getDbConnection($connectionId, $conType);
        }catch (Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST);
            return;
        }

        $data = Backend_Designer_Import::checkImportDBFields($db, $fields, $table);

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