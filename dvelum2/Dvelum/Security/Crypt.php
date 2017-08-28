<?php
/**
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
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

/**
 * Simple encryption class
 * Uses Base64 storage format for keys and data
 * @package Dvelum\Security
 */
class Crypt
{
    private $chipper;
    private $hash;
    private $error;

    private $privateKeyOptions = null;

    public function __construct(string $chipper = 'aes-256-ctr', string $hash = 'sha256')
    {
        $this->chipper = $chipper;
        $this->hash = $hash;
    }

    /**
     * Verify that encryption works, all dependencies are installed
     * @return bool
     */
    public function canCrypt() : bool
    {
        if(!extension_loaded('openssl')){
            $this->error = 'OpenSSL Extesion is not loaded';
            return false;
        }

        if(!in_array($this->chipper, openssl_get_cipher_methods(true), true)) {
            $this->error = 'Unknown cipher algorithm '. $this->chipper;
            return false;
        }

        if(!in_array($this->hash, openssl_get_md_methods(true), true)) {
            $this->error = 'Unknown hash algorithm '. $this->hash;
            return false;
        }
        return true;
    }

    /**
     * Get error message
     * @return string
     */
    public function getError() : string
    {
        return $this->error;
    }

    /**
     * Set prive key generator options
     * @param array $options
     * @return void
     */
    public function setPrivateKeyOptions(array $options) : void
    {
        $this->privateKeyOptions = $options;
    }

    /**
     * Generate new private key
     * @return string
     */
    public function createPrivateKey() :string
    {
        // init private key options
        if(empty($this->privateKeyOptions)){
            $this->privateKeyOptions = [
                "digest_alg" => "sha512",
                "private_key_bits" => 4096,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];
        }
        $res = openssl_pkey_new($this->privateKeyOptions);
        openssl_pkey_export($res, $key);
        return $key;
    }

    /**
     * Create random initialisation vector
     * return vector as base64 encoded string
     * @return string
     */
    public function createVector() : string
    {
        return base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->chipper)));
    }

    /**
     * Encrypt a string.
     * @param string $string - string to encrypt.
     * @param string $key - encryption key.
     * @param string $base64Vector - base64 encoded initialization vector
     * @throws \Exception
     * @return string - base64 encoded encryption result
     */
    public function encrypt(string $string, string $key, string $base64Vector) : string
    {
        $iv = base64_decode($base64Vector);
        $keyHash = openssl_digest($key, $this->hash, true);
        $encrypted = openssl_encrypt($string, $this->chipper, $keyHash, OPENSSL_RAW_DATA, $iv);

        if($encrypted === false){
            throw new \Exception('Encryption failed: ' . openssl_error_string());
        }

        return base64_encode($encrypted);
    }

    /**
     * Decrypt a string.
     * @param string $string - base64 encoded encrypted string to decrypt.
     * @param string $key - encryption key.
     * @param string $base64Vector - base64 encoded initialization vector
     * @throws \Exception
     * @return string - the decrypted string.
     */
    public function decryptString(string $string, string $key, string $base64Vector) :string
    {
        $iv = base64_decode($base64Vector);
        $src = base64_decode($string);
        $keyHash = openssl_digest($key, $this->hash, true);
        $res = openssl_decrypt($src, $this->chipper, $keyHash, OPENSSL_RAW_DATA, $iv);

        if ($res === false) {
            throw new \Exception('Decryption failed: ' . openssl_error_string());
        }
        return $res;
    }
}
