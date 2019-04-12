<?php
/**
 * Image Uploader
 * @author Kirill Egorov 2011
 * @package Upload
 * @uses Image_Thumbnail
 */

use Dvelum\Image\Resize;

class Upload_Image extends Upload_File
{
     /**
      * (non-PHPdoc)
      * @see Upload_File::upload()
      */
    public function upload(array $data , $path , $formUpload = true)
    {
        $data =   parent::upload($data , $path , $formUpload);
        if(!empty($data) && !empty($this->_config['sizes']))
        {
            foreach ($this->_config['sizes'] as $name => $xy){
                $ext = \Dvelum\File::getExt($data['path']);
                $replace= '-' . $name.$ext;
                $newName=str_replace($ext, ($replace) , $data['path']);

                switch($this->_config['thumb_types'][$name]){
                    case 'crop' :
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName,true,true);
                        break;
                    case 'resize_fit':
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName,true, false);
                        break;
                    case 'resize':
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName, false ,false);
                        break;
                    case 'resize_to_frame':
                        Resize::resizeToFrame($data['path'], $xy[0], $xy[1], $newName);
                        break;
                }
                if($name == 'icon')
                    $data['thumb'] = $newName;
            }
        }
        return $data;
    }
}