<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace Dvelum\App\Acl\Table;
/**
 * ACL adapter based on Db_Object
 * access permissions and DB table as storage
 */

use Dvelum\App\Model\Permissions;
use Dvelum\App\Session\User;
use Dvelum\Orm;

class Simple extends Orm\Record\Acl
{
    static $rights = [];

    public function __construct()
    {
        parent::__construct();
        $this->user = User::factory();
    }
    /**
     * Default permissions Model name
     * @var string
     */
    protected $defaultPermissionsModel = 'Acl_Simple';
    /**
     * Permissions list
     * @var array|null
     */
    protected $permissions = null;
    /**
     * Permissions model
     * @var Permissions
     */
    protected $permissionsModel = null;

    protected function loadPermissions()
    {
        // try to load from static cache
        if(is_null($this->permissions)  && isset(self::$rights[$this->user->getId()])){
            $this->permissions = self::$rights[$this->user->getId()];
            return;
        }

        // init default model
        if(is_null($this->permissionsModel))
            $this->permissionsModel = Orm\Model::factory($this->defaultPermissionsModel);

        //get permissions
        $permissions = $this->permissionsModel->getPermissions($this->user->getId() , $this->user->group_id);

        //static cache
        self::$rights[$this->user->getId()] = $permissions;

        $this->permissions = $permissions;
    }
    /**
     * Reset loaded permissions
     */
    public function resetPermissions()
    {
        $this->permissions = null;
        unset(self::$rights[$this->user->getId()]);
    }
    /**
     * Set permissions model object
     * @param Orm\Model $model
     */
    public function setPermissionsModel(Orm\Model $model)
    {
        $this->permissionsModel = $model;
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canCreate()
     */
    public function canCreate(Orm\RecordInterface $object)
    {
        return $this->checkPermission($object, self::ACCESS_CREATE);
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canEdit()
     */
    public function canEdit(Orm\RecordInterface $object)
    {
        return $this->checkPermission($object, self::ACCESS_EDIT);
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canDelete()
     */
    public function canDelete(Orm\RecordInterface $object)
    {
        return $this->checkPermission($object, self::ACCESS_DELETE);
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canPublish()
     */
    public function canPublish(Orm\RecordInterface $object)
    {
        return $this->checkPermission($object, self::ACCESS_PUBLISH);
    }
    /**
     * (non-PHPdoc)
     * @see Db_Object_Acl::canRead()
     */
    public function canRead(Orm\RecordInterface $object)
    {
        return $this->checkPermission($object, self::ACCESS_VIEW);
    }
    /**
     * Check permissions for object
     * @param Orm\RecordInterface $object - object name
     * @param string $permissionType - permission type
     * @return boolean
     */
    protected function checkPermission(Orm\RecordInterface $object , $permissionType)
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

        if(is_null($this->permissions))
            $this->loadPermissions();

        if(isset($this->permissions[$objectName]) && $this->permissions[$objectName][$operation])
            return true;
        else
            return false;
    }
}