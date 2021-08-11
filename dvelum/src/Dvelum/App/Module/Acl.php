<?php
namespace Dvelum\App\Module;

use Dvelum\App\Session;
use Dvelum\Orm\Model;
use Dvelum\Orm;

class Acl
{
    /**
     * @var Session\User
     */
    protected  $user;

    protected $permissions;

    public function __construct(Session\User $user)
    {
        $this->user = $user;
    }


    /**
     * Get modules available for the current user
     * @return array
     */
    public function getAvailableModules()
    {
        if(!isset($this->permissions))
            $this->loadPermissions();

        $data = array();
        if(!empty($this->permissions))
            foreach($this->permissions as $name => $config)
                if($config->view)
                    $data[$name] = $name;

        return $data;
    }

    /**
     * Get user permissions
     * @return array
     */
    public function getPermissions()
    {
        if($this->user->isAdmin())
        {
            if(!isset($this->permissions))
                $this->loadPermissions();

            return $this->permissions;
        }else{
            return [];
        }
    }

    /**
     * Get module permissions
     * @param $module
     * @return bool | Permissions
     */
    public function getModulePermissions($module)
    {
        $permissions = $this->getPermissions();
        if(isset($permissions[$module])){
            return $permissions[$module];
        }else{
            return false;
        }
    }

    /**
     * Load user permissions
     * @return void
     */
    protected function loadPermissions()
    {
        $list = Orm::factory()->model('Permissions')->getPermissions($this->user->getId() , (int) $this->user->getGroup());
        foreach ($list as $item){
            $this->permissions[$item['module']] = new Permissions($item);
        }
    }

    /**
     * Check if user can view module data
     * @param string $module
     * @return boolean
     */
    public function canView($module) : bool
    {
        return $this->checkPermission($module , 'view');
    }

    /**
     * Check if user can edit module data
     * @param string $module
     * @return boolean
     */
    public function canEdit($module) : bool
    {
        return $this->checkPermission($module , 'edit');
    }

    /**
     * Check if user can delete module data
     * @param string $module
     * @return boolean
     */
    public function canDelete($module) : bool
    {
        return $this->checkPermission($module , 'delete');
    }

    /**
     * Check if user can publish module data
     * @param string $module
     * @return boolean
     */
    public function canPublish($module) : bool
    {
        return $this->checkPermission($module , 'publish');
    }

    /**
     * Check if user can view only own records
     * @param $module
     * @return bool
     */
    public function onlyOwnRecords($module) : bool
    {
        return $this->checkPermission($module , 'only_own');
    }

    /**
     * Check permission for module
     * @param string $module - module name
     * @param string $perm  - permission type
     * @return boolean
     */
    protected function checkPermission($module , $perm)
    {
        if($module === false)
            return false;

        if(is_null($this->permissions))
            $this->loadPermissions();

        if(isset($this->permissions[$module]) && $this->permissions[$module]->{$perm})
            return true;
        else
            return false;
    }
}