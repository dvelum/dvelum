<?php
/**
 * Image Uploader
 * @author Kirill Egorov 2011
 * @package Upload
 * @uses Image_Thumbnail
 */
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
                $ext = File::getExt($data['path']);
                $replace= '-' . $name.$ext;
                $newName=str_replace($ext, ($replace) , $data['path']);

                switch($this->_config['thumb_types'][$name]){
                    case 'crop' :
                        Image_Resize::resize($data['path'], $xy[0], $xy[1], $newName,true,true);
                        break;
                    case 'resize_fit':
                        Image_Resize::resize($data['path'], $xy[0], $xy[1], $newName,true, false);
                        break;
                    case 'resize':
                        Image_Resize::resize($data['path'], $xy[0], $xy[1], $newName, false ,false);
                        break;
                }
                if($name == 'icon')
                    $data['thumb'] = $newName;
            }
        }
        return $data;
    }
}