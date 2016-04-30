<?php
/**
 * Users module UI Controller
 */
class Backend_User_Controller extends Backend_Controller
{
    /**
     * Load user info action
     */
    public function userLoadAction()
    {
        $id = Request::post('id' , 'integer' , false);
        if(!$id)
            Response::jsonError($this->_lang->get('INVALID_VALUE'));

        try
        {
            $user = new Db_Object('user' , $id);
            $userData = $user->getData();
            unset($userData['pass']);
            Response::jsonSuccess($userData);
        }
        catch(Exception $e)
        {
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
    }

    /**
     * Users list action
     */
    public function userListAction()
    {
        $pager = Request::post('pager' , 'array' , array());
        $filter = Request::post('filter' , 'array' , array());
        $query = Request::post('search' , 'string' , false);

        $model = Model::factory('User');
        $count = $model->getCount($filter , $query);
        $data = $model->getListVc($pager , $filter , $query , array(
            'id' ,
            'group_id' ,
            'name' ,
            'login' ,
            'email' ,
            'enabled' ,
            'admin'
        ));

        /*
         * Fill in group titles Its faster then using join
         */
        $groups = Model::factory('Group')->getGroups();
        if(! empty($data) && ! empty($groups))
            foreach($data as $k => &$v)
                if(array_key_exists($v['group_id'] , $groups))
                    $v['group_title'] = $groups[$v['group_id']];
                else
                    $v['group_title'] = '';
        unset($v);
        $result = array(
            'success' => true ,
            'count' => $count ,
            'data' => $data
        );
        Response::jsonArray($result);
    }

    /**
     * Groups list action
     */
    public function groupListAction()
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

        $data = array();

        if($user && $group)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        if($group)
            $data = Model::factory('Permissions')->getGroupPermissions($group);

        if(!empty($data))
            $data = Utils::rekey('module' , $data);

        $manager = new Modules_Manager();
        $modules = $manager->getRegisteredModules();
        $list = $manager->getList();

        foreach($modules as $name)
        {
            if(! isset($data[$name]))
            {
                $data[$name] = array(
                    'module' => $name ,
                    'view' => false ,
                    'edit' => false ,
                    'delete' => false ,
                    'publish' => false,
                    'only_own'=>false
                );
            }
        }

        foreach($data as $k => &$v)
        {
            $v['title'] = $v['module'];
            if(isset($list[$v['module']]) && !empty($list[$v['module']]['title'])){
                $v['title'] = $list[$v['module']]['title'];
            }

            $class = $manager->getModuleController($k);
            if(! class_exists($class))
            {
                $v['rc'] = false;
                continue;
            }

            $reflector = new ReflectionClass($class);

            if($reflector->isSubclassOf('Backend_Controller_Crud_Vc'))
                $v['rc'] = true;
            else
                $v['rc'] = false;
        }
        unset($v);
        Response::jsonSuccess(array_values($data));
    }

    /**
     * Save permissions action
     */
    public function savePermissionsAction()
    {
        $this->_checkCanEdit();

        $data = Request::post('data' , 'raw' , false);
        $groupId = Request::post('group_id' , 'int' , false);
        $data = json_decode($data , true);

        if(empty($data) || ! $groupId)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(Model::factory('Permissions')->updateGroupPermissions($groupId , $data))
        {
            if(Backend_Cache_Manager::resetAll())
                Response::jsonSuccess();
            else
                Response::jsonError($this->_lang->get('CANT_RESET_CACHE'));
        }
        else
        {
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }
    }

    /**
     * Add group action
     */
    public function addGroupAction()
    {
        $this->_checkCanEdit();

        $title = Request::post('name' , 'str' , false);
        if($title === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $gModel = Model::factory('Group');
        if($gModel->addGroup($title))
            Response::jsonSuccess(array());
        else
            Response::jsonError($this->_lang->CANT_EXEC);
    }

    /**
     * Remove group action
     */
    public function removeGroupAction()
    {
        $this->_checkCanDelete();

        $id = Request::post('id' , 'int' , false);
        if(! $id)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        $gModel = Model::factory('Group');
        $pModel = Model::factory('Permissions');
        if($gModel->removeGroup($id) && $pModel->removeGroup($id))
            Response::jsonSuccess(array());
        else
            Response::jsonError($this->_lang->get('CANT_EXEC'));
    }

    /**
     * Save user info action
     */
    public function userSaveAction()
    {
        $this->_checkCanEdit();

        $pass = Request::post('pass' , 'string' , false);

        if($pass)
            Request::updatePost('pass' , password_hash($pass , PASSWORD_DEFAULT));

        $object = $this->getPostedData($this->_module);

        /*
         * New user
         */
        if(!$object->getId())
        {
            $date = date('Y-m-d H:i:s');
            $ip = '127.0.0.1';

            $object->registration_date = $date;
            $object->confirmation_date = $date;
            $object->registration_ip = $ip;
            $object->confirmed = true;
            $object->last_ip = $ip;
        }

        if(!$recId = $object->save())
            Response::jsonError($this->_lang->get('CANT_EXEC'));

        Response::jsonSuccess();
    }

    /**
     * Remove user Action
     */
    public function removeUserAction()
    {
        $this->_checkCanDelete();

        $id = Request::post('id' , 'int' , false);

        if(! $id)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        if(User::getInstance()->getId() == $id)
            Response::jsonError($this->_lang->get('CANT_DELETE_OWN_PROFILE'));

        if(Model::factory('User')->remove($id))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->get('CANT_EXEC'));
    }

    /**
     * Check if login is unique
     */
    public function checkLoginAction()
    {
        $id = Request::post('id' , 'int' , 0);
        $value = Request::post('value' , 'string' , false);

        if(! $value)
            Response::jsonError($this->_lang->get('INVALID_VALUE'));

        if(Model::factory('User')->checkUnique($id , 'login' , $value))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->get('SB_UNIQUE'));
    }

    /**
     * Check if email is unique
     */
    public function checkEmailAction()
    {
        $id = Request::post('id' , 'int' , false);
        $value = Request::post('value' , Filter::FILTER_EMAIL , false);

        if(empty($value) || !Validator_Email::validate($value))
            Response::jsonError($this->_lang->get('INVALID_VALUE'));

        if(Model::factory('User')->checkUnique($id , 'email' , $value))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->get('SB_UNIQUE'));
    }
}