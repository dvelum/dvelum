<?php
class Code_Php_Compiller{
    
    public function compile(array $files , $destFile){
        $source = '';
        
        foreach ($files as $k=>$v)
                $source.=' '.str_replace(array('<?php','<?','?>') . '' . php_strip_whitespace($v));
        
        return @file_put_contents($destFile, '<?php ' . $source);
    }
}