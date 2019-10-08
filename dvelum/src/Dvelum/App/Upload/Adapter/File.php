<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
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

namespace Dvelum\App\Upload\Adapter;

/**
 * File uploader class
 */
class File extends AbstractAdapter
{
    /**
     * Upload file
     * @param array $data- $_FILES array item
     * @param bool $formUpload  - optional, default true
     * @return array|false on error
     */
    public function upload(array $data , string $path , bool $formUpload = true)
    {
        $this->error = '';

        if($data['error']){
            $this->error = 'Server upload error';
            return false;
        }

        if(isset($this->config['max_file_size']) && ($this->config['max_file_size'])){
            if($data['size'] > $this->config['max_file_size']){
                $this->error = 'File too large. Check max_file_size option';
                return false;
            }
        }

        $result = array(
            'name' => '' ,
            'path' => '' ,
            'size' => '' ,
            'type' => ''
        );

        $name = str_replace(' ' , '_' , $data['name']);
        $name = preg_replace("/[^A-Za-z0-9_\-\.]/i" , '' , $name);

        $ext = \Dvelum\File::getExt($name);

        if(!in_array($ext , $this->config['extensions'])){
            $this->error='File extension is not allowed';
            return false;
        }


        $namePart = str_replace($ext , '' , $name);

        if(isset($this->config['rewrite']) && $this->config['rewrite']){
            if(file_exists($path . $namePart . $ext))
                @unlink($path . $namePart . $ext);
        }

        if(file_exists($path . $namePart . $ext))
            $namePart .= '-0';

        $renameCount = 0;

        while(file_exists($path . $namePart . $ext))
        {
            $parts = explode('-' , $namePart);
            $el = array_pop($parts);
            $el = intval($el);
            $el++;
            $parts[] = $el;
            $namePart = implode('-' , $parts);
            $renameCount++;
            // limit iterations
            if($renameCount == 100){
                $this->error='Cannot rename file. Iterations limit';
                return false;
            }
        }

        $result['name'] = $namePart . $ext;
        $result['path'] = $path . $namePart . $ext;
        $result['ext'] = $ext;

        if($formUpload)
        {
            if(!move_uploaded_file($data['tmp_name'] , $result['path'])){
                $this->error='move_uploaded_file error';
                return false;
            }

        }
        else
        {
            if(!copy($data['tmp_name'] , $result['path'])){
                $this->error='copy error';
                return false;
            }
        }

        $result['size'] = $data['size'];
        $result['type'] = $data['type'];

        @chmod($result['path'] , 0644);

        return $result;
    }
}