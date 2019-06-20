<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
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
 */

namespace Dvelum\App\Session;

use Dvelum\Orm\Model as Model;
use Dvelum\Store\Factory;
use Exception;

/**
 * The class is used to identify the current system User.
 * @package Dvelum\App\Session
 */
class User
{
    protected static $instance;

    protected $info = [];

    protected $id = false;

    protected $authChecked = false;

    /**
     * @var bool | \Dvelum\User\Auth\AbstractAdapter
     */
    protected $authProvider = false;

    protected $moduleAcl = false;

    /**
     * @var UserSettings $settings
     */
    protected $settings;

    /**
     * @var \Dvelum\Store\Session
     */
    protected $session = false;

    /**
     * @var array $permissions
     */
    protected $permissions;

    /**
     * Authorizing the user: successful authorization returns
     * the link to the object, while failure to authorize returns false
     * @param string $user
     * @param string $password
     * @return \User|boolean
     */
    static public function login($user , $password)
    {
        $data = Model::factory('user')->login($user , $password);

        if(!$data)
            return false;

        $user = User::getInstance();
        $user->logout();
        $user->setId($data['id']);
        $user->setInfo($data);
        $user->setAuthorized();

        return $user;
    }

    /**
     * Instantiate a User
     * @return User
     */
    static public function factory() : User
    {
        if(!isset(static::$instance))
            static::$instance = new static();
        return static::$instance;
    }

    protected function __construct()
    {
        $this->session = Factory::get(Factory::SESSION);
        $this->checkAuthSession();
    }

    /**
     * Force User ID setup (leads to object data dump)
     * @param integer $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->info = null;
        $this->permissions = null;
        $this->settings = new UserSettings($id);
    }

    /**
     * Load user information
     * @throws Exception
     */
    protected function loadData()
    {
        if(!$this->id || !$this->isAuthorized())
            throw new Exception('User is not authorised');

        $data = Model::factory('User')->getInfo($this->id);
        if(!$data)
            throw new Exception('Invalid user data');

        $this->setInfo($data);
    }

    /**
     * Set up User data
     * @param array $data
     */
    public function setInfo(array $data)
    {
        $this->info = $data;
    }

    public function getSettings() : UserSettings
    {
        return $this->settings;
    }
    /**
     * Get User data
     * @return array
     */
    public function getInfo()
    {
        if(empty($this->info))
            $this->loadData();

        return $this->info;
    }

    /**
     * Set user auth provider
     * @param \Dvelum\User\Auth\AbstractAdapter $authProvider
     */
    public function setAuthProvider($authProvider)
    {
        $this->authProvider = $authProvider;
    }

    /**
     * Get user auth provider
     * @return \Dvelum\User\Auth\AbstractAdapter $authProvider
     */
    public function getAuthProvider()
    {
        return $this->authProvider;
    }

    /**
     * The object has a getter defined, which can be invoked by a key.
     * @param mixed $property
     * @throws Exception
     * @return mixed:
     */
    public function __get($property)
    {
        return $this->get($property);
    }

    public function get($property)
    {
        if($property === 'id')
            return $this->id;

        if(empty($this->info))
            $this->loadData();

        if(isset($this->info[$property]))
            return $this->info[$property];
        else
            throw new \Exception('User. Invalid property "' . $property . '" ');
    }

    public function __isset($property)
    {
        if($property === 'id')
            return true;

        return isset($this->info[$property]);
    }

    /**
     * Check once more if the user is authorized
     * at first launch (while running the script);
     * the model verifies whether the authorization data has been transferred by Post method
     * and if yes, it attempts to authorize the User; for more details, learn the
     * Model_User::checkLogin method
     * @return boolean
     */
    public function isAuthorized()
    {
        if ($this->authChecked){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Check if the User has administrative access to the system
     * @return bool
     */
    public function isAdmin()
    {
        if(!$this->isAuthorized())
            return false;

        if(empty($this->info)){
            $this->loadData();
        }

        return (boolean) $this->get('admin');
    }

    /**
     *  Force current User authorization
     * @return void
     */
    public function setAuthorized()
    {
        $this->session->set('auth' , true);
        $this->session->set('auth_id' , $this->id);
        $this->authChecked = true;
    }

    public function getId()
    {
        return  $this->id;
    }

    /**
     * Get user group
     * @return bool|mixed
     */
    public function getGroup()
    {
        return $this->get('group_id');
    }

    /**
     *  Remove User authorization data (the session remains active, while the User is logged out)
     */
    public function logout()
    {
        $ses = Dvelum\Store\Factory::get(Dvelum\Store\Factory::SESSION);
        $ses->set('auth' , false);
        $ses->set('auth_id' , false);
        $this->authChecked = false;
        $this->info = [];
        $this->permissions = null;
    }

    /**
     * Check if user auth session exists
     */
    protected function checkAuthSession()
    {
        if($this->session->keyExists('auth') && $this->session->get('auth') && $this->session->keyExists('auth_id') && $this->session->get('auth_id')){
            $this->setId($this->session->get('auth_id'));
            $this->setAuthorized();
        }
    }

    /**
     * @return \Dvelum\App\Module\Acl
     */
    public function getModuleAcl() : \Dvelum\App\Module\Acl
    {
        if(!$this->moduleAcl){
            $this->moduleAcl  = new \Dvelum\App\Module\Acl($this);
        }
        return  $this->moduleAcl;
    }
}