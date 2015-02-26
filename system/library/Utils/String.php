<?php
class Utils_String
{
	/**
	 * Get random string
	 * @param integer $length - string length
	 * @return string
	 */
	static function getRandomString($length)
	{
		$string = '';
		$symbols = array(
				'q','w','e','r','t','y','u','i','o','p',
				'a','s','d','f','g','h','j','k','l',
				'z','x','c','v','b','n','m',
				1,2,3,4,5,6,7,8,9,0,
				'Q','W','E','R','T','Y','U','I','O','P',
				'A','S','D','F','G','H','J','K','L','Z',
				'X','C','V','B','N','M'
		);
		$size = sizeof($symbols) - 1;
		while ($length) {
			$string .= $symbols[mt_rand(0, $size)];
			--$length;
		}
		 
		return $string;
	}
	
	static public function alphabetEn()
	{
		return array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	}
	
	/**
     * Normalize string to class name
     * @param string $str
     * @return string
     */
    static public function classFromString($str)
    {
    	$parts = explode('_',$str);
    	$parts = array_map('ucfirst' , $parts);
    	return implode('_', $parts);
    }
    
    /**
     * Normalize class name
     * @param string $name
     * @return string
     */
    static public function formatClassName($name)
    {
        $nameParts = explode('_', $name);
        $nameParts = array_map('ucfirst', $nameParts);
        return implode('_', $nameParts);
    }
    
    /**
     * Add lines indent
     * @param string $string
     * @param integer $tabsCount , optional, default = 1
     * @param string $indent , optional, default = "\t"
     * @return string
     */
    static public function addIndent($string , $tabsCount = 1 , $indent="\t" , $ignoreFirstLine = false)
    {
        $indent = str_repeat("\t", $tabsCount);
        if($ignoreFirstLine)
          return str_replace("\n", "\n".$indent, $string);
        else
          return $indent . str_replace("\n", "\n".$indent, $string);
    }
    
    /**
     * Limit text
     * @param string $string
     * @param intager $maxLength
     * @return  string
     */
    static public function limitText($string , $maxLength)
    {
        $strlen = strlen($string);
        if($strlen <= $maxLength)
            return $string;
    
        $string = substr($string, 0 , $maxLength);
        $wordStart = strrpos($string, ' ');
    
        if($wordStart)
            $string = substr($string, 0 , $wordStart);
    
        $string.='...';
        return $string;
    }
}