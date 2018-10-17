<?php

use Dvelum\Orm\Model;

class Model_User extends Model
{
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
}