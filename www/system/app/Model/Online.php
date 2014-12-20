<?php
class Model_Online extends Model
{
    /**
     * Clear old sessions
     */
    public function clean(){
    	$this->_db->delete($this->table(),'`update_time` < '.(time() - 300));
    }  
	/**
     * Register user as "online"
     * @param string $ssid - sesion Id
     * @param integer $userId - optional
     */
    public function addOnline($ssid , $userId = 0)
    {
    	
    	if(!Registry::get('main' , 'config')->get('usersOnline'))
    		return;
    	
    	$userId = intval($userId);
    	$curTime = time();

    	$this->_db->getConnection()->query('REPLACE INTO '.$this->table().' ( `ssid`,`user_id`,`update_time`) VALUES ("' .$ssid.'", '.$userId.',"'.date('Y-m-d H:i:s',$curTime).'")');
    	/*
    	 * Delete old records from "online" table
    	 */
    	if(self::$_dataCache ){
    		/*
    		 *  Every 5 minutes if cache is enabled
    		 */
    		$lastClean = self::$_dataCache->load('online_clean');
    		if($lastClean < $curTime - 300);{
    			$this->clean();
    			self::$_dataCache->save($curTime,'online_clean');
    		}	
    	}else{
    		/*
    		 * Every time
    		 */
    		$this->clean();
    	}
    }
}