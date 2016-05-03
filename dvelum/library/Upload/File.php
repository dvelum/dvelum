<?php
/**
 * File uploader class
 * @package Upload
 * @author Kirill Egorov
 */
class Upload_File
{
    protected $_config;

    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     * Upload file
     *
     * @param array $data- $_FILES array item
     * @param boolean $formUpload  - optional, default true
     * @return array / false on error
     */
    public function upload(array $data , $path , $formUpload = true)
    {
        if($data['error'])
            return false;

        if(isset($this->_config['max_file_size']) && ($this->_config['max_file_size'])){
            if($data['size'] > $this->_config['max_file_size'])
                return false;
        }

        $result = array(
            'name' => '' ,
            'path' => '' ,
            'size' => '' ,
            'type' => ''
        );

        $name = str_replace(' ' , '_' , $data['name']);
        $name = preg_replace("/[^A-Za-z0-9_\-\.]/i" , '' , $name);

        $ext = File::getExt($name);

        if(!in_array($ext , $this->_config['extensions']))
            return false;

        $namePart = str_replace($ext , '' , $name);

        if(isset($this->_config['rewrite']) && $this->_config['rewrite']){
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
                return false;
            }
        }

        $result['name'] = $namePart . $ext;
        $result['path'] = $path . $namePart . $ext;
        $result['ext'] = $ext;

        if($formUpload)
        {
            if(!@move_uploaded_file($data['tmp_name'] , $result['path']))
                return false;
        }
        else
        {
            if(!@copy($data['tmp_name'] , $result['path']))
                return false;
        }

        $result['size'] = $data['size'];
        $result['type'] = $data['type'];

        @chmod($result['path'] , 0644);

        return $result;
    }
}