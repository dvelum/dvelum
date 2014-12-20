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
        if(empty($id))
            throw new Exception('Need user id');
          
        /*
         * Check cached info
         */    
        $cache = self::$_dataCache;    
        if($cache && $data = $cache->load('user_info' . $id))
        	return $data;    

        /*
         * Get user data
         */	
        $sql = $this->_db->select()
					     ->from( $this->table() )
					     ->where('`id` = ' . intval($id))
					     ->limit(1);
        
        $data = $this->_db->fetchRow($sql);

        if(empty($data))
          	return false;

        /*
         * Store cache
         */  
        if($cache)
        	$cache->save($data , 'user_info' . $id);
        	
        return $data;      
    }
    /**
     * (non-PHPdoc)
     * @see Model::remove()
     */
    public function remove($recordId , $log =true)
    {
    	if(parent::remove($recordId , $log))
    	{
    		/*
	         * Check cached info
	         */    
	        $cache = self::$_dataCache;  
    		if($cache)
        		$cache->remove('user_info' . $recordId);
    		
    		return true;
    	}
    	return false;
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
    	
    	$sql = $this->_db->select()
                         ->from($this->table())
                         ->where('`login` =?' , $login)
                         ->where('`pass` =?' , $password)
                         ->where('`enabled` = 1');
                         
         $data = $this->_db->fetchRow($sql);        

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
}