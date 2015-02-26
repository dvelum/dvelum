<?php
class Converter_Ip2long implements Converter_Interface
{
  static public function convert($data)
  {
    $data = ip2long($data);
    if($data == false){
      throw new Exception('Invalid value');
    }
    return ip2long($data);
  }
}