<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Utils;

class Strings
{
    /**
     * Get random string
     * @param int $length - string length
     * @return string
     */
    static function getRandomString(int $length) : string
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

    static public function alphabetEn() : array
    {
        return ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
    }

    /**
     * Normalize string to class name
     * @param string $str
     * @param bool $useNamespace
     * @return string
     */
    static public function classFromString(string $str, bool $useNamespace = false) : string
    {
        if($useNamespace){
            $sep = '\\';
        }else{
            $sep = '_';
        }
        $parts = explode($sep, $str);
        $parts = array_map('ucfirst' , $parts);
        return implode($sep, $parts);
    }

    /**
     * Normalize class name
     * @param string $name
     * @param bool $useNamespace
     * @return string
     */
    static public function formatClassName($name, bool $useNamespace = false) : string
    {
        if($useNamespace){
            $sep = '\\';
        }else{
            $sep = '_';
        }

        $nameParts = explode($sep, $name);
        $nameParts = array_map('ucfirst', $nameParts);
        return implode($sep, $nameParts);
    }

    /**
     * Add lines indent
     * @param string $string
     * @param integer $tabsCount , optional, default = 1
     * @param string $indent , optional, default = "\t"
     * @return string
     */
    static public function addIndent(string $string , int $tabsCount = 1 , string $indent="\t" , bool $ignoreFirstLine = false) : string
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
     * @param int $maxLength
     * @return  string
     */
    static public function limitText(string $string , int $maxLength) : string
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

    static public function createEncryptIv()
    {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_OFB, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
        mcrypt_module_close($td);
        return $iv;
    }

    /**
     * Encrypt string
     * @param string $string
     * @return $string
     */
    static public function encrypt($string ,$key , $iv) : string
    {
        if(empty($string))
            return '';

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_OFB, '');;
        $ks = mcrypt_enc_get_key_size($td);

        $key = substr(md5($key), 0, $ks);

        mcrypt_generic_init($td, $key, $iv);

        $encrypted = mcrypt_generic($td , $string);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return base64_encode($encrypted);
    }
    /**
     * Decrypt string
     * @param string $string
     * @return string
     */
    static public function decrypt($string , $key , $iv): string
    {
        if(empty($string))
            return '';

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_OFB, '');
        $ks = mcrypt_enc_get_key_size($td);

        $key = substr(md5($key), 0, $ks);

        mcrypt_generic_init($td, $key, $iv);

        $decrypted = mdecrypt_generic($td, base64_decode($string));

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $decrypted;
    }
}