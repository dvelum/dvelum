<?php
class Model_User extends Model
{
	const AUTH_LOGIN = 'ulogin';
	const AUTH_PASSWORD = 'upassword';
	const AUTH_PROVIDER = 'uprovider';
    const AUTH_LANG = 'ulang';
	
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
     * @param string $provider
     * @return array | boolean false   User DATA or false
     */
    public function login($login, $password, $provider = 'dvelum')
    {
        $providerCfg = Config::storage()->get('auth/' . $provider . '.php', false, true);
        if (!$providerCfg)
            throw new Exception('Wrong auth provider config: ' . 'auth/' . $provider . '.php');

        $authProvider = User_Auth::factory($providerCfg);
        if (!$authProvider->auth($login, $password))
            return false;

        $data = $authProvider->getUserData();

        if (!$data)
            return false;

        $user = User::getInstance();
        $user->setId($data['id']);
        $user->setInfo($data);
		$user->setAuthProvider($authProvider);

        return $data;
    }

	/**
	 * Check if login form has been posted
	 * @return array | boolean false User DATA or false
	 */
    public function checkLogin()
    {
        $mainCfg = Config::storage()->get('main.php');
        $user = Request::post(self::AUTH_LOGIN, 'login', false);
        $pass = Request::post(self::AUTH_PASSWORD , 'string' , false);
        $provider = Request::post(self::AUTH_PROVIDER , 'string' , $mainCfg->get('default_auth_provider'));
        $language = Request::post(self::AUTH_LANG, 'string' , '');

        if($user === false || $pass=== false)
            return false;

        // slow check
        sleep(1);
        $result = $this->login($user, $pass , $provider);

        // Trying fallback provider if it set
        if(!$result && !empty($mainCfg['fallback_auth_provider'])){
            $provider = $mainCfg->get('fallback_auth_provider');
            $result = $this->login($user, $pass , $provider);
        }

        if($result) {
            $user = User::getInstance();
            $user->setLanguage($language);
        }

        return $result;
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