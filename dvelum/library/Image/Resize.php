<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
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
 *
 */

/**
 * Image Resizer Component
 * @package Image
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 */
class Image_Resize
{

    const L_TYPE_H = 1;
    const L_TYPE_V = 2;
    const L_TYPE_S = 3;

    /**
     * Crop image
     * @param string  $src - source image path
     * @param string $dest - destination image path
     * @param integer $x - x coord
     * @param integer $y - y coord
     * @param integer $w - width
     * @param integer $h - height
     * @return boolean
     */
    static public function cropImage($src , $dest , $x , $y , $w , $h)
    {
        $imgInfo = getimagesize($src);
        $img = self::createImg($src , $imgInfo[2]);
        $destImg = self::_createDuplicateLayer($imgInfo[2] , $w , $h);
        imagecopyresampled($destImg , $img , 0 , 0 , $x , $y , $w , $h , $w , $h);
        imagedestroy($img);

        return self::saveImage($destImg , $dest , $imgInfo[2]);
    }

    /**
     * Create image from file
     * @param string $path - file path
     * @param integer $type - image type constant, source file type
     * @return resource | boolean
     */
    static public function createImg($path , $type)
    {
        switch($type)
        {
            case IMAGETYPE_GIF :
                $im = imagecreatefromgif($path);
                break;
            case IMAGETYPE_JPEG :
                $im = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG :
                $im = imagecreatefrompng($path);
                break;
            default :
                trigger_error('Unsupported file type!' , E_USER_WARNING);
                return false;
                break;
        }
        return $im;
    }

    /**
     * Resize image
     * @param string $imgPath
     * @param integer $width
     * @param integer $height
     * @param string $newImgPath
     * @param boolean $fit, optional default false
     * @param boolean $crop, optional default false
     * @return boolean
     */
    static public function resize($imgPath , $width , $height , $newImgPath , $fit = false , $crop = true)
    {
        /*
         * Check if GD extension is loaded
         */
        if(!extension_loaded('gd') && !extension_loaded('gd2'))
        {
            trigger_error("GD is not loaded" , E_USER_WARNING);
            return false;
        }

        if($crop){
            return self::cropResize($imgPath , $width , $height , $newImgPath);
        }

        /*
         * Get Image size info
         */
        $imgInfo = getimagesize($imgPath);
        $im = self::createImg($imgPath , $imgInfo[2]);

        /*
         * If image sizes less then need just save image into the new location
         */
        if($imgInfo[0] < $width && $imgInfo[1] < $height)
        {
            $result = self::saveImage($im , $newImgPath , $imgInfo[2]);
            if($im){
                imagedestroy($im);
            }
            return $result;
        }

        /*
         * Resize it, but keep it proportional
         */
        if($width / $imgInfo[0] > $height / $imgInfo[1])
        {
            $nWidth = $width;
            $nHeight = $imgInfo[1] * ($width / $imgInfo[0]);
        }
        else
        {
            $nWidth = $imgInfo[0] * ($height / $imgInfo[1]);
            $nHeight = $height;
        }

        $nWidth = round($nWidth);
        $nHeight = round($nHeight);

        if($fit)
        {

            if($nWidth > $width)
            {
                $k = $width / $nWidth;
                $nWidth = $width;
                $nHeight = $nHeight * $k;
            }

            if($nHeight > $height)
            {
                $k = $height / $nHeight;
                $nHeight = $height;
                $nWidth = $nWidth * $k;
            }
            $nWidth = round($nWidth);
            $nHeight = round($nHeight);
        }

        $newImg = self::_createDuplicateLayer($imgInfo[2] , $nWidth , $nHeight);

        imagecopyresampled($newImg , $im , 0 , 0 , 0 , 0 , $nWidth , $nHeight , $imgInfo[0] , $imgInfo[1]);
        imagedestroy($im);

        return self::saveImage($newImg , $newImgPath , $imgInfo[2]);
    }

    /**
     * Create image resource for manipulation,
     * transparent for IMG_GIF and IMG_PNG
     * @param integer $type image type
     * @param integer $width
     * @param integer $height
     * @return resource $image
     */
    static protected function _createDuplicateLayer($type , $width , $height)
    {
        $img = imagecreatetruecolor($width , $height);
        if(in_array($type, array(IMG_GIF, IMG_PNG, IMAGETYPE_GIF, IMAGETYPE_PNG), true))
        {
            imagealphablending($img , false);
            imagesavealpha($img , true);
            $transparent = imagecolorallocatealpha($img , 255 , 255 , 255 , 127);
            imagefilledrectangle($img , 0 , 0 , $width , $height , $transparent);
        }
        return $img;
    }

    /**
     * Save image to file
     * @param resource $resource - image resource
     * @param string $path - path to file
     * @param mixed $imgType - image type constant deprecated
     * @return boolean
     */
    static protected function saveImage($resource , $path , $imgType = false)
    {
        $ext = File::getExt(strtolower($path));
        switch ($ext){
            case '.jpg':
            case '.jpeg':
                $imgType = IMAGETYPE_JPEG;
                break;
            case '.gif':
                $imgType = IMAGETYPE_GIF;
                break;
            case 'png':
                $imgType = IMAGETYPE_PNG;
                break;
        }

        switch($imgType)
        {
            case IMAGETYPE_GIF :
                $result = imagegif($resource , $path);
                break;
            case IMAGETYPE_JPEG :
                $result = imagejpeg($resource , $path , 100);
                break;
            case IMAGETYPE_PNG :
                $result = imagepng($resource , $path);
                break;
            default :
                $result = false;
        }
        imagedestroy($resource);
        return $result;
    }

    /**
     * Detect layout orientation
     * @param integer $width
     * @param integer $height
     * @return integer - orientation constant Image_Resize::L_TYPE_S , Image_Resize::L_TYPE_H , Image_Resize::L_TYPE_V
     */
    static public function detectLayout($width , $height)
    {
        if($width == $height)
            return self::L_TYPE_S;
        elseif($width > $height)
            return self::L_TYPE_H;
        else
            return self::L_TYPE_V;
    }

    /**
     * Crop and resize image
     * @param string $imgPath
     * @param integer $width
     * @param integer $height
     * @param string $newImgPath
     * @return boolean
     */
    static public function cropResize($imgPath , $width , $height , $newImgPath)
    {
        /*
         * Get Image size info
         */
        $imgInfo = getimagesize($imgPath);
        $sourceWidth = $imgInfo[0];
        $sourceHeight = $imgInfo[1];

        $sourceLayout = self::detectLayout($sourceWidth , $sourceHeight);
        $resultLayout = self::detectLayout($width , $height);

        if($sourceLayout == self::L_TYPE_H && $resultLayout == self::L_TYPE_H)
        {
            $newSizes = self::_calcHorizontalToHorizontal($sourceWidth , $sourceHeight , $width , $height);
        }
        elseif($sourceLayout == self::L_TYPE_H && $resultLayout == self::L_TYPE_V)
        {
            $newSizes = self::_calcHorizontalToVertical($sourceHeight , $width , $height);
        }
        elseif($sourceLayout == self::L_TYPE_V && $resultLayout == self::L_TYPE_H)
        {
            $newSizes = self::_calcVerticalToHorizontal($sourceWidth , $width , $height);
        }
        elseif($sourceLayout == self::L_TYPE_S && $resultLayout == self::L_TYPE_S)
        {
            $newSizes = self::_calcSquareToSquare($sourceWidth , $sourceHeight);
        }
        else
        {
            /*
             * Vertical to vertical
             * sqrt to sqrt
             * vertical to sqrt
             * horizontal to sqrt
             * sqrt to vertical
             * sqrt to horizontal
             */
            $newSizes = self::_calcHorizontalToHorizontal($sourceWidth , $sourceHeight , $width , $height);
        }

        $x = 0;
        $y = 0;

        if($newSizes[0] < $sourceWidth)
        {
            $x = ($sourceWidth - $newSizes[0]) / 2;
        }

        if($newSizes[1] < $sourceHeight)
        {
            $difference = $sourceHeight - $newSizes[1];
            $y = $difference / 2 - $difference / 4;
        }

        $im = self::createImg($imgPath , $imgInfo[2]);
        $dest = self::_createDuplicateLayer($imgInfo[2] , $width , $height);

        imagecopyresampled($dest , $im , 0 , 0 , $x , $y , $width , $height , $newSizes[0] , $newSizes[1]);
        imagedestroy($im);
        self::saveImage($dest , $newImgPath , $imgInfo[2]);
        return true;
    }

    static protected function _calcHorizontalToHorizontal($sourceWidth , $sourceHeight , $width , $height)
    {
        $sourceProportion = $sourceWidth / $sourceHeight;
        $proportion = $width / $height;

        if($sourceProportion > $proportion)
            return self::_calcHorizontalToVertical($sourceHeight , $width , $height);
        else
            return self::_calcVerticalToHorizontal($sourceWidth , $width , $height);
    }

    static protected function _calcHorizontalToVertical($sourceHeight , $width , $height)
    {
        $proportion = $width / $height;

        $newHeight = $sourceHeight;
        $newWidth = $newHeight * $proportion;

        return [
            $newWidth,
            $newHeight
        ];
    }

    static protected function _calcVerticalToHorizontal($sourceWidth , $width , $height)
    {
        $proportion = $width / $height;
        $newWidth = $sourceWidth;
        $newHeight = $newWidth / $proportion;

        return [
            intval($newWidth),
            intval($newHeight)
        ];
    }

    static protected function _calcSquareToSquare($sourceWidth , $sourceHeight)
    {
        return [
            $sourceWidth,
            $sourceHeight
        ];
    }
}