<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
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
 *
 */
declare(strict_types=1);

namespace Dvelum;

class Filter
{
    const FILTER_ARRAY = 'array';
    const FILTER_BOOLEAN = 'bool';
    const FILTER_INTEGER = 'int';
    const FILTER_FLOAT = 'float';
    const FILTER_STRING = 'str';
    const FILTER_CLEANED_STR = 'cleaned_string';
    const FILTER_EMAIL = 'email';
    const FILTER_ALPHANUM = 'alphanum';
    const FILTER_NUM = 'num';
    const FILTER_ALPHA = 'alpha';
    const FILTER_LOGIN = 'login';
    const FILTER_PAGECODE = 'pagecode';
    const FILTER_RAW = 'raw';
    const FILTER_URL = 'url';

    protected static $autoConvertFloatSeparator = true;

    /**
     * String cleanup
     * @param string $string
     * @return string
     */
    static public function filterString(string $string) : string
    {
        return trim(strip_tags($string));
    }

    /**
     * Filter variable
     * @param string $filter
     * @param mixed $value
     * @return mixed
     */
    static public function filterValue(string $filter, $value)
    {
        $filter = strtolower($filter);
        switch ($filter) {
            case 'array' :
                if (!is_array($value)) {
                    if(!empty($value)){
                        $value = [$value];
                    }else{
                        $value = [];
                    }
                }
                break;
            case 'bool' :
            case 'boolean' :
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'int' :
            case 'integer' :
                $value = intval($value);
                break;
            case 'float' :
            case 'decimal' :
            case 'number' :
                $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                if (is_string($value) && self::$autoConvertFloatSeparator) {
                    $value = str_replace(',', '.', $value);
                }
                $value = floatval($value);
                break;
            case 'str' :
            case 'string' :
            case 'text' :
                $value = trim((string)filter_var($value, FILTER_SANITIZE_STRING));
                break;

            case 'cleaned_string' :
                $value = trim((string)filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
                break;

            case 'email' :
                $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                break;

            case 'url' :
                $value = filter_var($value, FILTER_SANITIZE_URL);
                break;

            case 'raw' :
                break;
            case 'alphanum' :
                $value = preg_replace("/[^A-Za-z0-9_]/i", '', $value);
                break;
            case 'num'    :
                $value = preg_replace("/[^0-9]/i", '', $value);
                break;
            case 'login' :
                $value = preg_replace("/[^A-Za-z0-9_@\.\-]/i", '', $value);
                break;
            case 'pagecode' :
                $value = preg_replace("/[^a-z0-9_-]/i", '', strtolower((string) $value));
                $value = str_replace(' ', "-", $value);
                break;

            case 'alpha' :
                $value = preg_replace("/[^A-Za-z]/i", '', $value);
                break;

            default :
                $value = intval($value);
                break;

        }
        return $value;
    }
}