<?php
class Validator_Ip2long implements Validator_Interface
{
  /**
   * (non-PHPdoc)
   * @see Validator_Interface::validate()
   */
  static public function validate($data)
  {
    if(ip2long($data) === false)
      return false;
    else 
      return true;
  }
}