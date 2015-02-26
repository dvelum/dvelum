<?php
class Model_User extends Model
{
	const AUTH_LOGIN = 'ulogin';
	const AUTH_PASSWORD = 'upassword';
	
    /**
     * Get user info
     * @param integer $id
     * @throws Exception
     * @return array
     */
    public function getInfo($id)
    {
        return $this->getCachedItem($id);  
    }
    /**
     * Login as user
     * @param string $login
     * @param string $password
     * @return array | boolean false   User DATA or false
     */
    public function login($login , $password)
    {
    	$password = Utils::hash($password);

    	$sql = $this->_dbSlave->select()
                         ->from($this->table())
                         ->where('`login` =?' , $login)
                         ->where('`pass` =?' , $password)
                         ->where('`enabled` = 1');

         $data = $this->_dbSlave->fetchRow($sql);

         if(empty($data))
             return false;

         $user = User::getInstance();
         $user->setId($data['id']);
         $user->setInfo($data);

         return $data;
    }

	/**
	 * Check if login form has been posted
	 * @return array | boolean false User DATA or false
	 */
    public function checkLogin()
    {
        $user = Request::post(self::AUTH_LOGIN, 'login', false);
        $pass = Request::post(self::AUTH_PASSWORD , 'string' , false);

        if($user === false || $pass=== false)
            return false;

        // slow check
        sleep(1);
        return $this->login($user, $pass);
    }
    /**
     * Find user avatar
     * Will be used for sharding
     * @param integer $userId
     * @param string $userName
     * @param string $avatarPath
     * @param string $size - optional size tag
     */
    static public function findAvatar($userId , $userName , $avatarPath , $size = 'thumb'){
    	if(!strlen($avatarPath))
    		return '/media/user/avatar/unknown_user.png';

    	return Model_Medialib::getImgPath($avatarPath, '.jpg', $size);
    }
    
    /**
     * Create confirmation code
     * @param string $confirmDate
     * @param string $email
     */
    public function getConfirmCode($confirmDate , $email)
    {
        return Utils::hash($confirmDate . $email);
    }
}