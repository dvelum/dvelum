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

use Dvelum\Image\Resize;

/**
 * Image Uploader
 */
class Image extends File
{
    /**
     * @inheritDoc
     */
    public function upload(array $data, string $path, bool $formUpload = true)
    {
        $data = parent::upload($data, $path, $formUpload);
        if (!empty($data) && !empty($this->config['sizes'])) {
            foreach ($this->config['sizes'] as $name => $xy) {
                $ext = \Dvelum\File::getExt($data['path']);
                $replace = '-' . $name . $ext;
                $newName = str_replace($ext, ($replace), $data['path']);

                switch ($this->config['thumb_types'][$name]) {
                    case 'crop' :
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName, true, true);
                        break;
                    case 'resize_fit':
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName, true, false);
                        break;
                    case 'resize':
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName, false, false);
                        break;
                    case 'resize_to_frame':
                        Resize::resizeToFrame($data['path'], $xy[0], $xy[1], $newName);
                        break;
                }
                if ($name == 'icon') {
                    $data['thumb'] = $newName;
                }
            }
        }
        return $data;
    }
}