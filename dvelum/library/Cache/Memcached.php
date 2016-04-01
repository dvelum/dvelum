<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2016  Kirill A Egorov
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

/**
 * Cache Backend Memcached
 * Simple Memcached adapter
 * @author Kirill Egorov
 */
class Cache_Memcached extends Cache_Abstract implements Cache_Interface
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT =  11211;
    const DEFAULT_PERSISTENT_KEY = false;
    const DEFAULT_WEIGHT  = 1;
    const DEFAULT_TIMEOUT = 1;

    const DEFAULT_KEY_PREFIX = '';
    const DEFAULT_COMPRESSION = false;
    const DEFAULT_SERIALIZER = Memcached::SERIALIZER_PHP;
    const DEFAULT_LIFETIME = 0;
    const DEFAULT_NORMALIZE_KEYS = true;

    protected $_opCounter = array(
        'load'=>0,
        'save'=>0,
        'remove'=>0
    );

    /**
     * @var Memcached
     */
    protected $memcached = null;
    protected $options = [];

    /**
     *
     * @param array $options
     *
     *		'servers' => array(
     *		    array(
     *				'host' => self::DEFAULT_HOST,
     *				'port' => self::DEFAULT_PORT,
     *				'weight'  => self::DEFAULT_WEIGHT,
     *		    )
     *		 ),
     *		'compression' => self::DEFAULT_COMPRESSION,
     *		'normalizeKeys'=>sef::DEFAULT_NORMALIZE_KEYS,
     *		'defaultLifeTime=> self::DEFAULT_LIFETIME
     *		'keyPrefix'=>self:DEFAULT_KEY_PREFIX
     *      'persistent_key' => self::DEFAULT_PERSISTENT_KEY
     *
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        if(!isset($this->options['compression'])){
            $this->options['compression'] = self::DEFAULT_COMPRESSION;
        }

        if(!isset($this->options['serializer'])){
            $this->options['serializer'] = self::DEFAULT_SERIALIZER;
        }
        
        if(!isset($this->options['normalizeKeys'])){
            $this->options['normalizeKeys'] = self::DEFAULT_NORMALIZE_KEYS;
        }

        if(!isset($this->options['keyPrefix'])){
            $this->options['keyPrefix'] = self::DEFAULT_KEY_PREFIX;
        }

        if (!isset($this->options['persistent_key'])){
            $this->options['persistent_key'] = self::DEFAULT_PERSISTENT_KEY;
        }

        if($this->options['persistent_key']){
            $this->memcached = new Memcached($this->options['persistent_key']);
        }else{
            $this->memcached = new Memcached();
        }

        $this->memcached->setOptions([
            Memcached::OPT_COMPRESSION => $this->options['compression'],
            Memcached::OPT_SERIALIZER =>  $this->options['serializer'],
            Memcached::OPT_PREFIX_KEY => $this->options['keyPrefix'],
            Memcached::OPT_LIBKETAMA_COMPATIBLE => true
        ]);

        if(!count($this->memcached->getServerList()))
        {
            foreach ($this->options['servers'] as $server)
            {
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
     * Normalize key
     * @param $key
     * @return string
     */
    protected function _id($key)
    {
        if($this->options['normalizeKeys'])
            return md5($key);

        return $key;
    }

    /**
     * Save some string data into a cache record
     * @param  string $data Data to cache
     * @param  string $id Cache id
     * @param  int | bool $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $specificLifetime = false)
    {
        if($specificLifetime === false)
            $specificLifetime = $this->options['defaultLifeTime'];

        $id = $this->_id($id); // cache id may need normalization	
        
        $result = @$this->memcached->set($id, $data, $specificLifetime);
        $this->_opCounter['save']++;
        return $result;
    }

    /**
     * Remove a cache record
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        $id = $this->_id($id); // cache id may need normalization
        $this->_opCounter['remove']++;
        return $this->memcached->delete($id);
    }

    /**
     * Clean some cache records
     * @return boolean True if no problem
     */
    public function clean()
    {
        return $this->memcached->flush();
    }

    /**
     * Load data from cache
     * @param  string  $id  Cache id
     * @return mixed|false Cached datas
     */
    public function load($id)
    {
        $id = $this->_id($id); // cache id may need normalization

        $data = $this->memcached->get($id);
        $this->_opCounter['load']++;

        return $data;
    }

    /**
     * Get Memcache object link
     * @return Memcache
     */
    public function getHandler()
    {
        return $this->memcached;
    }
}