<?php
/**
 * The class is used to identify the current system User.
 * @author Kirill Egorov
 */
class User
{
	protected static $_instance;
	
	protected $_permissions = null;
	/**
     * @var Model_User
     */
	protected $_model;
	
	protected $_info = array();
	protected $_id = false;
	
	protected $_authChecked = false;

	protected $authProvider = false;

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
		if(!isset(self::$_instance))
			self::$_instance = new self();
		return self::$_instance;
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
	 * @param mixed $val
	 * @throws Exception
	 * @return mixed:
	 */
	public function __get($val)
	{
		if($val === 'id')
			return $this->_id;
		
		if(empty($this->_info))
			$this->_loadData();
		
		if(isset($this->_info[$val]))
			return $this->_info[$val];
		else
			throw new Exception('User. Invalid property "' . $val . '" ');
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
		if(!$this->_authChecked)
		{
			$userData = $this->_model->checkLogin();	
			if($userData)
			{
				$this->_authChecked = true;
				$this->setId($userData['id']);
				$this->setInfo($userData);
				$this->setAuthorized();
				return true;
			}
		}
		
		$ses = Store::factory(Store::Session);

		if($ses->keyExists('auth') && $ses->get('auth'))
			return (boolean) $ses->get('auth_id');
		else
			return false;
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
	
	/**
	 * (non-PHPdoc)
	 * @see User::getId()
	 */
	public function getId()
	{
	  return  $this->_id;
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
	
	
	/* == ADMIN SECTION == */
	
	/**
	 * Get modules available for the current user
	 * @return array
	 */
	public function getAvailableModules()
	{
		if(!isset($this->_permissions))
			$this->_loadPermissions();
		
		$data = array();
		if(!empty($this->_permissions))
			foreach($this->_permissions as $name => $config)
				if($config['view'])
					$data[$name] = $name;
		
		return $data;
	}

    /**
     * Get user permissions
     * @return array
     */
    public function getPermissions()
    {
        if($this->isAdmin())
        {
            if(!isset($this->_permissions))
                $this->_loadPermissions();

            return $this->_permissions;
        }else{
            return [];
        }
    }

	/**
	 * Get module permissions
	 * @param $module
	 * @return bool | []
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
	protected function _loadPermissions()
	{
		$this->_permissions = Model::factory('Permissions')->getPermissions($this->_id , $this->group_id);
	}

	/**
     * Check if user can view module data
     * @param string $module
     * @return boolean
     */
	public function canView($module)
	{
		return $this->_checkPermission($module , 'view');
	}

	/**
     * Check if user can edit module data
     * @param string $module
     * @return boolean
     */
	public function canEdit($module)
	{
		return $this->_checkPermission($module , 'edit');
	}

	/**
     * Check if user can delete module data
     * @param string $module
     * @return boolean
     */
	public function canDelete($module)
	{
		return $this->_checkPermission($module , 'delete');
	}

	/**
     * Check if user can publish module data
     * @param string $module
     * @return boolean
     */
	public function canPublish($module)
	{
		return $this->_checkPermission($module , 'publish');
	}

	/**
	 * Check if user can view only own records
	 * @param $module
	 * @return bool
	 */
	public function onlyOwnRecords($module)
	{
		return $this->_checkPermission($module , 'only_own');
	}

	/**
     * Check permission for module
     * @param string $module - module name
     * @param string $perm  - permission type
     * @return boolean
     */
	protected function _checkPermission($module , $perm)
	{
		if($module === false)
			return false;
		
		if(is_null($this->_permissions))
			$this->_loadPermissions();
		
		if(isset($this->_permissions[$module]) && $this->_permissions[$module][$perm])
			return true;
		else
			return false;
	}
}