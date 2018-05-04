<?php
class Backend_Designer_Sub_Properties extends Backend_Designer_Sub
{
    /**
     * Get object properties
     */
    public function listAction()
    {
        $this->_checkLoaded();
        $object = $this->_getObject();

        $class = $object->getClass();
        $properties = $object->getConfig()->__toArray();

        /*
         * Hide unused properties
         */
        switch ($class){
            case 'Docked':
                unset($properties['items']);
                break;
            case 'Object_Instance':
                unset($properties['defineOnly']);
                unset($properties['listeners']);
                break;
        }
        //unset($properties['isExtended']);
        unset($properties['extend']);

        if(isset($properties['dockedItems']))
            unset($properties['dockedItems']);

        if(isset($properties['menu']))
            unset($properties['menu']);

        if(isset($properties['store']))
            $properties['store'] = '';

        Response::jsonSuccess($properties);
    }
    /**
     * Set object property
     */
    public function setpropertyAction()
    {
        $this->_checkLoaded();

        $object = $this->_getObject();
        $project = $this->_getProject();

        $property = Request::post('name', 'string', false);
        $value = Request::post('value', 'raw', false);


        if(!$object->isValidProperty($property))
            Response::jsonError();

        if($property === 'isExtended')
        {
            $parent = $project->getParent($object->getName());
            if($parent){
                Response::jsonError($this->_lang->get('CANT_EXTEND_CHILD'));
            }
        }

        $object->$property = $value;

        $this->_storeProject();
        Response::jsonSuccess();
    }
    /**
     * Get list of existing ORM dictionaries
     */
    public function listdictionariesAction()
    {
        $manager = Dictionary_Manager::factory();
        $list = $manager->getList();
        $data = array();
        if(!empty($list))
            foreach ($list as $k=>$v)
                $data[] = array('id'=>$v,'title'=>$v);
        Response::jsonArray($data);
    }

    /**
     * Get list of store filds
     */
    public function storefieldsAction()
    {
        $this->_checkLoaded();
        $object = $this->_getObject();
        $project = $this->_getProject();

        if(!$object->isValidProperty('store'))
            Response::jsonArray([]);

        $storeName = str_replace([Designer_Project_Code::$NEW_INSTANCE_TOKEN,' '],'',$object->store);

        if(!$project->objectExists($storeName))
            Response::jsonArray([]);

        $store = $project->getObject($storeName);

        if($store instanceof Ext_Object_Instance){
            $store = $store->getObject();
        }

        $fields = array();


        if($store->isValidProperty('model') && strlen($store->model) && $project->objectExists($store->model))
        {
            $model = $project->getObject($store->model);

            if($model->isValidProperty('fields'))
            {
                $fields = $model->fields;
                if(is_string($fields))
                    $fields = json_decode($model->fields , true);
            }
        }

        if(empty($fields) && $store->isValidProperty('fields'))
        {
            $fields = $store->fields;

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
                    $data[] = array('id'=>$item->name);
                else
                    $data[] = array('id'=>$item['name']);
        }

        Response::jsonSuccess($data);
    }

    /**
     * Get list of existing form field adapters
     */
    public function listadaptersAction()
    {
        $data = array();
        $autoloaderPaths = Config::storage()->get('autoloader.php')->get('paths');
        $files = array();
        $classes = array();

        foreach($autoloaderPaths as $path)
        {
            $scanPath = $path. '/'. $this->_config->get('field_components');
            if(is_dir($scanPath))
            {
                $files = array_merge($files, File::scanFiles($scanPath, array('.php'), true, File::Files_Only));
                if(!empty($files))
                {
                    foreach ($files as $item)
                    {
                        $class = Utils::classFromPath(str_replace($autoloaderPaths, '', $item));
                        if(!in_array($class,$classes))
                        {
                            $data[] = array('id' => $class, 'title' => str_replace($scanPath.'/', '', substr($item, 0, -4)));
                            array_push($classes,$class);
                        }
                    }
                }
            }
        }
        Response::jsonArray($data);
    }

    /**
     * Change field type
     */
    public function changetypeAction()
    {
        $this->_checkLoaded();
        $object = $this->_getObject();
        $type = Request::post('type', 'string', false);
        $adapter = Request::post('adapter', 'string', false);
        $dictionary = Request::post('dictionary', 'string', false);

        if($type === 'Form_Field_Adapter')
        {
            $newObject = Ext_Factory::object($adapter);
            /*
             * Invalid adapter
             */
            if(!$adapter || !strlen($adapter) || !class_exists($adapter))
                Response::jsonError($this->_lang->INVALID_VALUE , array('adapter'=>$this->_lang->INVALID_VALUE ));

            if($adapter==='Ext_Component_Field_System_Dictionary')
            {
                /*
                 * Inavalid dictionary
                 */
                if(!$dictionary || !strlen($dictionary))
                    Response::jsonError($this->_lang->INVALID_VALUE , array('dictionary'=>$this->_lang->INVALID_VALUE));

                $newObject->dictionary = $dictionary;
                $newObject->displayField = 'title';
                $newObject->valueField = 'id';

            }
        }
        else
        {
            $newObject = Ext_Factory::object($type);
            /*
             * No changes
             */
            if($type === $object->getClass())
                Response::jsonSuccess();
        }

        Ext_Factory::copyProperties($object , $newObject);
        $newObject->setName($object->getName());
        $this->_getProject()->replaceObject($object->getName() , $newObject);
        $this->_storeProject();
        Response::jsonSuccess();
    }

    public function storeLoadAction()
    {
        $this->_checkLoaded();
        $object = $this->_getObject();
        $data = [];

        $store = $object->store;

        if(empty($store) || is_string($store))
        {
            if(strpos($store, Designer_Project_Code::$NEW_INSTANCE_TOKEN)!==false){
                $data = [
                    'type'=> 'instance',
                    'store' => trim(str_replace(Designer_Project_Code::$NEW_INSTANCE_TOKEN, '',$store))
                ];
            }else{
                $data = [
                    'type'=> 'store',
                    'store' => $store
                ];
            }

        }
        elseif($store instanceof Ext_Helper_Store)
        {
            $data = [
                'type'=> $store->getType(),
            ];
            switch($store->getType()){
                case Ext_Helper_Store::TYPE_STORE:
                    $data['store'] = $store->getValue();
                    break;
                case Ext_Helper_Store::TYPE_INSTANCE:
                    $data['instance'] = $store->getValue();
                    break;
                case Ext_Helper_Store::TYPE_JSCODE:
                    $data['call'] = $store->getValue();
                    break;
            }
        }
        Response::jsonSuccess($data);
    }

    public function storeSaveAction()
    {
        $this->_checkLoaded();
        $object = $this->_getObject();

        $storeHelper = new Ext_Helper_Store();

        $type =  Request::post('type','string',false);

        if(!in_array($type , $storeHelper->getTypes() , true)){
            Response::jsonError($this->_lang->get('FILL_FORM') , array('type'=>$this->_lang->get('INVALID_VALUE')));
        }

        $storeHelper->setType($type);

        switch($type){
            case Ext_Helper_Store::TYPE_STORE:
                $storeHelper->setValue(Request::post('store' , Filter::FILTER_RAW , ''));
                break;
            case Ext_Helper_Store::TYPE_INSTANCE:
                $storeHelper->setValue(Request::post('instance' , Filter::FILTER_RAW , ''));
                break;
            case Ext_Helper_Store::TYPE_JSCODE:
                $storeHelper->setValue(Request::post('call' , Filter::FILTER_RAW , ''));
                break;

        }

        $object->store = $storeHelper;
        $this->_storeProject();
        Response::jsonSuccess();
    }

}
