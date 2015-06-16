<?php
class Model_Acl_Simple extends Model
{
  static protected $_fields = array('view','create','edit','delete','object','publish');

  /**
   * Get object permissions for user
   * @param integer $userId
   * @param integer $groupId
   * @throws Exception
   * @return array
   */
  public function getPermissions($userId , $groupId)
  {
      if(empty($userId))
          throw new Exception('Need user id');

      $cache = $this->_cache;

      /*
       * Check if cache exists
       */
      if($cache && $data = $cache->load('object_permissions_' . $userId))
          return $data;

      $data = array();
      /*
       * Load permissions for group
      */
      if($groupId){

          $sql = $this->_dbSlave->select()
          ->from($this->table() , self::$_fields)
          ->where('`group_id` = '.intval($groupId))
          ->where('`user_id` IS NULL');

          $groupRights = $this->_dbSlave->fetchAll($sql);

          if(!empty($groupRights))
              $data =  Utils::rekey('object', $groupRights);
      }
     /*
      * Load permissions for user
      */
      $sql = $this->_dbSlave->select()
      ->from($this->table() , self::$_fields)
      ->where('`user_id` = '.intval($userId))
      ->where('`group_id` IS NULL');

      $userRights = $this->_dbSlave->fetchAll($sql);

      /*
       * Replace group permissions by permissions redefined for concrete user
       */
       if(!empty($userRights))
          $data = array_merge($data , Utils::rekey('object', $userRights));

      /*
       * Cache info
      */
      if($cache)
          $cache->save($data , 'object_permissions' . $userId);

      return $data;
  }

  /**
   * Get permissions for user group
   * Return permissions list indexed by module id
   * @return array
   */
  public function getGroupPermissions($groupId)
  {
      $data = array();
      $cache = $this->_cache;
      /*
       * Check if cache exists
       */
      if($cache && $data = $cache->load('acl_simple_group_permissions' . $groupId))
          return $data;

      $sql = $this->_dbSlave->select()
      ->from($this->table() , self::$_fields)
      ->where('`group_id` = '.intval($groupId))
      ->where('`user_id` IS NULL');

      $data = $this->_dbSlave->fetchAll($sql);

      if(!empty($data))
          $data =  Utils::rekey('object', $data);

      /*
       * Cache info
      */
      if($cache)
          $cache->save($data , 'acl_simple_group_permissions' . $groupId);

      return $data;
  }

 /**
  * Update group permissions
  * @param integer $groupId
  * @param array $data - permissions like array(
  * array(
  * 			'object'=>'object',
  * 			'view'=>true,
  *             'create'=>false,
  * 			'edit'=>false,
  * 			'delete'=>false,
  * 			'publish'=>false
  * 	),
  * 	...
  * )
  * @return boolean
  */
  public function updateGroupPermissions($groupId , array $data)
  {

      $groupPermissions = $this->getList(false, array('group_id'=>$groupId,'user_id'=>null));
      $sorted = Utils::rekey('object', $groupPermissions);

      $modulesToRemove = array();

      if(!empty($sorted))
          $modulesToRemove = array_diff(array_keys($sorted), Utils::fetchCol('object', $data));

      if(!empty($modulesToRemove))
          $this->_db->delete($this->table(),'`object` IN (\''.implode("','", $modulesToRemove).'\') AND `group_id`='.intval($groupId));

      $errors = false;

      foreach ($data as $values)
      {
          if(empty($values))
              return false;

          /**
           * Check if all needed fields are present
           */
          $diff = array_diff(self::$_fields, array_keys($values));

          if(!empty($diff))
              continue;

          try{

              if(isset($sorted[$values['object']]))
              {
                  $obj = new Db_Object($this->_name , $sorted[$values['object']][$this->_objectConfig->getPrimaryKey()]);
                  $obj->setValues(array(
                      'view'=>(boolean)$values['view'],
                      'create'=>(boolean)$values['create'],
                      'edit'=>(boolean)$values['edit'],
                      'delete'=>(boolean)$values['delete'],
                      'publish'=>(boolean)$values['publish'],
                  ));
              }
              else
              {
                  $obj = new Db_Object($this->_name);
                  $obj->setValues(array(
                      'view'=>(boolean)$values['view'],
                      'create'=>(boolean)$values['create'],
                      'edit'=>(boolean)$values['edit'],
                      'delete'=>(boolean)$values['delete'],
                      'publish'=>(boolean)$values['publish'],
                      'object'=>$values['object'],
                      'group_id'=>$groupId,
                      'user_id'=>null
                  ));
              }

              if(!$obj->save())
                  $errors = true;

          }catch (Exception $e){
             $this->logError($e->getMessage());
              $errors = true;
          }
      }

      if($errors)
          return false;
      else
      	   return true;
  }
}