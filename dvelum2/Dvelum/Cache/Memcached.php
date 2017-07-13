<?php
/**
 *    DVelum project https://github.com/dvelum/dvelum
 *    Copyright (C) 2011-2017  Kirill Yegorov
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Cache;

/**
 * Cache Backend Memcached
 * Simple Memcached adapter
 * @author Kirill Yegorov 2011-2017
 */
class Memcached extends AbstractAdapter implements CacheInterface
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 11211;
    const DEFAULT_PERSISTENT_KEY = false;
    const DEFAULT_WEIGHT = 1;
    const DEFAULT_TIMEOUT = 1;

    const DEFAULT_KEY_PREFIX = '';
    const DEFAULT_COMPRESSION = false;
    const DEFAULT_SERIALIZER = \Memcached::SERIALIZER_PHP;
    const DEFAULT_LIFETIME = 0;
    const DEFAULT_NORMALIZE_KEYS = true;

    /**
     * @var \Memcached
     */
    protected $memcached = null;

    /**
     * @param array $settings
     *
     *        'servers' => array(
     *            array(
     *                'host' => self::DEFAULT_HOST,
     *                'port' => self::DEFAULT_PORT,
     *                'weight'  => self::DEFAULT_WEIGHT,
     *            )
     *         ),
     *        'compression' => self::DEFAULT_COMPRESSION,
     *        'normalizeKeys'=>sef::DEFAULT_NORMALIZE_KEYS,
     *        'defaultLifeTime=> self::DEFAULT_LIFETIME
     *        'keyPrefix'=>self:DEFAULT_KEY_PREFIX
     *      'persistent_key' => self::DEFAULT_PERSISTENT_KEY
     * @return array
     */
    protected function initConfiguration(array $settings): array
    {

        if (!isset($settings['compression'])) {
            $settings['compression'] = self::DEFAULT_COMPRESSION;
        }

        if (!isset($settings['serializer'])) {
            $settings['serializer'] = self::DEFAULT_SERIALIZER;
        }

        if (!isset($settings['normalizeKeys'])) {
            $settings['normalizeKeys'] = self::DEFAULT_NORMALIZE_KEYS;
        }

        if (!isset($settings['keyPrefix'])) {
            $settings['keyPrefix'] = self::DEFAULT_KEY_PREFIX;
        }

        if (!isset($settings['persistent_key'])) {
            $settings['persistent_key'] = self::DEFAULT_PERSISTENT_KEY;
        }

        return $settings;
    }

    protected function connect(array $settings)
    {
        if ($settings['persistent_key']) {
            $this->memcached = new \Memcached($settings['persistent_key']);
        } else {
            $this->memcached = new \Memcached();
        }

        $this->memcached->setOptions([
            \Memcached::OPT_COMPRESSION => $settings['compression'],
            \Memcached::OPT_SERIALIZER => $settings['serializer'],
            \Memcached::OPT_PREFIX_KEY => $settings['keyPrefix'],
            \Memcached::OPT_LIBKETAMA_COMPATIBLE => true
        ]);

        if (!count($this->memcached->getServerList())) {
            foreach ($settings['servers'] as $server) {
                if (!isset($server['port'])) {
                    $server['port'] = self::DEFAULT_PORT;
                }

                if (!isset($server['weight'])) {
                    $server['weight'] = self::DEFAULT_WEIGHT;
                }
                $this->memcached->addServer($server['host'], $server['port'], $server['weight']);
            }
        }
    }

    /**
     * Save some string data into a cache record
     * @param  string $data Data to cache
     * @param  string $id Cache id
     * @param  int | bool $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return bool True if no problem
     */
    public function save($data, string $id, $specificLifetime = false): bool
    {
        if ($specificLifetime === false) {
            $specificLifetime = $this->settings['defaultLifeTime'];
        }

        $id = $this->prepareKey($id); // cache id may need normalization
        try {
            $result = $this->memcached->set($id, $data, $specificLifetime);
            $this->stat['save']++;
            return $result;
        } catch (\Error $e) {
            return false;
        }
    }

    /**
     * Remove a cache record : bool
     * @param string $id Cache id
     * @return bool True if no problem
     */
    public function remove(string $id): bool
    {
        $id = $this->prepareKey($id); // cache id may need normalization
        $this->stat['remove']++;
        return $this->memcached->delete($id);
    }

    /**
     * Clean some cache records
     * @return bool True if no problem
     */
    public function clean(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * Load data from cache
     * @param  string $id Cache id
     * @return mixed|false Cached datas
     */
    public function load(string $id)
    {
        $id = $this->prepareKey($id); // cache id may need normalization

        $data = $this->memcached->get($id);
        $this->stat['load']++;

        return $data;
    }

    /**
     * Get Memcache object link
     * @return \Memcached
     */
    public function getHandler(): \Memcached
    {
        return $this->memcached;
    }
}