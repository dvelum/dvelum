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

namespace Dvelum\App\Upload;

use Dvelum\App\Upload\Adapter\AbstractAdapter;
use Dvelum\App\Upload\Adapter\File;
use Dvelum\App\Upload\Adapter\Image;

/**
 * File Uploader
 * @author Kirill Egorov 2010
 */
class Uploader
{
    protected $config;
    protected $uploaders;
    protected $errors = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->uploaders = [];
    }

    /**
     * Auto create dirs for upload
     * @param string $root
     * @param string $path
     * @return bool
     */
    public function createDirs(string $root, string $path) : bool
    {
        $path = str_replace('//', '/', $root . '/' . $path);

        if (file_exists($path)) {
            return true;
        }

        if (!@mkdir($path, 0775, true)) {
            return false;
        }

        return true;
    }

    /**
     * Identify file type
     * @param string $extension
     * @return mixed string | false
     */
    protected function identifyType($extension)
    {
        foreach ($this->config as $k => $v)
            if (in_array($extension, $v['extensions'], true))
                return $k;

        return false;
    }

    /**
     * Multiple upload files
     *
     * @property array $data - array of Request::files() items
     * @param string $path
     * @param bool $formUpload - optional, default true
     * @return array|false - uploaded files Info on error
     */
    public function start(array $files, string $path, $formUpload = true)
    {
        $this->errors = [];

        $uploadedFiles = [];
        foreach ($files as $item) {
            if (isset($item['error']) && $item['error']) {
                $this->errors[] = 'Server upload error';
                continue;
            }

            $item['name'] = str_replace(' ', '_', $item['name']);
            $item['name'] = strtolower(preg_replace("/[^A-Za-z0-9_\-\.]/i", '', $item['name']));

            $item['ext'] = \Dvelum\File::getExt($item['name']);
            $item['title'] = str_replace($item['ext'], '', $item['name']);
            $type = $this->identifyType($item['ext']);

            if (!$type)
                continue;

            switch ($type) {
                case 'image' :
                    if (!isset($this->uploaders['image'])) {
                        $this->uploaders['image'] = new Image($this->config['image']);
                    }
                    /**
                     * @var AbstractAdapter $uploader
                     */
                    $uploader = $this->uploaders['image'];

                    $file = $uploader->upload($item, $path, $formUpload);

                    if (!empty($file)) {
                        $file['type'] = $type;
                        $file['title'] = $item['title'];
                        if (isset($item['old_name'])) {
                            $file['old_name'] = $item['old_name'];
                        } else {
                            $file['old_name'] = $item['name'];
                        }
                        $uploadedFiles[] = $file;
                    } else {
                        if (!empty($uploader->getError())) {
                            $this->errors[] = $uploader->getError();
                        }
                    }
                    break;

                case 'audio' :
                case 'video' :
                case 'file' :
                    if (!isset($this->uploaders['file'])) {
                        $this->uploaders['file'] = new File($this->config[$type]);
                    }
                    /**
                     * @var AbstractAdapter $uploader
                     */
                    $uploader = $this->uploaders['file'];
                    $file = $uploader->upload($item, $path, $formUpload);

                    if (!empty($file)) {
                        $file['type'] = $type;
                        $file['title'] = $item['title'];

                        if (isset($item['old_name'])) {
                            $file['old_name'] = $item['old_name'];
                        } else {
                            $file['old_name'] = $item['name'];
                        }
                        $uploadedFiles[] = $file;
                    } else {
                        if (!empty($uploader->getError())) {
                            $this->errors[] = $uploader->getError();
                        }
                    }
                    break;
            }
        }

        return $uploadedFiles;
    }

    /**
     * Get upload errors
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}