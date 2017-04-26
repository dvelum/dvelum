<?php

use Dvelum\Orm\Model as Model;
/**
 * The class is used to identify the current system User.
 * @author Kirill Egorov
 */
class User
{
	protected static $_instance;
	

	/**
     * @var Model_User
     */
	protected $_model;
	
	protected $_info = array();
	protected $_id = false;
	
	protected $_authChecked = false;

	protected $authProvider = false;

    protected $moduleAcl = false;

	/**
     * @var Store_Session
     */
	protected $_session = false;

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
		if(!isset(static::$_instance))
			static::$_instance = new static();
		return static::$_instance;
	}

	protected function __construct()
	{
		$this->_model = Model::factory('User');
		$this->_session = Store_Session::getInstance();
		$this->_checkAuthSession();
	}

	/**
     * Force User ID setup (leads to object data dump)
     * @param integer $id
     * @return void
     */
	public function setId($id)
	{
		$this->_id = $id;
		$this->_info = null;
		$this->_permissions = null;
	}

	/**
     * Load user information
     * @throws Exception
     */
	protected function _loadData()
	{
		if(!$this->_id || !$this->isAuthorized())
			throw new Exception('User is not authorised');
		
		$data = $this->_model->getInfo($this->_id);
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
		$this->_info = $data;
	}

	/**
     * Get User data
     * @return array
     */
	public function getInfo()
	{
		if(empty($this->_info))
			$this->_loadData();
		
		return $this->_info;
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
            return $this->_id;

        if(empty($this->_info))
            $this->_loadData();

        if(isset($this->_info[$property]))
            return $this->_info[$property];
        else
            throw new \Exception('User. Invalid property "' . $property . '" ');
    }
	
	public function __isset($property)
	{
		if($property === 'id')
			return true;
			
		return isset($this->_info[$property]);
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
	    if ($this->_authChecked){
            return true;
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

		if(empty($this->_info))
		$this->_loadData();
			
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
		$ses->set('auth_id' , $this->_id);			
		$this->_authChecked = true;	
	}

	public function getId()
	{
	  return  $this->_id;
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
		$this->_authChecked = false;
		$this->_info = array();
		$this->_permissions = null;
	}

	/**
     * Check if user auth session exists 
     */
	protected function _checkAuthSession()
	{
		if($this->_session->keyExists('auth') && $this->_session->get('auth') && $this->_session->keyExists('auth_id') && $this->_session->get('auth_id')){
            $this->setId($this->_session->get('auth_id'));
        }
	}

	/**
	 * Get selected language
	 * @return mixed|null|string
	 */
	public function getLanguage()
	{
		return $this->_session->get('lang');
	}

	/**
	 * Set UI language
	 * @param $lang
	 */
	public function setLanguage($lang)
	{
		$this->_session->set('lang' , $lang);
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