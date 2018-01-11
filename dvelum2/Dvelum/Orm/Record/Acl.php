<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2018  Kirill Yegorov
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
 */

namespace Dvelum\Orm\Record;

use Dvelum\Orm\Record;
use Dvelum\Orm\RecordInterface;

abstract class Acl
{
    const ACCESS_VIEW = 'view';
    const ACCESS_EDIT = 'edit';
    const ACCESS_CREATE = 'create';
    const ACCESS_DELETE = 'delete';
    const ACCESS_PUBLISH = 'publish';

    public function __construct(){}

    /**
     * Current user
     * @var \User
     */
    protected $_user = false;

    /**
     * Check create permissions
     * @param RecordInterface $object
     * @return boolean
     */
    abstract public function canCreate(RecordInterface $object);

    /**
     * Check update permissions
     * @param RecordInterface $object
     * @return boolean
     */
    abstract public function canEdit(RecordInterface $object);

    /**
     * Check delete permissions
     * @param RecordInterface $object
     * @return boolean
     */
    abstract public function canDelete(RecordInterface $object);

    /**
     * Check publish permissions
     * @param RecordInterface $object
     * @return boolean
     */
    abstract public function canPublish(RecordInterface $object);

    /**
     * Check read permissions
     * @param RecordInterface $object
     * @return boolean
     */
    abstract public function canRead(RecordInterface $object);

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
     * @param string $operation - const  Object\Acl::ACCESS_VIEW,ACCESS_EDIT,ACCESS_CREATE,ACCESS_DELETE,ACCESS_PUBLISH
     * @param string $objectName
     * @return boolean
     */
    abstract public function can($operation , $objectName);

    /**
     * Create ACL adapter object
     * @param string $class
     * @throws \Exception
     * @return Record\Acl
     */
    static public function factory($class)
    {
        $object = new $class;

        if(!$object instanceof Record\Acl)
            throw new \Exception('Invalid ACL adapter '.$class);

        return $object;
    }
}