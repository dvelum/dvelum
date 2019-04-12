<?php
/**
 * ACL adapter based on Db_Object
 * access permissions and DB table as storage
 */
use Dvelum\Orm;

class Acl_Table_Simple extends Orm\Record\Acl
{
    static $_rights = [];

    public function __construct()
    {
        parent::__construct();
        $this->_user = User::getInstance();
    }
    /**
     * Default permissions Model name
     * @var string
     */
    protected $_defaultPermissionsModel = 'Acl_Simple';
    /**
     * Permissions list
     * @var array|null
     */
    protected $_permissions = null;
    /**
     * Permissions model
     * @var \Model_Permissions
     */
    protected $_permissionsModel = null;

    protected function _loadPermissions()
    {
        // try to load from static cache
        if(is_null($this->_permissions)  && isset(self::$_rights[$this->_user->getId()])){
            $this->_permissions = self::$_rights[$this->_user->getId()];
            return;
        }

        // init default model
        if(is_null($this->_permissionsModel))
            $this->_permissionsModel = Model::factory($this->_defaultPermissionsModel);

        //get permissions
        $permissions = $this->_permissionsModel->getPermissions($this->_user->getId() , $this->_user->group_id);

        //static cache
        self::$_rights[$this->_user->getId()] = $permissions;

        $this->_permissions = $permissions;
    }
    /**
     * Reset loaded permissions
     */
    public function resetPermissions()
    {
        $this->_permissions = null;
        unset(self::$_rights[$this->_user->getId()]);
    }
    /**
     * Set permissions model object
     * @param Orm\Model $model
     */
    public function setPermissionsModel(Orm\Model $model)
    {
        $this->_permissionsModel = $model;
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canCreate()
     */
    public function canCreate(Orm\RecordInterface $object)
    {
        return $this->_checkPermission($object, self::ACCESS_CREATE);
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canEdit()
     */
    public function canEdit(Orm\RecordInterface $object)
    {
        return $this->_checkPermission($object, self::ACCESS_EDIT);
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canDelete()
     */
    public function canDelete(Orm\RecordInterface $object)
    {
        return $this->_checkPermission($object, self::ACCESS_DELETE);
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canPublish()
     */
    public function canPublish(Orm\RecordInterface $object)
    {
        return $this->_checkPermission($object, self::ACCESS_PUBLISH);
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canRead()
     */
    public function canRead(Orm\RecordInterface $object)
    {
        return $this->_checkPermission($object, self::ACCESS_VIEW);
    }
    /**
     * Check permissions for object
     * @param Orm\Record $object - object name
     * @param string $permissionType - permission type
     * @return boolean
     */
    protected function _checkPermission(Orm\RecordInterface $object , $permissionType)
    {
        return $this->can($permissionType , $object->getName());
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::can()
     */
    public function can($operation , $objectName)
    {
        $objectName = strtolower($objectName);

        if(is_null($this->_permissions))
            $this->_loadPermissions();

        if(isset($this->_permissions[$objectName]) && $this->_permissions[$objectName][$operation])
            return true;
        else
            return false;
    }
}