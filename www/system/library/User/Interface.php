<?php
interface User_Interface{
  /**
   * Get user id
   * @return integer | false
   */
   public function getId();
  
  /**
   * Get users info
   * @return array
   */
   public function getInfo();
}