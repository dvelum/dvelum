<?php
/**
 * File uploader class
 * @package Upload
 * @author Kirill Egorov
 */
class Upload_File extends Upload_AbstractAdapter
{
    /**
     * Upload file
     *
     * @param array $data- $_FILES array item
     * @param boolean $formUpload  - optional, default true
     * @return array|bool on error
     */
    public function upload(array $data , $path , $formUpload = true)
    {
        $this->_error = '';

        if($data['error']){
            $this->_error = 'Server upload error';
            return false;
        }

        if(isset($this->_config['max_file_size']) && ($this->_config['max_file_size'])){
            if($data['size'] > $this->_config['max_file_size']){
                $this->_error = 'File too large. Check max_file_size option';
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

        if(!in_array($ext , $this->_config['extensions'])){
            $this->_error='File extension is not allowed';
            return false;
        }


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
                $this->_error='Cannot rename file. Iterations limit';
                return false;
            }
        }

        $result['name'] = $namePart . $ext;
        $result['path'] = $path . $namePart . $ext;
        $result['ext'] = $ext;

        if($formUpload)
        {
            if(!move_uploaded_file($data['tmp_name'] , $result['path'])){
                $this->_error='move_uploaded_file error';
                return false;
            }

        }
        else
        {
            if(!copy($data['tmp_name'] , $result['path'])){
                $this->_error='copy error';
                return false;
            }
        }

        $result['size'] = $data['size'];
        $result['type'] = $data['type'];

        @chmod($result['path'] , 0644);

        return $result;
    }
}