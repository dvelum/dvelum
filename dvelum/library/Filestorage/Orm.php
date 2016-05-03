<?php
/*
* DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
* Copyright (C) 2011-2014  Kirill A Egorov
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
 * Filestorage ORM adpater.
 * @uses Db_Object, Model
 * @author Kirill A Egorov 2014
 *
 */
class Filestorage_Orm extends Filestorage_Simple
{
    /**
     * Object name
     * @var string
     */
    protected $_object;

    /**
     * Orm object fields
     * @var array
     */
    protected $_objectFields = array('id','path','date','ext','size','user_id');

    public function __construct(Config_Abstract $config)
    {
        parent::__construct($config);

        if(!$this->_config->offsetExists('object'))
            throw new Exception('Filestorage_Orm undefined Orm object');

        $this->_object = $this->_config->get('object');

        if(!$this->_config->offsetExists('check_orm_structure') || $this->_config->get('check_orm_structure'))
            $this->checkOrmStructure();
    }

    /**
     * Check Db_Object structure
     * @throws Exception
     */
    public function checkOrmStructure()
    {
        if(!Db_Object_Config::configExists($this->_object))
            throw new Exception('Filestorage_Orm undefined Orm object');

        $cfg = Db_Object_Config::getInstance($this->_object);
        $fields = $cfg->getFieldsConfig(true);

        foreach ($this->_objectFields as $name)
            if(!isset($fields[$name]))
                throw new Exception('Filestorage_Orm invalid orm structure, field ' . $name . ' not found');
    }
    /**
     * (non-PHPdoc)
     * @see Filestorage_Simple::upload()
     */
    public function upload()
    {
        $data = parent::upload();

        if(empty($data))
            return array();

        foreach ($data as $k=>&$v)
        {
            try{
                $o = new Db_Object($this->_object);
                $o->setValues(
                    array(
                        'path' => $v['path'],
                        'date' => date('Y-m-d H:i:s'),
                        'ext' => $v['ext'],
                        'size' =>  number_format(($v['size']/1024/1024) , 3),
                        'user_id' => $this->_config->get('user_id'),
                        'name'=>$v['old_name']

                    )
                );
                if(!$o->save())
                    throw new Exception('Cannot save object');

                $v['id'] = $o->getId();

            }catch (Exception $e){
                Model::factory($this->_object)->logError('Filestorage_Orm: ' . $e->getMessage());
            }
        }unset($v);
        return $data;
    }
    /**
     * (non-PHPdoc)
     * @see Filestorage_Simple::generateFilePath()
     */
    protected function generateFilePath()
    {
        return $this->_config->get('filepath') . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $this->_config->get('user_id').'/';
    }

    /**
     * (non-PHPdoc)
     * @see Filestorage_Simple::remove()
     */
    public function remove($fileId)
    {
        if(!Db_Object::objectExists($this->_object, $fileId))
            return true;

        try{
            $o = new Db_Object($this->_object ,  $fileId);
        }catch (Exception $e){
            return false;
        }

        $path = $o->path;

        if(!$o->delete())
            return false;

        return parent::remove($path);
    }
    /**
     * (non-PHPdoc)
     * @see Filestorage_Simple::add()
     */
    public function add($filePath , $useName = false)
    {
        $data = parent::add($filePath , $useName);

        if(empty($data))
            return false;

        try{
            $o = new Db_Object($this->_object);
            $o->setValues(
                array(
                    'path' => $data['path'],
                    'date' => date('Y-m-d H:i:s'),
                    'ext' => $data['ext'],
                    'size' =>  number_format(($data['size']/1024/1024) , 3),
                    'user_id' => $this->_config->get('user_id'),
                    'name' => $data['old_name']
                )
            );
            if(!$o->save())
                throw new Exception('Cannot save object');

            $data['id'] = $o->getId();

        }catch (Exception $e){
            echo $e->getMessage();
            Model::factory($this->_object)->logError('Filestorage_Orm: ' . $e->getMessage());
        }
        return $data;
    }
}