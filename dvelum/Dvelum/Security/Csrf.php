<?php
/**
 * DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2019  Kirill Yegorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Security;

use Dvelum\Request;
use Dvelum\Store\AdapterInterface;
use Dvelum\Store\Factory;
use Dvelum\Store\Session;
use Dvelum\Utils;
use \Exception;

/**
 * Security_Csrf class handles creation and validation
 * of tokens aimed at anti-CSRF protection.
 * @author Kirill Egorov
 * @package Security
 * @uses Utils, AdapterInterface , Session , Request
 */
class Csrf
{
    /**
     * A constant value, the name of the header parameter carrying the token
     * @var string
     */
    const HEADER_VAR = 'HTTP_X_CSRF_TOKEN';

    /**
     * A constant value, the name of the token parameter being passed by POST request
     * @var string
     */
    const POST_VAR = 'xscrftoken';

    /**
     * Token lifetime (1 hour 3600s)
     * @var integer
     */
    static protected $lifetime = 3600;
    /**
     * Limit of tokens count to perform cleanup
     * @var integer
     */
    static protected $cleanupLimit = 300;

    /**
     * Token storage
     * @var AdapterInterface
     */
    static protected $storage = false;

    /**
     * Set token storage implementing the Store_interface
     * @param AdapterInterface $store
     */
    static public function setStorage(AdapterInterface $store)
    {
        static::$storage = $store;
    }

    /**
     * Set config options (storage , lifetime , cleanupLimit)
     * @param array $options
     * @throws Exception
     */
    static public function setOptions(array $options)
    {
        if (isset($options['storage'])) {
            if ($options['storage'] instanceof AdapterInterface) {
                static::$storage = $options['storage'];
            } else {
                throw new Exception('invalid storage');
            }
        }

        if (isset($options['lifetime'])) {
            static::$lifetime = intval($options['lifetime']);
        }

        if (isset($options['cleanupLimit'])) {
            static::$cleanupLimit = intval($options['cleanupLimit']);
        }

    }

    public function __construct()
    {
        if (!self::$storage) {
            self::$storage = Factory::get(Factory::SESSION, 'security_csrf');
        }
    }

    /**
     * Create and store token
     * @return string
     */
    public function createToken()
    {
        /*
         * Cleanup storage
         */
        if (self::$storage->getCount() > self::$cleanupLimit) {
            $this->cleanup();
        }

        $token = md5(Utils::getRandomString(16) . uniqid('', true));
        self::$storage->set($token, time());
        return $token;
    }

    /**
     * Check if token is valid
     * @param string $token
     * @return boolean
     */
    public function isValidToken($token)
    {
        if (!self::$storage->keyExists($token)) {
            return false;
        }

        if (time() < intval(self::$storage->get($token)) + self::$lifetime) {
            return true;
        } else {
            self::$storage->remove($token);
            return false;
        }
    }

    /**
     * Remove tokens with expired lifetime
     */
    public function cleanup() : void
    {
        $tokens = self::$storage->getData();
        $time = time();

        foreach ($tokens as $k => $v) {
            if (intval($v) + self::$lifetime < $time) {
                self::$storage->remove($k);
            }
        }
    }

    /**
     * Invalidate (remove) token
     * @param string $token
     */
    public function removeToken($token)
    {
        self::$storage->remove($token);
    }

    /**
     * Check POST request for a token
     * @param string $tokenVar - Variable name in the request
     * @return boolean
     */
    public function checkPost($tokenVar = self::POST_VAR)
    {
        $var = Request::factory()->post($tokenVar, 'string', false);
        if ($var !== false && $this->isValidToken($var)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check HEADER for a token
     * @param string $tokenVar - Variable name in the header
     * @return boolean
     */
    public function checkHeader($tokenVar = self::HEADER_VAR)
    {
        $var = Request::factory()->server($tokenVar, 'string', false);
        if ($var !== false && $this->isValidToken($var)) {
            return true;
        } else {
            return false;
        }
    }
}