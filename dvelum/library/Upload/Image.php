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

                if($this->_config['thumb_types'][$name] == 'crop')
                	Image_Resize::resize($data['path'], $xy[0], $xy[1], $newName,true,true);
                else
                	Image_Resize::resize($data['path'], $xy[0], $xy[1], $newName,true,false);

                if($name == 'icon')
                    $data['thumb'] = $newName;
            }
        }
        return $data;
    }
}