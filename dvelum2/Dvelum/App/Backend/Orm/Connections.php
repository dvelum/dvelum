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

namespace Dvelum\App\Backend\Orm;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;

class Connections
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function typeExists($devType)
    {
        return isset($this->config[$devType]);
    }
    /**
     * Get connections list
     * @param integer $devType
     * @throws \Exception
     * @return array
     */
    public function getConnections($devType)
    {
        if(!$this->typeExists($devType))
            throw new \Exception('Backend_Orm_Connections_Manager :: getConnections undefined dev type ' . $devType);

        $files = Config::storage()->getList($this->config[$devType]['dir']);
        $result = array();
        if(!empty($files)){
            foreach($files as $path){
                $result[substr(basename($path),0,-4)] =  Config::storage()->get($this->config[$devType]['dir'] . basename($path) , true , false);
            }
        }
        return $result;
    }

    /**
     * Remove DB Connection config
     * Caution! Connection settings will be removed for all system modes.
     * @param string $id
     * @throws \Exception
     */
    public function removeConnection($id)
    {
        $writePath = Config::storage()->getWrite();
        $errors = array();
        /*
         * Check for write permissions before operation
         */
        foreach ($this->config as $devType =>$data)
        {
            $file = $writePath . $data['dir'] . $id .'.php';
            if(!file_exists($file) && !is_writable($file))
                $errors[] = $file;
        }

        if(!empty($errors))
            throw new \Exception(Lang::lang()->get('CANT_WRITE_FS') . ' ' . implode(', ',$errors));

        foreach ($this->config as $devType=>$data)
        {
            $file = $writePath . $data['dir'] . $id .'.php';
            if(!@unlink($file)){
                throw new \Exception(Lang::lang()->get('CANT_WRITE_FS') . ' ' . $file);
            }
        }
    }

    /**
     * Get connection config
     * @param int $devType
     * @param string $id
     * @return ConfigInterface|null
     */
    public function getConnection(int $devType , string $id) : ?ConfigInterface
    {
        if(!$this->typeExists($devType))
            return null;

        $cfg = Config::storage()->get($this->config[$devType]['dir'] . $id . '.php');

        if(empty($cfg))
            return null;

        return $cfg;
    }

    public function createConnection($id)
    {
        foreach ($this->config as $devType=>$data)
            if($this->connectionExists($devType , $id))
                return false;

        foreach ($this->config as $devType=>$data)
        {
            if(!Config::storage()->create($this->config[$devType]['dir'] . $id . '.php'))
                return false;

            $c = $this->getConnection($devType, $id);
            $c->setData([
                'username' => '',
                'password' => '',
                'dbname'   => '',
                'host'     => '',
                'charset'  => 'UTF8',
                'prefix'   => '',
                'adapter'  => 'Mysqli',
                'transactionIsolationLevel' => 'default'
            ]);

            if(!$c->save())
                return false;
        }
        return true;
    }
    /**
     * Rename DB connection config
     * @param string $oldId
     * @param string $newId
     * @return boolean
     */
    public function renameConnection($oldId , $newId)
    {
        $writePath = Config::storage()->getWrite();
        /**
         * Check permissions
         */
        foreach ($this->config as $devType=>$data)
        {
            if(!is_writable($writePath . $data['dir'])
                || $this->connectionExists($devType, $newId)
                || !file_exists($writePath . $data['dir'] . $oldId . '.php')
                || !is_writable($writePath . $data['dir'] . $oldId . '.php')
            ){
                return false;
            }
        }
        foreach ($this->config as $devType=>$data){
            rename($writePath .$this->config[$devType]['dir'] . $oldId . '.php', $writePath.$this->config[$devType]['dir'] . $newId . '.php');
        }
        return true;
    }
    /**
     * Check if DB Connection exists
     * @param integer $devType
     * @param string $id
     * @return boolean
     */
    public function connectionExists($devType , $id)
    {
        if(!$this->typeExists($devType))
            return false;

        return Config::storage()->exists($this->config[$devType]['dir'] . $id . '.php');
    }
    /**
     * Get connections config
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}