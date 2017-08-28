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

namespace Dvelum\Security;

interface CryptServiceInterface
{
    /**
     * Generate new private key
     * @return string
     */
    public function createPrivateKey(): string;

    /**
     * Create random initialisation vector
     * return vector as base64 encoded string
     * @return string
     */
    public function createVector(): string;

    /**
     * Encrypt a string.
     * @param string $string - string to encrypt.
     * @param string $base64Vector - base64 encoded initialization vector
     * @throws \Exception
     * @return string - base64 encoded encryption result
     */
    public function encrypt(string $string, string $base64Vector): string;

    /**
     * Decrypt a string.
     * @param string $string - base64 encoded encrypted string to decrypt.
     * @param string $base64Vector - base64 encoded initialization vector
     * @throws \Exception
     * @return string - the decrypted string.
     */
    public function decrypt(string $string, string $base64Vector): string;
}