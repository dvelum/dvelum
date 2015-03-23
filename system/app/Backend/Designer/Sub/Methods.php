<?php
class Backend_Designer_Sub_Methods extends Backend_Designer_Sub
{
  /**
   * Get list of Project methods
   */
  public function listAction()
  {
    $project = $this->_getProject();
    $methods =  $project->getMethodManager()->getMethods();
     
    $result = array();
    foreach ($methods as $object=>$list){
      foreach($list as $item) {
        if ($this->_getProject()->objectExists($object)) {
          $result[] = $this->_methodToArray($item, $object);
        }
      }
    }
    Response::jsonSuccess($result);
  }
  
  /**
   * Conver method object into array
   * @param Designer_Project_Methods_Item $method
   * @param string $objectName
   * @return array
   */
  protected function _methodToArray(Designer_Project_Methods_Item $method , $objectName)
  {
    $object = $this->_getProject()->getObject($objectName);    
    $code = $method->getCode();
    
    return array(
      'object'=>$objectName,
      'method'=>$method->getName(),
      'params'=> $method->getParamsAsDescription(),
      'has_code'=>(!empty($code)),
      'description'=>$method->getDescription(),
      'enabled'=>$object->isExtendedComponent()
    );
  }
  
  /**
   * Get list of object methods
   */
  public function objectmethodsAction()
  {
     $project = $this->_getProject();   
     $name = Request::post('object' , 'string' , '');
    
     if(!strlen($name) || ! $project->objectExists($name))
       Response::jsonSuccess(array());
    
     $object = $project->getObject($name);  
     $objectName = $object->getName();
     $objectMethods =  $project->getMethodManager()->getObjectMethods($objectName);
     
     $result = array();
     foreach ($objectMethods as $item)
       $result[] = $this->_methodToArray($item , $name);
     
     Response::jsonSuccess($result);
  }
  
  /**
   * Add new object method
   */
  public function addmethodAction()
  {
    $project = $this->_getProject();
    $objectName = Request::post('object' , 'string' , '');
    $objectMethodSrc = Request::post('method' , 'string' , '');
    $objectMethod = Filter::filterValue(Filter::FILTER_ALPHANUM, $objectMethodSrc);
    
    if(!strlen($objectName) || ! $project->objectExists($objectName))
      Response::jsonError($this->_lang->get('WRONG_REQUEST'));
    
    if(!strlen($objectMethodSrc))
      Response::jsonError($this->_lang->get('CANT_BE_EMPTY'));
        
    if($objectMethodSrc!==$objectMethod)
      Response::jsonError($this->_lang->get('INVALID_VALUE'));
    
    $methodsManager =  $project->getMethodManager();
    
    if($methodsManager->methodExists($objectName, $objectMethod))
      Response::jsonError($this->_lang->get('SB_UNIQUE'));
    
    if(!$methodsManager->addMethod($objectName, $objectMethod))
      Response::jsonError($this->_lang->get('CANT_EXEC'));
    
    $this->_storeProject();
    
    Response::jsonSuccess();
  }
  
  /**
   * Remove object method
   */
  public function removemethodAction()
  {
    $project = $this->_getProject();
    $objectName = Request::post('object' , 'string' , '');
    $objectMethod = Request::post('method' , Filter::FILTER_ALPHANUM , '');
    
    $methodManager  = $project->getMethodManager();
    
    if(!strlen($objectName) || ! $project->objectExists($objectName) || !strlen($objectMethod) || !$methodManager->methodExists($objectName, $objectMethod))
        Response::jsonError($this->_lang->get('WRONG_REQUEST'));
    
    $methodManager->removeMethod($objectName, $objectMethod);
    $this->_storeProject();
    Response::jsonSuccess();
  }
  
  /**
   * Get method data (name , params , code)
   */
  public function methoddataAction()
  {
    $project = $this->_getProject();
    $objectName = Request::post('object' , 'string' , '');
    $objectMethod = Request::post('method' , Filter::FILTER_ALPHANUM , '');
    
    $methodManager  = $project->getMethodManager();
    
    if(!strlen($objectName) || ! $project->objectExists($objectName) || !strlen($objectMethod) || !$methodManager->methodExists($objectName, $objectMethod))
        Response::jsonError($this->_lang->get('WRONG_REQUEST'));
    
    $method = $methodManager->getObjectMethod($objectName, $objectMethod);
    Response::jsonSuccess($method->toArray());
  }
  /**
   * Update method data
   */
  public function updateAction()
  {
    $project = $this->_getProject();
    $objectName = Request::post('object' , 'string' , '');
    $objectMethod = Request::post('method' , Filter::FILTER_ALPHANUM , '');
    
    $newName = Request::post('method_name' , Filter::FILTER_ALPHANUM , '');
    $description =  Request::post('description' , Filter::FILTER_STRING , '');
    $code =  Request::post('code' , Filter::FILTER_RAW ,'');
    $params = Request::post('params' , Filter::FILTER_STRING ,'');
    
    $methodManager  = $project->getMethodManager();
    
    if(!strlen($objectName) || ! $project->objectExists($objectName) || !strlen($objectMethod) || !$methodManager->methodExists($objectName, $objectMethod))
        Response::jsonError($this->_lang->get('WRONG_REQUEST'));
    
    if(!strlen($newName))
      Response::jsonError($this->_lang->get('FILL_FORM') , array('method_name'=>$this->_lang->get('CANT_BE_EMPTY')));
    
    if($objectMethod !== $newName)
    {
       if($methodManager->methodExists($objectName, $newName))       
         Response::jsonError($this->_lang->get('FILL_FORM') , array('method_name'=>$this->_lang->get('SB_UNIQUE')));
       
       if(!$methodManager->renameMethod($objectName, $objectMethod, $newName))
         Response::jsonError($this->_lang->get('CANT_EXEC').' (rename)');
    }
        
    $method = $methodManager->getObjectMethod($objectName, $newName);
    
    $method->setDescription($description);
    $method->setCode($code);
    $paramsArray = array();
    if(!empty($params))
    {
      $params = explode(',', trim($params));
      foreach ($params as $k=>$v)
      {
        $param = explode(' ', trim($v));
        if(count($param) == 1)
        {
          $paramsArray[] = array('name'=>trim($v) ,'type'=>'');
        }else{
          $pName = array_pop($param);
          $ptype = trim(implode(' ', str_replace('  ', ' ',$param)));
          $paramsArray[] = array('name'=>$pName ,'type'=>$ptype);
        }
      }
    }
    $method->setParams($paramsArray);    
    $this->_storeProject();
    Response::jsonSuccess();
  }
}
