<?php
abstract class Db_Object_Acl
{
  const ACCESS_VIEW = 'view';
  const ACCESS_EDIT = 'edit';
  const ACCESS_CREATE = 'create';
  const ACCESS_DELETE = 'delete';
  const ACCESS_PUBLISH = 'publish';

  public function __construct(){

  }
  /**
   * Current user
   * @var User
   */
  protected $_user = false;
  /**
   * Check create permissions
   * @param Db_Object $object
   * @return boolean 
   */
  abstract public function canCreate(Db_Object $object);
  /**
   * Check update permissions
   * @param Db_Object $object
   * @return boolean 
   */
  abstract public function canEdit(Db_Object $object);
  /**
   * Check delete permissions
   * @param Db_Object $object
   * @return boolean
   */
  abstract public function canDelete(Db_Object $object);
  /**
   * Check publish permissions
   * @param Db_Object $object
   * @return boolean
   */
  abstract public function canPublish(Db_Object $object);
  /**
   * Check read permissions
   * @param Db_Object $object
   * @return boolean
   */
  abstract public function canRead(Db_Object $object);  
  /**
   * Set current User
   * @param mixed $user
   */
  public function setUser($user)
  {
    $this->_user = $user;
  }
  /**
   * Check permissions for action
   * @param string $operation - const  Db_Object_Acl::ACCESS_VIEW,ACCESS_EDIT,ACCESS_CREATE,ACCESS_DELETE,ACCESS_PUBLISH
   * @param string $objectName
   * @return boolean
   */
  abstract public function can($operation , $objectName);
  /**
   * Create ACL adapter object
   * @param string $class
   * @throws Exception
   * @return Db_Object_Acl
   */
  static public function factory($class)
  {
    $object = new $class;
    
    if(!$object instanceof Db_Object_Acl)
      throw new Exception('Invalid ACL adapter '.$class);
    
    return $object;  
  }
}