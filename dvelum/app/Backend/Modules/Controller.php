<?php
class Backend_Modules_Controller extends Backend_Controller{
    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
     public function indexAction()
     {    
         $res = Resource::getInstance();  
         $res->addJs('/js/app/system/FilesystemWindow.js'  , 1);
         $res->addJs('/js/app/system/ImageField.js'  , 1);
         $res->addJs('/js/app/system/IconField.js'  , 1);
         $res->addJs('/js/app/system/HistoryPanel.js'  , 1);
         $res->addJs('/js/app/system/EditWindow.js'  , 1);
         $res->addJs('/js/app/system/crud/modules.js'  , 2);
         $res->addJs('/js/app/system/Modules.js'  , 3);
           $this->_resource->addInlineJs('
            var canEdit = '.((integer)$this->_user->canEdit($this->_module)).';
            var canDelete = '.((integer)$this->_user->canDelete($this->_module)).';
        ');
         parent::indexAction();
    }
    
    /**
     * Get modules list
     */
    public function listAction()
    {
        $type = Request::post('type' , Filter::FILTER_STRING, false);

        if(!$type)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        switch($type){
            case 'backend':
                $this->listBackend();
                break;
            case 'frontend':
                $this->listFrontend();
                break;
            default:
                Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
    }
    /**
     * Get list of backend modules
     */
    public function listBackend()
    {
        $manager = new Modules_Manager();
        $data = $manager->getList();

        foreach ($data as $k=>&$item)
        {
            $item['related_files']= '';

            if(empty($item['dist'])){
                $relatedFiles = $this->getRelatedFiles($item);
                if(!empty($relatedFiles)){
                    $item['related_files'] = implode('<br>' , $relatedFiles);
                }
            }

            $item['iconUrl'] = $this->_configMain->get('wwwroot').$item['icon'];
        }

        Response::jsonSuccess(array_values($data));
    }

    /**
     * Get list of frontend modules
     */
    public function listFrontend()
    {
        $manager = new Modules_Manager_Frontend();
        $data = $manager->getList();
        Response::jsonSuccess(array_values($data));
    }

    /**
     * Update modules list
     */
    public function updateAction()
    {
        $this->_checkCanEdit();
        $type = Request::post('type' , Filter::FILTER_STRING, false);

        if(!$type)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        switch($type){
            case 'backend':
                $this->updateBackendRecord();
                break;
            case 'frontend':
                $this->updateFrontendRecord();
                break;
            default:
                Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
    }

    /**
     * Update frontend module record
     */
    protected function updateFrontendRecord()
    {
        $id = Request::post('id' , Filter::FILTER_STRING , false);

        $acceptedFields =  array(
            'code' => Filter::FILTER_STRING ,
            'title' => Filter::FILTER_STRING ,
            'class'=> Filter::FILTER_STRING,
        );

        $data = array();
        $errors = array();
        foreach($acceptedFields as $name => $type){
            $data[$name] = Request::post($name , $type , false);
            if(empty($data[$name]))
                $errors[$name] = $this->_lang->get('CANT_BE_EMPTY');
        }

        if(!empty($errors))
            Response::jsonError($this->_lang->get('FILL_FORM') , $errors);

        $manager = new Modules_Manager_Frontend();

        if(empty($id) && $manager->isValidModule($data['code'])){
            Response::jsonError($this->_lang->get('INVALID_VALUE'),array('code'=>$this->_lang->get('SB_UNIQUE')));
        }

        if(empty($id)){
            $id = $data['code'];
        }

        if(!$manager->updateModule($id , $data)){
            Response::jsonError($this->_lang->get('CANT_WRITE_FS'));
        }

        Response::jsonSuccess(array('id'=>$data['code']));
    }

    /**
     * Update module record
     */
    protected function updateBackendRecord()
    {

        $manager = new Modules_Manager();

        $id = Request::post('id' , Filter::FILTER_STRING , false);
        $controller = Request::post('class' , Filter::FILTER_STRING , false);

        if(!$id){
            if(!$controller){
                Response::jsonError($this->_lang->get('INVALID_VALUE'),array('class'=>$this->_lang->get('CANT_BE_EMPTY')));
            }else{
                $moduleName = str_replace(array('Backend_','_Controller'),'', $controller);
                $list = $manager->getRegisteredModules();
                if(in_array($moduleName, $list , true)){
                    Response::jsonError($this->_lang->get('INVALID_VALUE'),array('class'=>$this->_lang->get('SB_UNIQUE')));
                }
            }
        }

        $acceptedFields =  array(
            //'id'=> Filter::FILTER_STRING ,
            'dev' => Filter::FILTER_BOOLEAN ,
            'active' => Filter::FILTER_BOOLEAN ,
            'title'=> Filter::FILTER_STRING ,
            'class'=> Filter::FILTER_STRING ,
            'designer'=> Filter::FILTER_STRING,
            'in_menu'=> Filter::FILTER_BOOLEAN ,
            'icon'=> Filter::FILTER_STRING
        );

        foreach($acceptedFields as $name => $type){
            if($type === Filter::FILTER_BOOLEAN)
                $data[$name] = Request::post($name , $type , false);
            else
                $data[$name] = Request::post($name , $type , null);
        }

        if($id) {
            if (!$manager->isValidModule($id)) {
                Response::jsonError($this->_lang->get('WRONG_REQUEST'));
            }
        }else{
            $id = $moduleName;
        }

        $data['id'] = $id;

        if($manager->updateModule($id , $data)){
            Response::jsonSuccess(array('id'=>$id));
        }else{
            Response::jsonError($this->_lang->get('CANT_WRITE_FS'));
        }
    }

    /**
     * Get list of available controllers
     */
    public function controllersAction()
    {
        $type = Request::post('type' , Filter::FILTER_STRING , false);

        if(!$type)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        switch($type){
            case 'backend':
                $manager = new Modules_Manager();
                Response::jsonSuccess($manager->getAvailableControllers());
                break;
            case 'frontend':
                $manager = new Modules_Manager_Frontend();
                Response::jsonSuccess($manager->getControllers());;
                break;
            default:
                Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
    }
    
    /**
     * Get Designer projects tree list
     */
    public function fslistAction()
    {
        $node = Request::post('node', 'string', '');
        $manager = new Designer_Manager($this->_configMain);
        Response::jsonArray($manager->getProjectsList($node));
    }

    /**
     * Get list of registered Db_Object's
     */
    public function objectsAction()
    {
        $manager = new Db_Object_Manager();
        $list = $manager->getRegisteredObjects();
        $data = array();

        $systemObjects = $this->_configBackend['system_objects'];

        foreach ($list as $key)
            if(!in_array(ucfirst($key), $systemObjects , true) && !class_exists('Backend_'.Utils_String::formatClassName($key).'_Controller'))
                $data[]= array('id'=>$key , 'title'=>Db_Object_Config::getInstance($key)->getTitle());

        Response::jsonSuccess($data);
    }

    /**
     * Create new module
     */
    public function createAction()
    {
        $this->_checkCanEdit();

        $object = Request::post('object', 'string', false);

        if(!$object)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $object = Utils_String::formatClassName($object);

        $class = 'Backend_' . $object . '_Controller';

        if(class_exists($class))
            Response::jsonError($this->_lang->FILL_FORM , array('id'=>'name','msg'=>$this->_lang->SB_UNIQUE));

        $designerConfig = Config::storage()->get('designer.php');

        $projectRelativePath = '/' . strtolower($object) . '.designer.dat';
        $projectFile = Config::storage()->getWrite() . $designerConfig->get('configs') . strtolower($object) . '.designer.dat';

        if(file_exists($projectFile))
            Response::jsonError($this->_lang->FILE_EXISTS . '(' . $projectFile . ')');

        $objectConfig = Db_Object_Config::getInstance($object);

        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Db_Object_Acl::ACCESS_CREATE , $object)  || 	!$acl->can(Db_Object_Acl::ACCESS_VIEW , $object)){
                Response::jsonError($this->_lang->get('ACL_ACCESS_DENIED'));
            }
        }

        $manager = new Db_Object_Manager();

        if(!$manager->objectExists($object))
            Response::jsonError($this->_lang->FILL_FORM , array('id'=>'object','msg'=>$this->_lang->INVALID_VALUE));

        $codeGenadApter = $this->_configBackend->get('modules_generator');

        $codeGen = new $codeGenadApter();
        try{
            if($objectConfig->isRevControl())
                $codeGen->createVcModule($object,  $projectFile);
            else
                $codeGen->createModule($object,  $projectFile);

        }catch (Exception $e){
            Response::jsonError($e->getMessage());
        }

        $userInfo = User::getInstance()->getInfo();
        $per = Model::factory('Permissions');

        if(!$per->setGroupPermissions($userInfo['group_id'], $object , 1 , 1 , 1 , 1))
            Response::jsonError($this->_lang->CANT_EXEC);

        $modulesManager = new Modules_Manager();
        $modulesManager->addModule($object , array(
            'class'=>$class,
            'id'=>$object ,
            'active'=>true,
            'dev'=>false,
            'title'=>$objectConfig->getTitle(),
            'designer'=> $projectRelativePath,
            'icon'=>'i/system/icons/default.png',
            'in_menu'=>true
        ));

        Response::jsonSuccess(
            array(
                'class'=>$class,
                'name'=>$object ,
                'active'=>true,
                'dev'=>false,
                'title'=>$objectConfig->getTitle(),
                'designer'=> $projectFile
            )
        );
    }

    /**
     * Get module data
     */
    public function loadDataAction()
    {
        $id = Request::post('id' , Filter::FILTER_STRING , false);
        $type = Request::post('type' , Filter::FILTER_STRING , false);

        if(!$id || !$type)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        switch($type){
            case 'backend':
                 $manager = new Modules_Manager();
                 Response::jsonSuccess($manager->getModuleConfig($id));
                break;
            case 'frontend':
                $manager = new Modules_Manager_Frontend();
                $data = $manager->getModuleConfig($id);
                $data['id'] = $data['code'];
                Response::jsonSuccess($data);
                break;
            default:
                Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
    }

    public function deleteAction()
    {
        $this->_checkCanDelete();

        $type = Request::post('type' , Filter::FILTER_STRING , false);

        if(!$type)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        switch($type){
            case 'backend':
                $this->deleteBackendModule();
                break;
            case 'frontend':
                $this->deleteFrontendModule();
                break;
            default:
                Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
    }

    /**
     * Delete frontend module
     */
    protected function deleteFrontendModule()
    {
        $manager = new Modules_Manager_Frontend();
        $code = Request::post('code' , Filter::FILTER_STRING , false);

        if(!$code || !$manager->isValidModule($code)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        if($manager->removeModule($code)){
            Response::jsonSuccess();
        }else{
            Response::jsonError($this->_lang->get('CANT_WRITE_FS'));
        }
    }

    /**
     * Get list of module files which can be deleted
     * @param array $moduleConfig
     * @return array
     */
    protected function getRelatedFiles($moduleConfig)
    {
        $relatedFiles = [];
        $classFile = $this->_configMain->get('local_controllers').str_replace('_', '/', $moduleConfig['class']).'.php';

        if(empty($moduleConfig['dist']) && file_exists($classFile))
            $relatedFiles[] = $classFile;

        if(empty($moduleConfig['dist']) && !empty($moduleConfig['designer']))
        {
            // local configs path
            $configWrite = Config::storage()->getWrite();
            // relative designer projects path
            $layoutsPath = Config::storage()->get('designer.php')->get('configs');
            // project path in local configs directory
            $projectWrite = $configWrite.$layoutsPath.$moduleConfig['designer'];

            if(file_exists($projectWrite)){
                $relatedFiles[] = $projectWrite;
                if(is_dir($projectWrite.'.files')){
                    $relatedFiles[]=$projectWrite.'.files';
                }
            }
        }
        return $relatedFiles;
    }
    /**
     * Delete module
     */
    protected function deleteBackendModule()
    {
        $this->_checkCanDelete();
        $module = Request::post('id', 'string', false);
        $removeRelated = Request::post('delete_related', 'boolean', false);

        $manager = new Modules_Manager();
        $data = $manager->getList();
        if(!$module || !strlen($module) || !$manager->isValidModule($module) || !isset($data[$module]))
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        $item = $data[$module];

        $filesToDelete = array();

        if($removeRelated && empty($item['dist'])){
            $filesToDelete = $this->getRelatedFiles($item);
        }

        // check before deleting
        if(!empty($filesToDelete))
        {
            $err = array();
            foreach ($filesToDelete as $file){
                if(!is_writable($file))
                    $err[] = $file;
            }

            if(!empty($err))
                Response::jsonError($this->_lang->get('CANT_WRITE_FS') . "\n<br>" . implode(",\n<br>", $err));
        }

        $manager->removeModule($module);

        if(!$manager->save())
            Response::jsonError($this->_lang->get('CANT_WRITE_FS') .' ' . $manager->getConfig()->getName());

        // try to delete related files
        if(!empty($filesToDelete))
        {
            $err = array();
            foreach ($filesToDelete as $file)
            {
                if(!file_exists($file))
                    continue;

                if(is_dir($file)){
                    if(!File::rmdirRecursive($file , true))
                        $err[] = $file;
                }
                else{
                    if(!unlink($file))
                        $err[] = $file;
                }
            }

            if(!empty($err))
                Response::jsonError($this->_lang->get('CANT_WRITE_FS') . "\n<br>".implode(",\n<br>", $err));
        }

        $this->createClassMap();
        Response::jsonSuccess();
    }

    /**
     * Get list of image folders
     */
    public function iconDirsAction()
    {
        $path = Request::post('node', 'string', '');
        $path = str_replace('.','', $path);

        $dirPath = $this->_configMain->get('wwwpath');

        if(!is_dir($dirPath.$path))
            Response::jsonArray(array());

        $files = File::scanFiles($dirPath . $path, false, false , File::Dirs_Only);

        if(empty($files))
            Response::jsonArray(array());

        sort($files);
        $list = array();

        foreach($files as $k=>$fpath)
        {
            $text = basename($fpath);

            $obj = new stdClass();
            $obj->id = str_replace($dirPath, '', $fpath);
            $obj->text = $text;
            $obj->url = '/' . $obj->id;

            if(is_dir($fpath))
            {
                $obj->expanded = false;
                $obj->leaf = false;
            }
            else
            {
                $obj->leaf = true;
            }
            $list[] = $obj;
        }
        Response::jsonArray($list);
    }

    /**
     * Get image list
     */
    public function iconListAction()
    {
        $dirPath = $this->_configMain->get('wwwpath');
        $dir = Request::post('dir', 'string', '');

        if(!is_dir($dirPath . $dir))
            Response::jsonArray(array());

        // windows & linux paths fix
        $scanPath = str_replace('//','/', $dirPath . $dir);

        $files = File::scanFiles($scanPath, array('.jpg','.png','.gif','.jpeg') , false , File::Files_Only);

        if(empty($files))
            Response::jsonArray(array());

        sort($files);
        $list = array();

        foreach($files as $k=>$fpath)
        {
            $text  = basename($fpath);
            $list[] = array(
                'name'=>$text,
                'url'=>str_replace($dirPath , $this->_configMain->get('wwwroot'), $fpath),
                'path'=>str_replace($dirPath, '', $fpath),
            );
        }
        Response::jsonSuccess($list);
    }

    /**
     * Rebuild class map
     */
    public function rebuildMapAction()
    {
        $this->_checkCanEdit();

        if(!$this->createClassMap()){
            Response::jsonError();
        }
        Response::jsonSuccess();
    }

    /**
     *
     */
    public function createClassMap()
    {
        $mapBuilder = new Classmap($this->_configMain);
        $mapBuilder->update();
        return $mapBuilder->save();
    }

    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] = '/js/app/system/Modules.js';
        $projectData['includes']['js'][] = '/js/app/system/FilesystemWindow.js';
        $projectData['includes']['js'][] = '/js/app/system/IconField.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}
