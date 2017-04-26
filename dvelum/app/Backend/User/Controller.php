<?php
/**
 * Users module UI Controller
 */
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config;

class Backend_User_Controller extends  Dvelum\App\Backend\Api\Controller
{
    /**
     * Load user info action
     */
    public function userLoadAction()
    {
        $id = $this->request->post('id' , 'integer' , false);
        if(!$id)
           $this->response->error($this->lang->get('INVALID_VALUE'));

        try
        {
            $user = Orm\Object::factory('user' , $id);
            $userData = $user->getData();
            unset($userData['pass']);
            $this->response->success($userData);
        }
        catch(Exception $e)
        {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }
    }

    /**
     * Users list action
     */
    public function userListAction()
    {
        $pager = $this->request->post('pager' , 'array' , array());
        $filter = $this->request->post('filter' , 'array' , array());
        $query = $this->request->post('search' , 'string' , false);

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

        $this->response->success($data,['count'=>$count]);
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
       $this->response->success($data);
    }

    /**
     * List permissions action
     */
    public function permissionsAction()
    {
        $user = $this->request->post('user_id' , 'int' , 0);
        $group = $this->request->post('group_id' , 'int' , 0);

        $data = array();

        if($user && $group)
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        if($group)
            $data = Model::factory('Permissions')->getGroupPermissions($group);

        if(!empty($data))
            $data = Utils::rekey('module' , $data);

        $manager = new Modules_Manager();
        $modules = $manager->getRegisteredModules();
        $moduleKeys = array_flip($modules);

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
            // remove unregistered modules from data
            if(!isset($moduleKeys[$v['module']])){
                unset($data[$k]);
            }

            $v['title'] = $v['module'];
            if(isset($list[$v['module']]) && !empty($list[$v['module']]['title'])){
                $v['title'] = $list[$v['module']]['title'];
            }
            $v['rc'] = $manager->isVcModule($k);
        }
        unset($v);
       $this->response->success(array_values($data));
    }

    /**
     * Get list of individual permissions
     */
    public function individualPermissionsAction()
    {
        $userId = $this->request->post('id', Filter::FILTER_INTEGER, false);

        if(!$userId)
           $this->response->success();

        $userInfo = Model::factory('User')->getCachedItem($userId);

        if(!$userInfo)
           $this->response->success([]);

        $permissionsModel =  Model::factory('Permissions');

        $manager = new Modules_Manager();
        $modules = $manager->getRegisteredModules();
        $list = $manager->getList();

        foreach($modules as $name)
        {
            if(!isset($data[$name]))
            {
                $data[$name] = array(
                    'module' => $name ,
                    'view' => false ,
                    'edit' => false ,
                    'delete' => false ,
                    'publish' => false
                );
            }
            if(isset($list[$name]) && !empty($list[$name]['title'])){
                $data[$name]['title'] = $list[$name]['title'];
            }else{
                $data[$name]['title'] = $name;
            }
            $data[$name]['rc'] = $manager->isVcModule($name);
        }

        $permissionFields = ['view','edit','delete','publish','only_own'];
        $records = $permissionsModel->getRecords($userId, $userInfo['group_id']);


        foreach ($records as $item)
        {
            if(!isset($data[$item['module']]))
                continue;

            foreach ($permissionFields as $field)
            {
                if($item[$field]){
                    $data[$item['module']][$field] = (boolean) $item[$field];
                }

                if($item['group_id']){
                    $data[$item['module']]['g_'.$field] = (boolean) $item[$field];
                    continue;
                }

            }
        }
        $this->response->success(array_values($data));
    }

    /**
     * Save permissions action
     */
    public function savePermissionsAction()
    {
        $this->checkCanEdit();

        $data = $this->request->post('data' , 'raw' , false);
        $groupId = $this->request->post('group_id' , 'int' , false);
        $data = json_decode($data , true);

        if(empty($data) || ! $groupId)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        if(!Model::factory('Permissions')->updateGroupPermissions($groupId , $data)) {
           $this->response->error($this->lang->get('CANT_EXEC'));
        }
        $this->response->success();
    }

    public function saveIndividualPermissionsAction()
    {
        $this->checkCanEdit();
        $data = $this->request->post('data' , 'raw' , false);
        $userId = $this->request->post('user_id' , 'int' , false);
        $data = json_decode($data , true);

        if(empty($data) || !$userId){
           $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        if(!Model::factory('Permissions')->updateUserPermissions($userId , $data)){
           $this->response->error($this->lang->get('CANT_EXEC'));
        }
       $this->response->success();
    }

    /**
     * Add group action
     */
    public function addGroupAction()
    {
        $this->checkCanEdit();

        $title = $this->request->post('name' , 'str' , false);
        if($title === false)
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        $gModel = Model::factory('Group');
        if($gModel->addGroup($title))
           $this->response->success(array());
        else
           $this->response->error($this->lang->get('CANT_EXEC'));
    }

    /**
     * Remove group action
     */
    public function removeGroupAction()
    {
        $this->checkCanDelete();

        $id = $this->request->post('id' , 'int' , false);
        if(! $id)
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        $gModel = Model::factory('Group');
        $pModel = Model::factory('Permissions');
        if($gModel->removeGroup($id) && $pModel->removeGroup($id))
           $this->response->success(array());
        else
           $this->response->error($this->lang->get('CANT_EXEC'));
    }

    /**
     * Save user info action
     */
    public function userSaveAction()
    {
        $this->checkCanEdit();

        $pass = $this->request->post('pass' , 'string' , false);

        if($pass)
            Request::updatePost('pass' , password_hash($pass , PASSWORD_DEFAULT));

        $object = $this->getPostedData($this->module);

        if(!$object->get('admin')){
            $object->set('group_id', null);
        }

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
           $this->response->error($this->lang->get('CANT_EXEC'));

       $this->response->success();
    }

    /**
     * Remove user Action
     */
    public function removeUserAction()
    {
        $this->checkCanDelete();

        $id = $this->request->post('id' , 'int' , false);

        if(! $id)
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        if(User::getInstance()->getId() == $id)
           $this->response->error($this->lang->get('CANT_DELETE_OWN_PROFILE'));

        if(Model::factory('User')->remove($id))
           $this->response->success();
        else
           $this->response->error($this->lang->get('CANT_EXEC'));
    }

    /**
     * Check if login is unique
     */
    public function checkLoginAction()
    {
        $id = $this->request->post('id' , 'int' , 0);
        $value = $this->request->post('value' , 'string' , false);

        if(! $value)
           $this->response->error($this->lang->get('INVALID_VALUE'));

        if(Model::factory('User')->checkUnique($id , 'login' , $value))
           $this->response->success();
        else
           $this->response->error($this->lang->get('SB_UNIQUE'));
    }

    /**
     * Check if email is unique
     */
    public function checkEmailAction()
    {
        $id = $this->request->post('id' , 'int' , false);
        $value = $this->request->post('value' , Filter::FILTER_EMAIL , false);

        if(empty($value) || !Validator_Email::validate($value))
           $this->response->error($this->lang->get('INVALID_VALUE'));

        if(Model::factory('User')->checkUnique($id , 'email' , $value))
           $this->response->success();
        else
           $this->response->error($this->lang->get('SB_UNIQUE'));
    }
}