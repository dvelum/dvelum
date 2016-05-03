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
 * Filestorage simple filesystem adpater.
 * @author  Kirill A Egorov 2014
 *
 */
class Filestorage_Simple extends Filestorage_Abstract
{
    /**
     * (non-PHPdoc)
     * @see Filestorage_Abstract::upload()
     */
    public function upload()
    {
        $path = $this->generateFilePath();

        if(!is_dir($path) && !@mkdir($path, 0755, true)){
            $this->logError('Cannot write FS ' . $path , self::ERROR_CANT_WRITE_FS);
            return false;
        }

        $fileList = Request::files();
        $files = array();

        foreach ($fileList as $item => $cfg)
        {
            if(is_array($cfg) && !isset($cfg['name']))
            {
                foreach ($cfg as $item){
                    $item['old_name'] = $item['name'];
                    if($this->_config->get('rename')){
                        $item['name'] = time() . uniqid('-') .File::getExt($item['name']);
                    }
                    $files[]= $item;
                }

            }else{
                $cfg['old_name'] = $cfg['name'];
                if($this->_config->get('rename')){
                    $cfg['name'] = time() . uniqid('-') .File::getExt($cfg['name']);
                }
                $files[]= $cfg;
            }
        }

        if(empty($files))
            return array();

        $uploadAdapter = $this->_config->get('uploader');
        $uploaderConfig = $this->_config->get('uploader_config');

        $uploader = new $uploadAdapter($uploaderConfig);

        $uploaded = $uploader->start($files, $path);

        if(empty($uploaded))
            return array();

        foreach ($uploaded as $k=>&$v){
            $v['path'] = str_replace($this->_config->get('filepath') , '' , $v['path']);
            $v['id'] = $v['path'];
        }unset($v);

        return $uploaded;
    }
    /**
     * Generate file path
     * @return string
     */
    protected function generateFilePath()
    {
        return $this->_config->get('filepath'). '/' . date('Y') . '/' . date('m') . '/' . date('d') ;
    }
    /**
     * (non-PHPdoc)
     * @see Filestorage_Abstract::remove()
     */
    public function remove($fileId)
    {
        $fullPath = $this->_config->get('filepath') . $fileId;

        if(!file_exists($fullPath))
            return true;

        return unlink($fullPath);
    }
    /**
     * (non-PHPdoc)
     * @see Filestorage_Abstract::add()
     */
    public function add($filePath , $useName = false)
    {
        if(!file_exists($filePath))
            return false;

        $path = $this->generateFilePath();

        if(!is_dir($path) && !@mkdir($path, 0755, true)){
            $this->logError('Cannot write FS ' . $path , self::ERROR_CANT_WRITE_FS);
            return false;
        }

        $uploadAdapter = $this->_config->get('uploader');
        $uploaderConfig = $this->_config->get('uploader_config');
        $uploader = new $uploadAdapter($uploaderConfig);

        $fileName = basename($filePath);
        $oldName = basename($filePath);

        if($useName !== false){
            $oldName = $useName;
        }

        if($this->_config->get('rename')){
            $fileName = time() . uniqid('-') . File::getExt($fileName);
        }

        $files = array(
            'file' => array(
                'name' => $fileName,
                'old_name'=> $oldName,
                'type' => '',
                'tmp_name' => $filePath,
                'error' => 0,
                'size' => filesize($filePath)
            )
        );

        $uploaded = $uploader->start($files, $path , false);

        if(empty($uploaded))
            return false;

        $uploaded = $uploaded[0];
        $uploaded['path'] = str_replace($this->_config->get('filepath') , '' , $uploaded['path']);
        $uploaded['id'] = $uploaded['path'];

        return $uploaded;
    }
}