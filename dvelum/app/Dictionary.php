<?php
/**
 * System dictionary class
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2012  Kirill A Egorov,
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
 * @license General Public License version 3
 *
 * Backward compatibility
 */
use Dvelum\App\Dictionary\DictionaryInterface;

class Dictionary
{
    /**
     * Instantiate a dictionary by name
     * @param string $name
     * @return DictionaryInterface
     * @deprecated
     */
    static public function getInstance($name): DictionaryInterface
    {
        return self::factory($name);
    }

    /**
     * Instantiate a dictionary by name
     * @param string $name
     * @return DictionaryInterface
     */
    static public function factory($name): DictionaryInterface
    {
        return Dvelum\Service::get('dictionary')->get($name);
    }
}