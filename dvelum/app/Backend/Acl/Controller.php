<?php
class Backend_Acl_Controller extends Backend_Controller
{
    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
    public function indexAction()
    {
        $this->_resource->addJs('/js/app/system/Acl.js' , true , 1);
        $this->_resource->addJs('/js/app/system/crud/acl.js' , true , 2);
        $this->_resource->addInlineJs('
        	var canEdit = ' . ((integer) $this->_user->canEdit($this->_module)) . ';
        	var canDelete = ' . ((integer) $this->_user->canDelete($this->_module)) . ';
        ');
    }
    /**
     * Groups list action
     */
    public function grouplistAction()
    {
        $data = Model::factory('Group')->getListVc(false , false , false , array(
            'id' ,
            'title' ,
            'system'
        ));
        Response::jsonSuccess($data);
    }
    /**
     * List permissions action
     */
    public function permissionsAction()
    {
        $user = Request::post('user_id' , 'int' , 0);
        $group = Request::post('group_id' , 'int' , 0);

        if($user && $group)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if($group)
            $data = Model::factory('acl_simple')->getGroupPermissions($group);

        if(!empty($data))
            $data = Utils::rekey('object' , $data);

        $manager = new Db_Object_Manager();
        $objects = $manager->getRegisteredObjects();

        foreach($objects as $name)
        {
            if(!isset($data[$name]))
            {
                $data[$name] = array(
                    'object' => $name ,
                    'create' => false,
                    'view' => false ,
                    'edit' => false ,
                    'delete' => false ,
                    'user_id'=>null,
                    'publish'=>false,
                    'group_id'=>$group
                );
            }
        }

        foreach($data as $k => &$v)
        {
            if(!Db_Object_Config::configExists($k))
            {
                unset($data[$k]);
                continue;
            }
            $cfg = Db_Object_Config::getInstance($k);

            if($cfg->isRevControl())
                $v['rc'] = true;
            else
                $v['rc'] = false;

            $v['title'] = $cfg->getTitle();
        }
        unset($v);
        Response::jsonSuccess(array_values($data));
    }
    /**
     * Save permissions action
     */
    public function savepermissionsAction()
    {
        $this->_checkCanEdit();

        $data = Request::post('data' , 'raw' , false);
        $groupId = Request::post('group_id' , 'int' , false);
        $data = json_decode($data , true);

        if(empty($data) || ! $groupId)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(Model::factory('acl_simple')->updateGroupPermissions($groupId , $data))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_EXEC);
    }
    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Acl.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}