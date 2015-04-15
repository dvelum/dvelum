<?php
class Backend_Fmodules_Controller extends Backend_Controller
{
  /**
   * (non-PHPdoc)
   * 
   * @see Backend_Controller::indexAction()
   */
  public function indexAction()
  {
    $res = Resource::getInstance();
    $res->addJs('/js/app/system/FilesystemWindow.js' , 1);
    $res->addJs('/js/app/system/crud/fmodules.js' , 1);
    $this->_resource->addInlineJs('
        	var canEdit = ' . ((integer) $this->_user->canEdit($this->_module)) . ';
        	var canDelete = ' . ((integer) $this->_user->canDelete($this->_module)) . ';
     ');
  }

  public function listAction()
  {
    $manager = new Backend_Fmodules_Manager();
    $data = $manager->getList();
    
    foreach($data as $k => $v)
      $data[$k]['name'] = $k;
    
    Response::jsonSuccess(array_values($data));
  }

  public function updateAction()
  {
    $this->_checkCanEdit();
    
    $data = Request::post('data' , 'raw' , false);
    
    if($data === false)
      Response::jsonError($this->_lang->INVALID_VALUE);
    
    $data = json_decode($data , true);
    
    if(!isset($data[0]))
      $data = array($data);
    
    $manager = new Backend_Fmodules_Manager();
    $manager->removeAll();
    
    if(!empty($data))
    {
      foreach($data as $v)
      {
        if(empty($v))
          continue;
        
        $name = $v['name'];
        unset($v['name']);
        $manager->addModule($name , $v);
      }
    }
    
    if($manager->save())
      Response::jsonSuccess();
    else
      Response::jsonError($this->_lang->CANT_WRITE_FS);
  }

  /**
   * Get list of available controllers
   */
  public function controllersAction()
  {
    $appPath = $this->_configMain['application_path'];
    $folders = File::scanFiles($this->_configMain->get('frontend_controllers') , false , true , File::Dirs_Only);
    $data = array();
    
    if(!empty($folders))
    {
      foreach($folders as $item)
      {
        $name = basename($item);
        if(file_exists($item . '/Controller.php'))
        {
          $name = str_replace($appPath , '' , $item . '/Controller.php');
          $name = Utils::classFromPath($name);
          $data[] = array(
              'id' => $name , 
              'title' => $name
          );
        }
      }
    }
    Response::jsonSuccess($data);
  }
}