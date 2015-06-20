<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net Copyright
 * (C) 2011-2012 Kirill A Egorov This program is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version. This program is distributed
 * in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. You should have received
 * a copy of the GNU General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 */
/**
 * System Utils class Do not include into packages!
 *
 * @author Kirill A Egorov
 */
class Utils
{
  protected static $_salt = '';

  /**
   * Define system hash salt
   *
   * @param string $salt
   */
  static public function setSalt($salt)
  {
    self::$_salt = $salt;
  }

  /**
   * Create an array from another array using field as key
   *
   * @param string $key
   * @param array $data
   * @throws Exception
   * @return array
   */
  static public function rekey($key , array $data)
  {
    $result = array();

    foreach($data as $k => $v)
    {
      if(! isset($v[$key]))
        throw new Exception('Invalid key');

      $result[$v[$key]] = $v;
    }

    return $result;
  }

  /**
   * Collect data from resultset
   *
   * @param string $keyField
   * @param string $valueField
   * @param array $data
   * @throws Exception
   * @return array
   */
  static public function collectData($keyField , $valueField , $data)
  {
    $result = array();
    foreach($data as $k => $v)
    {
      if(! isset($v[$keyField]) || ! isset($v[$valueField]))
        throw new Exception('Invalid key');
      $result[$v[$keyField]] = $v[$valueField];
    }
    return $result;
  }

  /**
   * Fetch array column
   *
   * @param string $key
   * @param array $data
   * @throws Exception
   * @return array
   */
  static public function fetchCol($key , array $data)
  {
    $result = array();

    if(empty($data))
      return array();

    foreach($data as $v)
      $result[] = $v[$key];

    return $result;
  }

  /**
   * Group array by column, used for db results sorting
   *
   * @param string $key
   * @param array $data
   * @return array
   */
  static public function groupByKey($key , array $data)
  {
    $result = array();

    if(empty($data))
      return array();

    foreach($data as $v)
    {
      if(! isset($v[$key]))
        trigger_error('Invalid key');
      $result[$v[$key]][] = $v;
    }
    return $result;
  }

  /**
   * Get hash for the string
   *
   * @param string $string
   * @throws Exception
   * @return string
   */
  static public function hash($string)
  {
    return md5(md5($string . self::$_salt . $string));
  }

  /**
   * Format file size in user friendly
   *
   * @param integer $size
   * @return string
   */
  static public function formatFileSize($size)
  {
    return Utils_Format::formatFileSize($size);
  }

  /**
   * Format time
   *
   * @param integer $difference
   * @return string
   */
  static public function formatTime($difference)
  {
    return Utils_Format::formatTime($difference);
  }

  /**
   * Export php array into the file
   * This function may return Boolean FALSE,
   * but may also return a non-Boolean value which evaluates to FALSE.
   *
   * Please read the section on Booleans for more information.
   * Use the === operator for testing the return value of this function.
   *
   * @param string $file
   * @param string $string
   * @return integer / false
   */
  static public function exportArray($file , array $data)
  {
    return @file_put_contents($file , '<?php return ' . var_export($data , true) . '; ');
  }

  /**
   * Export php code
   * This function may return Boolean FALSE,
   * but may also return a non-Boolean value which evaluates to FALSE.
   *
   * Please read the section on Booleans for more information.
   * Use the === operator for testing the return value of this function.
   *
   * @param string $file
   * @param string $string
   * @return integer / false
   */
  static public function exportCode($file , $string)
  {
    return @file_put_contents($file , '<?php ' . $string);
  }

  /**
   * Create class name from filepath
   *
   * @param string $path
   * @return string or false
   */
  static public function classFromPath($path)
  {
    return Utils_Fs::classFromPath($path);
  }

  /**
   * Create path for cache file
   *
   * @param string $basePath
   * @param string $fileName
   * @return string
   */
  static public function createCachePath($basePath , $fileName)
  {
    $extension = File::getExt($fileName);

    $str = md5($fileName);
    $len = strlen($str);
    $path = '';
    $count = 0;
    $parts = 0;
    for($i = 0; $i < $len; $i++)
    {
      if($count == 4)
      {
        $path .= '/';
        $count = 0;
        $parts++;
      }
      if($parts == 4)
      {
        break;
      }
      $path .= $str[$i];
      $count++;
    }
    $path = $basePath . $path;

    if(! is_dir($path))
      mkdir($path , 0755 , true);

    return $path . $str . $extension;
  }

  /**
   * Convert files list into Tree structure
   *
   * @param array $data
   * @return Tree
   */
  static public function fileListToTree(array $data)
  {
    return Utils_Format::fileListToTree($data);
  }

  /**
   * Get random string
   *
   * @param integer $length
   *          - string length
   * @return string
   */
  static function getRandomString($length)
  {
    return Utils_String::getRandomString($length);
  }

  /**
   * Check if operation system is windows
   *
   * @return boolean
   */
  static function isWindows()
  {
    if(strtoupper(substr(PHP_OS , 0 , 3)) === 'WIN')
      return true;
    else
      return false;
  }

  /**
   * Get user IP address
   *
   * @return string
   */
  static public function getClientIp()
  {
    $ip = 'Unknown';

    if(isset($_SERVER['HTTP_X_REAL_IP']))
    {
      $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    elseif(isset($_ENV['HTTP_CLIENT_IP']) && strcasecmp($_ENV['HTTP_CLIENT_IP'] , 'unknown') !== 0)
    {
      $ip = $_ENV['HTTP_CLIENT_IP'];
    }
    elseif(isset($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'] , 'unknown') !== 0)
    {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif(isset($_ENV['HTTP_X_FORWARDED_FOR']) && strcasecmp($_ENV['HTTP_X_FORWARDED_FOR'] , 'unknown') !== 0)
    {
      $ip = $_ENV['HTTP_X_FORWARDED_FOR'];
    }
    elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'] , 'unknown') !== 0)
    {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    elseif(isset($_ENV['REMOTE_ADDR']) && strcasecmp($_ENV['REMOTE_ADDR'] , 'unknown') !== 0)
    {
      $ip = $_ENV['REMOTE_ADDR'];
    }
    elseif(isset($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'] , 'unknown') !== 0)
    {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    if(stristr($ip , ","))
    {
      $ip_arr = explode("," , $ip);
      $ip = $ip_arr[0];
    }
    return $ip;
  }
  /**
   * Sort array list by sub array field
   * Faster then uasort
   * @param array $data
   * @param string $field
   * @return array
   */
  static public function sortByField(array $data , $field)
  {
     foreach ($data as $id=>$item){
      	 $index[$id] = $item[$field];
     }

  	 asort($index);

  	 $result = array();

  	 foreach ($index as $id => $value){
  	 	$result[] = $data[$id];
  	 }

  	 return $result;
  }
}