<?php
class Backend_User_Controller extends Backend_Controller
{

  /**
   * (non-PHPdoc)
   * 
   * @see Backend_Controller::indexAction()
   */
  public function indexAction()
  {
    $res = Resource::getInstance();
    $res->addJs('/js/lib/ext_ux/SearchField.js');
    $this->_resource->addJs('/js/lib/extjs4/ux/CheckColumn.js' , 0);
    $this->_resource->addJs('/js/app/system/SearchPanel.js' , 0);
    $res->addJs('/js/app/system/crud/user.js' , true , 1);
    $this->_resource->addInlineJs('
          var canPublish =  ' . ((integer) $this->_user->canPublish($this->_module)) . ';
        	var canEdit = ' . ((integer) $this->_user->canEdit($this->_module)) . ';
        	var canDelete = ' . ((integer) $this->_user->canDelete($this->_module)) . ';
        ');
  }

  /**
   * Load user info action
   */
  public function userloadAction()
  {
    $id = Request::post('id' , 'integer' , false);
    if(! $id)
      Response::jsonError($this->_lang->INVALID_VALUE);
    
    try
    {
      $user = new Db_Object('user' , $id);
      $userData = $user->getData();
      unset($userData['pass']);
      Response::jsonSuccess($userData);
    }
    catch(Exception $e)
    {
      Response::jsonError($this->_lang->WRONG_REQUEST);
    }
  }

  /**
   * Users list action
   */
  public function userlistAction()
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
     * Fillin group titles Its faster then using join
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
      $data = Model::factory('Permissions')->getGroupPermissions($group);
    
    if(! empty($data))
      $data = Utils::rekey('module' , $data);
    
    $manager = new Backend_Modules_Manager();
    $modules = $manager->getRegisteredModules();
    $sysControllers = $this->_configBackend->get('system_controllers');
    
    foreach($modules as $name)
    {
      if(! isset($data[$name]))
      {
        $data[$name] = array(
            'module' => $name , 
            'view' => false , 
            'edit' => false , 
            'delete' => false , 
            'publish' => false
        );
      }
    }
    
    foreach($data as $k => &$v)
    {
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
  public function savepermissionsAction()
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
        Response::jsonError($this->_lang->CANT_RESET_CACHE);
    }
    else
    {
      Response::jsonError($this->_lang->CANT_EXEC);
    }
  }

  /**
   * Add group action
   */
  public function addgroupAction()
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
  public function removegroupAction()
  {
    $this->_checkCanDelete();
    
    $id = Request::post('id' , 'int' , false);
    if(! $id)
      Response::jsonError($this->_lang->WRONG_REQUEST);
    
    $gModel = Model::factory('Group');
    $pModel = Model::factory('Permissions');
    if($gModel->removeGroup($id) && $pModel->removeGroup($id))
      Response::jsonSuccess(array());
    else
      Response::jsonError($this->_lang->CANT_EXEC);
  }

  /**
   * Save user info action
   */
  public function usersaveAction()
  {
    $this->_checkCanEdit();
    
    $pass = Request::post('pass' , 'string' , false);
    
    if($pass)
      Request::updatePost('pass' , Utils::hash($pass));
    
    $object = $this->getPostedData($this->_module);
    
    /*
     * New user
     */
    if(! $object->getId())
    {
      $date = date('Y-m-d H:i:s');
      $ip = '127.0.0.1';
      
      $object->registration_date = $date;
      $object->confirmation_date = $date;
      $object->registration_ip = $ip;
      $object->confirmed = true;
      $object->last_ip = $ip;
    }
    
    if(! $recId = $object->save())
      Response::jsonError($this->_lang->CANT_EXEC);
    
    Response::jsonSuccess();
  }

  /**
   * Remove user Action
   */
  public function removeuserAction()
  {
    $this->_checkCanDelete();
    
    $id = Request::post('id' , 'int' , false);
    
    if(! $id)
      Response::jsonError($this->_lang->WRONG_REQUEST);
    
    if(User::getInstance()->id == $id)
      Response::jsonError($this->_lang->CANT_DELETE_OWN_PROFILE);
    
    if(Model::factory('User')->remove($id))
      Response::jsonSuccess();
    else
      Response::jsonError($this->_lang->CANT_EXEC);
  }

  /**
   * Check if login is unique
   */
  public function checkloginAction()
  {
    $id = Request::post('id' , 'int' , 0);
    $value = Request::post('value' , 'string' , false);
    
    if(! $value)
      Response::jsonError($this->_lang->INVALID_VALUE);
    
    if(Model::factory('User')->checkUnique($id , 'login' , $value))
      Response::jsonSuccess();
    else
      Response::jsonError($this->_lang->SB_UNIQUE);
  }

  /**
   * Check if email is unique
   */
  public function checkemailAction()
  {
    $id = Request::post('id' , 'int' , false);
    $value = Request::post('value' , 'string' , false);
    
    if(! $value)
      Response::jsonError($this->_lang->INVALID_VALUE);
    
    if(Model::factory('User')->checkUnique($id , 'email' , $value))
      Response::jsonSuccess();
    else
      Response::jsonError($this->_lang->SB_UNIQUE);
  }
}