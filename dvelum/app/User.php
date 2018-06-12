<?php

use Dvelum\Orm\Model as Model;
use Dvelum\App\Session\UserSettings;
/**
 * The class is used to identify the current system User.
 * @author Kirill Egorov
 */
class User
{
	protected static $instance;

	/**
     * @var Model_User
     */
	protected $model;
	
	protected $info = array();
	protected $id = false;
	
	protected $authChecked = false;

	protected $authProvider = false;

    protected $moduleAcl = false;

    /**
     * @var UserSettings $settings
     */
    protected $settings;

	/**
     * @var Store_Session
     */
	protected $session = false;

	/**
     * Authorizing the user: successful authorization returns 
     * the link to the object, while failure to authorize returns false
     * @param string $user
     * @param string $password
     * @return User|boolean
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
	static public function getInstance()
	{
		if(!isset(static::$instance))
			static::$instance = new static();
		return static::$instance;
	}

	protected function __construct()
	{
		$this->model = Model::factory('User');
		$this->session = Store_Session::getInstance();
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

		$data = $this->model->getInfo($this->id);
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
	 * @param User_Auth_Abstract $authProvider
	 */
	public function setAuthProvider($authProvider)
	{
		$this->authProvider = $authProvider;
	}

	/**
	 * Get user auth provider
	 * @return User_Auth_Abstract $authProvider
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
     * @return boolean
     */
	public function isAdmin()
	{	
		if(!$this->isAuthorized())
			return false;

		if(empty($this->info)){
            $this->loadData();
        }
			
		return (boolean) $this->admin;	
	}

	/**
     *  Force current User authorization
     * @return void
     */
	public function setAuthorized()
	{
		$ses = Store::factory(Store::Session);
		$ses->set('auth' , true);
		$ses->set('auth_id' , $this->id);
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
		$ses = Store::factory(Store::Session);
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



    /**
     * Get modules available for the current user
     * @deprecated
     * @return array
     */
    public function getAvailableModules()
    {
        $acl = $this->getModuleAcl();
        return $acl->getAvailableModules();
    }

    /**
     * Get user permissions
     * @deprecated
     * @return array
     */
    public function getPermissions()
    {
        $acl = $this->getModuleAcl();
        return $acl->getPermissions();
    }

    /**
     * Get module permissions
     * @param $module
     * @deprecated
     * @return bool | []
     */
    public function getModulePermissions($module)
    {
        $acl = $this->getModuleAcl();
        return $acl->getModulePermissions($module);
    }

    /**
     * Check if user can view module data
     * @param string $module
     * @deprecated
     * @return boolean
     */
    public function canView($module) : bool
    {
        $acl = $this->getModuleAcl();
        return $acl->canView($module);
    }

    /**
     * Check if user can edit module data
     * @param string $module
     * @deprecated
     * @return boolean
     */
    public function canEdit($module) : bool
    {
        $acl = $this->getModuleAcl();
        return $acl->canEdit($module);
    }

    /**
     * Check if user can delete module data
     * @param string $module
     * @deprecated
     * @return boolean
     */
    public function canDelete($module) : bool
    {
        $acl = $this->getModuleAcl();
        return $acl->canDelete($module);
    }

    /**
     * Check if user can publish module data
     * @param string $module
     * @deprecated
     * @return boolean
     */
    public function canPublish($module) : bool
    {
        $acl = $this->getModuleAcl();
        return $acl->canPublish($module);
    }

    /**
     * Check if user can view only own records
     * @param $module
     * @deprecated
     * @return bool
     */
    public function onlyOwnRecords($module) : bool
    {
        $acl = $this->getModuleAcl();
        return $acl->onlyOwnRecords($module);
    }
}